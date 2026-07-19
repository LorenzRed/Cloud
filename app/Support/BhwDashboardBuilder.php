<?php

namespace App\Support;

use App\Models\Barangay;
use App\Models\Consultation;
use App\Models\HouseholdMonitoringCase;
use App\Models\Purok;
use App\Models\ResidentRequest;
use App\Models\ResidentTransaction;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class BhwDashboardBuilder
{
    /**
     * @return array<string, mixed>
     */
    public static function forBhw(User $bhw): array
    {
        $bhw->loadMissing(['assignedPuroks', 'purok']);

        $purokNames = $bhw->assignedPuroks
            ->pluck('name')
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique()
            ->values();

        if ($purokNames->isEmpty()) {
            $fallback = trim((string) ($bhw->purok?->name ?? ''));
            if ($fallback !== '') {
                $purokNames = collect([$fallback]);
            }
        }

        $residents = ResidentRequest::query()
            ->forBhw($bhw)
            ->where('status', 'accepted')
            ->with(['purok', 'user.purok'])
            ->get();

        $pendingRequests = ResidentRequest::query()
            ->forBhw($bhw)
            ->where('status', 'pending')
            ->count();

        return self::fromResidents($residents, [
            'scope_label' => $purokNames->isNotEmpty() ? $purokNames->implode(', ') : 'Unassigned purok',
            'pending_requests' => $pendingRequests,
            'managed_bhw_count' => null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function forHead(User $head): array
    {
        $barangay = BhwHeadScope::assignedBarangayName($head) ?? 'Unassigned barangay';
        $workers = BhwHeadScope::managedBhwWorkers($head);

        $residents = BhwHeadScope::apply(
            ResidentRequest::query()->where('status', 'accepted'),
            $head
        )->with(['purok', 'user.purok'])->get();

        $pendingRequests = BhwHeadScope::apply(
            ResidentRequest::query()->where('status', 'pending'),
            $head
        )->count();

        $purokNames = $workers
            ->flatMap(fn (User $worker) => $worker->assignedPuroks->pluck('name'))
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return self::fromResidents($residents, [
            'scope_label' => $barangay,
            'purok_coverage_label' => $purokNames->isNotEmpty() ? $purokNames->implode(', ') : 'No purok assigned yet',
            'covered_purok_names' => $purokNames->all(),
            'pending_requests' => $pendingRequests,
            'managed_bhw_count' => $workers->count(),
            'purok_count' => $purokNames->count(),
        ]);
    }

    /**
     * Barangay-wide dashboard for Barangay Captain (all accepted residents / puroks).
     *
     * @return array<string, mixed>
     */
    public static function forCaptain(?Barangay $barangay): array
    {
        if (! $barangay) {
            return self::fromResidents(collect(), [
                'scope_label' => '',
                'purok_coverage_label' => 'No barangay assigned yet',
                'covered_purok_names' => [],
                'pending_requests' => 0,
                'managed_bhw_count' => 0,
                'purok_count' => 0,
            ]);
        }

        PurokLocator::ensureNonPurok($barangay);

        $barangayName = trim((string) $barangay->name);

        $residents = ResidentRequest::query()
            ->where('status', 'accepted')
            ->where('barangay', $barangayName)
            ->with(['purok', 'user.purok'])
            ->get();

        $pendingRequests = ResidentRequest::query()
            ->where('status', 'pending')
            ->where('barangay', $barangayName)
            ->count();

        $purokNames = Purok::query()
            ->where('barangay_id', $barangay->id)
            ->orderBy('name')
            ->pluck('name')
            ->map(fn ($name) => trim((string) $name))
            ->filter(fn ($name) => $name !== '' && $name !== PurokLocator::NON_NAME)
            ->unique()
            ->sort()
            ->values();

        $bhwCount = User::query()
            ->whereIn('role', [User::ROLE_BHW, User::ROLE_BHW_HEAD])
            ->where('barangay', $barangayName)
            ->count();

        return self::fromResidents($residents, [
            'scope_label' => $barangayName !== '' ? $barangayName : 'Unassigned barangay',
            'purok_coverage_label' => $purokNames->isNotEmpty() ? $purokNames->implode(', ') : 'No purok created yet',
            'covered_purok_names' => $purokNames->all(),
            'pending_requests' => $pendingRequests,
            'managed_bhw_count' => $bhwCount,
            'purok_count' => $purokNames->count(),
        ]);
    }

    /**
     * @param  Collection<int, ResidentRequest>  $residents
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    public static function fromResidents(Collection $residents, array $meta = []): array
    {
        $residentIds = $residents->pluck('id');

        $transactions = $residentIds->isEmpty()
            ? collect()
            : ResidentTransaction::query()
                ->whereIn('resident_request_id', $residentIds)
                ->with(['residentRequest.purok', 'residentRequest.user.purok'])
                ->orderByDesc('transaction_date')
                ->orderByDesc('id')
                ->get();

        $monitoringCases = $residentIds->isEmpty()
            ? collect()
            : HouseholdMonitoringCase::query()
                ->whereIn('resident_request_id', $residentIds)
                ->where('is_active', true)
                ->with('residentRequest')
                ->get();

        $upcomingConsultations = $residentIds->isEmpty()
            ? 0
            : Consultation::query()
                ->whereIn('resident_request_id', $residentIds)
                ->where('scheduled_at', '>=', now())
                ->count();

        $conditionCounts = [
            'Hypertension' => 0,
            'Diabetes' => 0,
            'Hyperlipidemia' => 0,
        ];
        $activeIllnessKeys = [];
        $householdsWithConcern = [];
        $medicineDispensedPcs = 0;
        $transactionsThisMonth = 0;
        $illnessByPurok = [];
        $monthStart = now()->copy()->startOfMonth()->toDateString();

        foreach ($transactions as $transaction) {
            $conditions = $transaction->inferredConditions();
            $entries = $transaction->medicationEntries();

            foreach ($entries as $entry) {
                $medicineDispensedPcs += (int) ($entry['pcs'] ?? 0);
            }

            if ($transaction->transaction_date && $transaction->transaction_date->toDateString() >= $monthStart) {
                $transactionsThisMonth++;
            }

            if ($conditions === []) {
                continue;
            }

            $householdsWithConcern[$transaction->resident_request_id] = true;
            $memberKey = $transaction->resident_request_id.'|'.mb_strtolower(trim((string) ($transaction->member_name ?: 'resident')));
            $activeIllnessKeys[$memberKey] = true;

            foreach ($conditions as $condition) {
                if (isset($conditionCounts[$condition])) {
                    $conditionCounts[$condition]++;
                }
            }

            $purokName = trim((string) (
                $transaction->residentRequest?->purok?->name
                ?? $transaction->residentRequest?->user?->purok?->name
                ?? $residents->firstWhere('id', $transaction->resident_request_id)?->purok?->name
                ?? ''
            )) ?: 'Unassigned';

            if (! isset($illnessByPurok[$purokName])) {
                $illnessByPurok[$purokName] = [
                    'purok' => $purokName,
                    'cases' => 0,
                    'Hypertension' => 0,
                    'Diabetes' => 0,
                    'Hyperlipidemia' => 0,
                ];
            }

            $illnessByPurok[$purokName]['cases']++;
            foreach ($conditions as $condition) {
                if (isset($illnessByPurok[$purokName][$condition])) {
                    $illnessByPurok[$purokName][$condition]++;
                }
            }
        }

        $totalHouseholdsAssigned = $residents
            ->map(function (ResidentRequest $resident) {
                $familyNumber = trim((string) ($resident->family_number ?? ''));

                return $familyNumber !== '' ? 'family:'.$familyNumber : 'resident:'.$resident->id;
            })
            ->unique()
            ->count();

        $peopleInHousehold = static function (ResidentRequest $resident): int {
            $names = collect([
                $resident->full_name,
                $resident->spouse_full_name,
            ]);

            foreach ([$resident->household_children, $resident->household_seniors] as $members) {
                foreach (is_array($members) ? $members : [] as $member) {
                    if (is_array($member)) {
                        $names->push($member['full_name'] ?? $member['name'] ?? null);
                    }
                }
            }

            return max(1, $names
                ->filter(fn ($name) => filled($name))
                ->map(fn ($name) => mb_strtolower(trim((string) $name)))
                ->unique()
                ->count());
        };

        $totalPeopleAssigned = $residents
            ->groupBy(function (ResidentRequest $resident) {
                $familyNumber = trim((string) ($resident->family_number ?? ''));

                return $familyNumber !== '' ? 'family:'.$familyNumber : 'resident:'.$resident->id;
            })
            ->sum(fn ($household) => $household->max($peopleInHousehold));

        $pregnantMonitored = $monitoringCases->where('member_type', HouseholdMonitoringCase::TYPE_PREGNANT)->count();
        $seniorMonitored = $monitoringCases->where('member_type', HouseholdMonitoringCase::TYPE_SENIOR)->count();
        $generalMonitored = $monitoringCases->where('member_type', HouseholdMonitoringCase::TYPE_GENERAL)->count();
        $seniorByAge = $residents->filter(fn ($resident) => (int) ($resident->age ?? 0) >= 60)->count();

        $mapMonitoringMembers = function ($cases) {
            return $cases
                ->map(function (HouseholdMonitoringCase $case) {
                    $familyNumber = trim((string) ($case->residentRequest?->family_number ?? ''));

                    return [
                        'name' => trim((string) ($case->member_name ?? '')) ?: ($case->residentRequest?->full_name ?: 'Household member'),
                        'family_number' => $familyNumber !== '' ? $familyNumber : 'Not assigned',
                        'household' => $case->residentRequest?->full_name ?: '—',
                        'purok' => trim((string) ($case->residentRequest?->purok?->name ?? '')) ?: '—',
                    ];
                })
                ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                ->values()
                ->all();
        };

        $periodBounds = function (string $period): array {
            $to = now()->endOfDay();

            return match ($period) {
                'week' => [now()->startOfWeek()->startOfDay(), $to],
                'year' => [now()->startOfYear()->startOfDay(), $to],
                default => [now()->startOfMonth()->startOfDay(), $to],
            };
        };

        $periodRangeLabel = function (Carbon $from, Carbon $to): string {
            if ($from->isSameDay($to)) {
                return $from->format('M j, Y');
            }

            if ($from->year === $to->year) {
                return $from->format('M j').' – '.$to->format('M j, Y');
            }

            return $from->format('M j, Y').' – '.$to->format('M j, Y');
        };

        $transactionsInRange = function (Carbon $from, Carbon $to) use ($transactions) {
            return $transactions->filter(function (ResidentTransaction $transaction) use ($from, $to) {
                if (! $transaction->transaction_date) {
                    return false;
                }

                $date = $transaction->transaction_date->copy()->startOfDay();

                return $date->betweenIncluded($from->copy()->startOfDay(), $to->copy()->startOfDay());
            });
        };

        $conditionLabels = ['Hypertension', 'Diabetes', 'Hyperlipidemia'];
        $conditionColors = ['#BE123C', '#0EA5E9', '#F59E0B'];

        $concernsForTransactions = function ($scopedTransactions) use ($conditionLabels, $conditionColors) {
            $counts = array_fill_keys($conditionLabels, 0);

            foreach ($scopedTransactions as $transaction) {
                foreach ($transaction->inferredConditions() as $condition) {
                    if (isset($counts[$condition])) {
                        $counts[$condition]++;
                    }
                }
            }

            return [
                'labels' => array_keys($counts),
                'values' => array_values($counts),
                'colors' => $conditionColors,
                'transaction_count' => $scopedTransactions->filter(
                    fn (ResidentTransaction $transaction) => $transaction->medicationEntries() !== [] || $transaction->inferredConditions() !== []
                )->count(),
            ];
        };

        $trendForPeriod = function (string $period, Carbon $from, Carbon $to) use ($transactionsInRange) {
            $scoped = $transactionsInRange($from, $to);
            $labels = [];
            $values = [];

            $countForBucket = function (Carbon $bucketStart, Carbon $bucketEnd) use ($scoped): int {
                return $scoped->filter(function (ResidentTransaction $transaction) use ($bucketStart, $bucketEnd) {
                    if (! $transaction->transaction_date) {
                        return false;
                    }

                    if ($transaction->medicationEntries() === [] && $transaction->inferredConditions() === []) {
                        return false;
                    }

                    $date = $transaction->transaction_date->copy()->startOfDay();

                    return $date->betweenIncluded($bucketStart->copy()->startOfDay(), $bucketEnd->copy()->startOfDay());
                })->count();
            };

            if ($period === 'week') {
                $cursor = $from->copy()->startOfDay();
                while ($cursor->lte($to)) {
                    $labels[] = $cursor->format('D');
                    $values[] = $countForBucket($cursor, $cursor->copy()->endOfDay());
                    $cursor->addDay();
                }
            } elseif ($period === 'month') {
                $cursor = $from->copy()->startOfWeek();
                while ($cursor->lte($to)) {
                    $weekEnd = $cursor->copy()->endOfWeek()->min($to);
                    $labels[] = $cursor->format('M j');
                    $values[] = $countForBucket($cursor, $weekEnd);
                    $cursor->addWeek();
                }
            } else {
                $cursor = $from->copy()->startOfMonth();
                while ($cursor->lte($to)) {
                    $monthEnd = $cursor->copy()->endOfMonth()->min($to);
                    $labels[] = $cursor->format('M');
                    $values[] = $countForBucket($cursor, $monthEnd);
                    $cursor->addMonth();
                }
            }

            return [
                'labels' => $labels,
                'values' => $values,
            ];
        };

        $chartPeriods = [];
        foreach (['week', 'month', 'year'] as $period) {
            [$from, $to] = $periodBounds($period);
            $scoped = $transactionsInRange($from, $to);

            $chartPeriods[$period] = [
                'range_label' => $periodRangeLabel($from, $to),
                'transaction_trend' => $trendForPeriod($period, $from, $to),
                'health_concerns' => $concernsForTransactions($scoped),
            ];
        }

        $illnessByPurokRows = collect($illnessByPurok)
            ->sortByDesc('cases')
            ->values()
            ->all();

        $coveredPurokNames = collect($meta['covered_purok_names'] ?? [])
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique()
            ->values();

        if ($coveredPurokNames->isEmpty()) {
            $coveredPurokNames = collect($illnessByPurokRows)->pluck('purok')->filter()->values();
        } else {
            $coveredPurokNames = $coveredPurokNames
                ->merge(collect($illnessByPurokRows)->pluck('purok'))
                ->unique()
                ->sort()
                ->values();
        }

        $maxPurokCases = (int) collect($illnessByPurokRows)->max('cases');
        $illnessByPurokIndex = collect($illnessByPurokRows)->keyBy('purok');

        $householdsByPurok = $residents
            ->groupBy(function (ResidentRequest $resident) {
                return trim((string) ($resident->purok?->name ?? $resident->user?->purok?->name ?? '')) ?: 'Unassigned';
            })
            ->map(function (Collection $group) {
                return $group
                    ->map(function (ResidentRequest $resident) {
                        $familyNumber = trim((string) ($resident->family_number ?? ''));

                        return $familyNumber !== '' ? 'family:'.$familyNumber : 'resident:'.$resident->id;
                    })
                    ->unique()
                    ->count();
            });

        $purokAlertLevels = $coveredPurokNames
            ->map(function (string $purokName) use ($illnessByPurokIndex, $maxPurokCases, $householdsByPurok) {
                $row = $illnessByPurokIndex->get($purokName, [
                    'purok' => $purokName,
                    'cases' => 0,
                    'Hypertension' => 0,
                    'Diabetes' => 0,
                    'Hyperlipidemia' => 0,
                ]);
                $cases = (int) ($row['cases'] ?? 0);
                $ratio = $maxPurokCases > 0 ? ($cases / $maxPurokCases) : 0;

                if ($cases === 0) {
                    $level = 'clear';
                    $label = 'Clear';
                } elseif ($ratio >= 0.7 || ($cases === $maxPurokCases && $maxPurokCases >= 3)) {
                    $level = 'high';
                    $label = 'High alert';
                } elseif ($ratio >= 0.35) {
                    $level = 'moderate';
                    $label = 'Watch';
                } else {
                    $level = 'low';
                    $label = 'Low';
                }

                return [
                    'purok' => $purokName,
                    'households' => (int) ($householdsByPurok->get($purokName) ?? 0),
                    'cases' => $cases,
                    'Hypertension' => (int) ($row['Hypertension'] ?? 0),
                    'Diabetes' => (int) ($row['Diabetes'] ?? 0),
                    'Hyperlipidemia' => (int) ($row['Hyperlipidemia'] ?? 0),
                    'level' => $level,
                    'level_label' => $label,
                ];
            })
            ->sort(function (array $a, array $b) {
                $caseCmp = ($b['cases'] ?? 0) <=> ($a['cases'] ?? 0);

                return $caseCmp !== 0
                    ? $caseCmp
                    : strcasecmp((string) ($a['purok'] ?? ''), (string) ($b['purok'] ?? ''));
            })
            ->values()
            ->all();

        return [
            'assignedPurokLabel' => $meta['scope_label'] ?? '',
            'purokCoverageLabel' => $meta['purok_coverage_label'] ?? ($meta['scope_label'] ?? ''),
            'managedBhwCount' => $meta['managed_bhw_count'] ?? null,
            'purokCount' => $meta['purok_count'] ?? null,
            'bhwStats' => [
                'total_households_assigned' => $totalHouseholdsAssigned,
                'total_people_assigned' => $totalPeopleAssigned,
                'active_illness_cases' => count($activeIllnessKeys),
                'households_with_concern' => count($householdsWithConcern),
                'transactions_this_month' => $transactionsThisMonth,
                'medicine_pcs_dispensed' => $medicineDispensedPcs,
                'pending_requests' => (int) ($meta['pending_requests'] ?? 0),
                'upcoming_consultations' => $upcomingConsultations,
                'pregnant_women_monitored' => $pregnantMonitored,
                'senior_citizens_monitored' => max($seniorMonitored, $seniorByAge),
                'active_monitoring_cases' => $monitoringCases->count(),
                'total_residents' => $residents->count(),
            ],
            'dashboardCharts' => [
                'default_period' => 'month',
                'chart_periods' => $chartPeriods,
            ],
            'monitoringMix' => [
                'labels' => ['Pregnant', 'Senior', 'General'],
                'values' => [$pregnantMonitored, $seniorMonitored, $generalMonitored],
                'colors' => ['#EC4899', '#8B5CF6', '#4A90E2'],
                'members' => [
                    'Pregnant' => $mapMonitoringMembers($monitoringCases->where('member_type', HouseholdMonitoringCase::TYPE_PREGNANT)),
                    'Senior' => $mapMonitoringMembers($monitoringCases->where('member_type', HouseholdMonitoringCase::TYPE_SENIOR)),
                    'General' => $mapMonitoringMembers($monitoringCases->where('member_type', HouseholdMonitoringCase::TYPE_GENERAL)),
                ],
            ],
            'illnessByPurok' => $illnessByPurokRows,
            'purokAlertLevels' => $purokAlertLevels,
            'commonIllnesses' => [
                'labels' => array_keys($conditionCounts),
                'values' => array_values($conditionCounts),
                'colors' => $conditionColors,
            ],
        ];
    }
}
