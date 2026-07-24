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
                ->with(['residentRequest.purok'])
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

        $pregnantCases = $monitoringCases->where('member_type', HouseholdMonitoringCase::TYPE_PREGNANT);
        $pregnantMonitoredCount = $pregnantCases->count();
        $needMaintenanceCases = $monitoringCases->whereIn('member_type', [
            HouseholdMonitoringCase::TYPE_PREGNANT,
            HouseholdMonitoringCase::TYPE_SENIOR,
        ]);
        $needMaintenanceCount = $needMaintenanceCases->count();

        $householdSeniorMembers = collect();
        foreach ($residents as $resident) {
            $familyNumber = trim((string) ($resident->family_number ?? ''));
            $purokName = trim((string) ($resident->purok?->name ?? '')) ?: '—';
            $householdName = trim((string) ($resident->full_name ?? '')) ?: '—';

            foreach (is_array($resident->household_seniors) ? $resident->household_seniors : [] as $senior) {
                if (! is_array($senior)) {
                    continue;
                }

                $name = trim((string) ($senior['full_name'] ?? $senior['name'] ?? ''));
                if ($name === '') {
                    continue;
                }

                $householdSeniorMembers->push([
                    'name' => $name,
                    'family_number' => $familyNumber !== '' ? $familyNumber : 'Not assigned',
                    'household' => $householdName,
                    'purok' => $purokName,
                ]);
            }
        }

        $householdSeniorMembers = $householdSeniorMembers
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
        $householdSeniorCount = $householdSeniorMembers->count();

        $mapMonitoringMembers = function ($cases) {
            return $cases
                ->map(function (HouseholdMonitoringCase $case) {
                    $familyNumber = trim((string) ($case->residentRequest?->family_number ?? ''));
                    $reason = match ($case->member_type) {
                        HouseholdMonitoringCase::TYPE_PREGNANT => 'Pregnant',
                        HouseholdMonitoringCase::TYPE_SENIOR => 'Senior',
                        default => 'Maintenance',
                    };

                    return [
                        'name' => trim((string) ($case->member_name ?? '')) ?: ($case->residentRequest?->full_name ?: 'Household member'),
                        'family_number' => $familyNumber !== '' ? $familyNumber : 'Not assigned',
                        'household' => $case->residentRequest?->full_name ?: '—',
                        'purok' => trim((string) ($case->residentRequest?->purok?->name ?? '')) ?: '—',
                        'reason' => $reason,
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

        $householdKey = static function (ResidentRequest $resident): string {
            $familyNumber = trim((string) ($resident->family_number ?? ''));

            return $familyNumber !== '' ? 'family:'.$familyNumber : 'resident:'.$resident->id;
        };

        $purokNameOf = static function (ResidentRequest $resident): string {
            return trim((string) ($resident->purok?->name ?? $resident->user?->purok?->name ?? '')) ?: 'Unassigned';
        };

        $householdsByPurok = $residents
            ->groupBy($purokNameOf)
            ->map(function (Collection $group) use ($householdKey) {
                return $group
                    ->map($householdKey)
                    ->unique()
                    ->count();
            });

        $residentsByPurok = $residents
            ->groupBy($purokNameOf)
            ->map(function (Collection $group) use ($householdKey, $peopleInHousehold) {
                return $group
                    ->groupBy($householdKey)
                    ->sum(fn ($household) => $household->max($peopleInHousehold));
            });

        $purokAlertLevels = $coveredPurokNames
            ->map(function (string $purokName) use ($illnessByPurokIndex, $maxPurokCases, $householdsByPurok, $residentsByPurok) {
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
                    'residents' => (int) ($residentsByPurok->get($purokName) ?? 0),
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
                'pregnant_women_monitored' => $pregnantMonitoredCount,
                'senior_citizens_monitored' => $householdSeniorCount,
                'active_monitoring_cases' => $monitoringCases->count(),
                'total_residents' => $residents->count(),
            ],
            'dashboardCharts' => [
                'default_period' => 'month',
                'chart_periods' => $chartPeriods,
            ],
            'monitoringMix' => [
                'labels' => ['Pregnant', 'Senior', 'Need maintenance'],
                'values' => [$pregnantMonitoredCount, $householdSeniorCount, $needMaintenanceCount],
                'colors' => ['#EC4899', '#8B5CF6', '#4A90E2'],
                'members' => [
                    'Pregnant' => $mapMonitoringMembers($pregnantCases),
                    'Senior' => $householdSeniorMembers->all(),
                    'Need maintenance' => $mapMonitoringMembers($needMaintenanceCases),
                ],
            ],
            'illnessByPurok' => $illnessByPurokRows,
            'purokAlertLevels' => $purokAlertLevels,
            'commonIllnesses' => [
                'labels' => array_keys($conditionCounts),
                'values' => array_values($conditionCounts),
                'colors' => $conditionColors,
            ],
            'populationDistribution' => self::populationDistribution($residents),
        ];
    }

    /**
     * Age buckets for all household members, filterable by gender, plus gender totals.
     *
     * @param  Collection<int, ResidentRequest>  $residents
     * @return array{
     *     age_labels: list<string>,
     *     age_by_gender: array{all: list<int>, male: list<int>, female: list<int>},
     *     gender: array{labels: list<string>, values: list<int>, colors: list<string>}
     * }
     */
    public static function populationDistribution(Collection $residents): array
    {
        $labels = ['0–17', '18–35', '36–59', '60+'];
        $ageByGender = [
            'all' => [0, 0, 0, 0],
            'male' => [0, 0, 0, 0],
            'female' => [0, 0, 0, 0],
        ];
        $genderCounts = ['Male' => 0, 'Female' => 0];

        foreach (self::householdPeople($residents) as $person) {
            $age = $person['age'];
            $gender = $person['gender'];
            $bucket = self::ageBucketIndex($age);

            if ($bucket === null) {
                continue;
            }

            $ageByGender['all'][$bucket]++;

            if ($gender === 'Male') {
                $ageByGender['male'][$bucket]++;
                $genderCounts['Male']++;
            } elseif ($gender === 'Female') {
                $ageByGender['female'][$bucket]++;
                $genderCounts['Female']++;
            }
        }

        return [
            'age_labels' => $labels,
            'age_by_gender' => $ageByGender,
            'gender' => [
                'labels' => ['Male', 'Female'],
                'values' => [$genderCounts['Male'], $genderCounts['Female']],
                'colors' => ['#4A90E2', '#EC4899'],
            ],
        ];
    }

    /**
     * @param  Collection<int, ResidentRequest>  $residents
     * @return list<array{age: int|null, gender: string|null}>
     */
    private static function householdPeople(Collection $residents): array
    {
        $people = [];

        foreach ($residents as $resident) {
            $headGender = self::normalizeGender(
                $resident->user?->gender
                ?? match ($resident->parent_role) {
                    ResidentRequest::PARENT_ROLE_FATHER => 'Male',
                    ResidentRequest::PARENT_ROLE_MOTHER => 'Female',
                    default => null,
                }
            );
            $headAge = self::resolveAge(
                isset($resident->age) ? (int) $resident->age : null,
                $resident->user?->date_of_birth?->toDateString()
            );

            $people[] = ['age' => $headAge, 'gender' => $headGender];

            $spouse = is_array($resident->spouse_details) ? $resident->spouse_details : [];
            if ($spouse !== [] || filled($resident->spouse_full_name)) {
                $people[] = [
                    'age' => self::resolveAge(
                        isset($spouse['age']) ? (int) $spouse['age'] : null,
                        isset($spouse['birthdate']) ? (string) $spouse['birthdate'] : null
                    ),
                    'gender' => self::normalizeGender($spouse['gender'] ?? null)
                        ?? ($headGender === 'Male' ? 'Female' : ($headGender === 'Female' ? 'Male' : null)),
                ];
            }

            foreach (is_array($resident->household_children) ? $resident->household_children : [] as $child) {
                if (! is_array($child)) {
                    continue;
                }

                $people[] = [
                    'age' => self::resolveAge(
                        isset($child['age']) ? (int) $child['age'] : null,
                        isset($child['birthdate']) ? (string) $child['birthdate'] : null
                    ),
                    'gender' => self::normalizeGender($child['gender'] ?? null),
                ];
            }

            foreach (is_array($resident->household_seniors) ? $resident->household_seniors : [] as $senior) {
                if (! is_array($senior)) {
                    continue;
                }

                $people[] = [
                    'age' => self::resolveAge(
                        isset($senior['age']) ? (int) $senior['age'] : null,
                        isset($senior['birthdate']) ? (string) $senior['birthdate'] : null
                    ),
                    'gender' => self::normalizeGender($senior['gender'] ?? null),
                ];
            }
        }

        return $people;
    }

    private static function resolveAge(?int $age, ?string $birthdate): ?int
    {
        if ($age !== null && $age >= 0 && $age <= 130) {
            return $age;
        }

        return PersonAge::ageInYears($birthdate);
    }

    private static function normalizeGender(mixed $gender): ?string
    {
        $value = ucfirst(strtolower(trim((string) ($gender ?? ''))));

        return in_array($value, ['Male', 'Female'], true) ? $value : null;
    }

    private static function ageBucketIndex(?int $age): ?int
    {
        if ($age === null || $age < 0) {
            return null;
        }

        return match (true) {
            $age <= 17 => 0,
            $age <= 35 => 1,
            $age <= 59 => 2,
            default => 3,
        };
    }
}
