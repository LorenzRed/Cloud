<?php

namespace App\Support;

use App\Models\Barangay;
use App\Models\Consultation;
use App\Models\HouseholdMonitoringCase;
use App\Models\HouseholdMonitoringVisit;
use App\Models\Purok;
use App\Models\ResidentRequest;
use App\Models\ResidentTransaction;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class BhwReportBuilder
{
    /**
     * @return array{0: ?string, 1: ?string, 2: string, 3: string}
     */
    public static function resolvePeriod(?string $period, ?string $customFrom = null, ?string $customTo = null): array
    {
        return BnsReportBuilder::resolvePeriod($period, $customFrom, $customTo);
    }

    /**
     * @return array<string, mixed>
     */
    public static function build(User $bhw, ?string $fromDate = null, ?string $toDate = null, string $periodKey = 'this_month'): array
    {
        $bhw->loadMissing(['assignedPuroks', 'purok']);

        $assignedPurokNames = $bhw->assignedPuroks
            ->pluck('name')
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique()
            ->values();

        if ($assignedPurokNames->isEmpty()) {
            $fallbackPurok = trim((string) ($bhw->purok?->name ?? ''));
            if ($fallbackPurok !== '') {
                $assignedPurokNames = collect([$fallbackPurok]);
            }
        }

        $assignedPurokLabel = $assignedPurokNames->isNotEmpty()
            ? $assignedPurokNames->implode(', ')
            : 'Unassigned purok';

        $residents = ResidentRequest::query()
            ->forBhw($bhw)
            ->where('status', 'accepted')
            ->with(['purok', 'user.purok'])
            ->get();

        $pendingRequests = ResidentRequest::query()
            ->forBhw($bhw)
            ->where('status', 'pending')
            ->count();

        return self::buildFromResidents($residents, $assignedPurokLabel, $pendingRequests, $fromDate, $toDate, $periodKey);
    }

    /**
     * @return array<string, mixed>
     */
    public static function buildForHead(User $head, ?string $fromDate = null, ?string $toDate = null, string $periodKey = 'this_month'): array
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

        $scopeLabel = $barangay.($purokNames->isNotEmpty() ? ' · '.$purokNames->implode(', ') : '');

        $report = self::buildFromResidents($residents, $scopeLabel, $pendingRequests, $fromDate, $toDate, $periodKey);
        $report['barangay_name'] = $barangay;
        $report['managed_bhw_count'] = $workers->count();
        $report['purok_count'] = $purokNames->count();

        return $report;
    }

    /**
     * Barangay-wide report for Barangay Captain (all accepted residents / puroks).
     *
     * @return array<string, mixed>
     */
    public static function buildForCaptain(?Barangay $barangay, ?string $fromDate = null, ?string $toDate = null, string $periodKey = 'this_month'): array
    {
        if (! $barangay) {
            $report = self::buildFromResidents(collect(), 'No barangay assigned yet', 0, $fromDate, $toDate, $periodKey);
            $report['barangay_name'] = '';
            $report['managed_bhw_count'] = 0;
            $report['purok_count'] = 0;

            return $report;
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

        $scopeLabel = $barangayName.($purokNames->isNotEmpty() ? ' · '.$purokNames->implode(', ') : '');

        $report = self::buildFromResidents($residents, $scopeLabel, $pendingRequests, $fromDate, $toDate, $periodKey);
        $report['barangay_name'] = $barangayName;
        $report['managed_bhw_count'] = $bhwCount;
        $report['purok_count'] = $purokNames->count();

        return $report;
    }

    /**
     * @param  Collection<int, ResidentRequest>  $residents
     * @return array<string, mixed>
     */
    public static function buildFromResidents(
        Collection $residents,
        string $assignedPurokLabel,
        int $pendingRequests,
        ?string $fromDate = null,
        ?string $toDate = null,
        string $periodKey = 'this_month'
    ): array {
        $residentIds = $residents->pluck('id');

        $from = filled($fromDate) ? Carbon::parse($fromDate)->startOfDay() : null;
        $to = filled($toDate) ? Carbon::parse($toDate)->endOfDay() : null;

        $transactions = $residentIds->isEmpty()
            ? collect()
            : ResidentTransaction::query()
                ->whereIn('resident_request_id', $residentIds)
                ->with('residentRequest')
                ->orderByDesc('transaction_date')
                ->orderByDesc('id')
                ->get();

        $periodTransactions = self::filterByDate($transactions, 'transaction_date', $from, $to);

        // Ensure purok is available for illness-by-purok breakdown when relation was lazy.
        $periodTransactions->loadMissing(['residentRequest.purok', 'residentRequest.user.purok']);

        $monitoringCases = $residentIds->isEmpty()
            ? collect()
            : HouseholdMonitoringCase::query()
                ->whereIn('resident_request_id', $residentIds)
                ->with(['residentRequest', 'visits' => fn ($q) => $q->orderByDesc('visit_date')])
                ->get();

        $activeMonitoring = $monitoringCases->where('is_active', true);
        $pregnantMonitored = $activeMonitoring->where('member_type', HouseholdMonitoringCase::TYPE_PREGNANT)->count();
        $seniorMonitored = $activeMonitoring->where('member_type', HouseholdMonitoringCase::TYPE_SENIOR)->count();
        $generalMonitored = $activeMonitoring->where('member_type', HouseholdMonitoringCase::TYPE_GENERAL)->count();

        $visits = $residentIds->isEmpty()
            ? collect()
            : HouseholdMonitoringVisit::query()
                ->whereHas('monitoringCase', fn ($q) => $q->whereIn('resident_request_id', $residentIds))
                ->with('monitoringCase.residentRequest')
                ->orderByDesc('visit_date')
                ->get();

        $periodVisits = self::filterByDate($visits, 'visit_date', $from, $to);

        $consultations = $residentIds->isEmpty()
            ? collect()
            : Consultation::query()
                ->whereIn('resident_request_id', $residentIds)
                ->with('residentRequest')
                ->orderByDesc('scheduled_at')
                ->get();

        $periodConsultations = $consultations->filter(function (Consultation $consultation) use ($from, $to) {
            if (! $consultation->scheduled_at) {
                return false;
            }

            if ($from && $consultation->scheduled_at->lt($from)) {
                return false;
            }

            if ($to && $consultation->scheduled_at->gt($to)) {
                return false;
            }

            return true;
        });

        $householdCount = $residents
            ->map(function (ResidentRequest $resident) {
                $familyNumber = trim((string) ($resident->family_number ?? ''));

                return $familyNumber !== '' ? 'family:'.$familyNumber : 'resident:'.$resident->id;
            })
            ->unique()
            ->count();

        $conditionCounts = [
            'Hypertension' => 0,
            'Diabetes' => 0,
            'Hyperlipidemia' => 0,
        ];
        $activeIllnessKeys = [];
        $householdsWithConcern = [];
        $medicinePcs = 0;

        foreach ($periodTransactions as $transaction) {
            foreach ($transaction->medicationEntries() as $entry) {
                $medicinePcs += (int) ($entry['pcs'] ?? 0);
            }

            $conditions = $transaction->inferredConditions();
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
        }

        $concernMembers = $periodTransactions
            ->filter(fn (ResidentTransaction $tx) => $tx->inferredConditions() !== [])
            ->take(20)
            ->map(function (ResidentTransaction $tx) {
                return [
                    'member_name' => $tx->recipientLabel(),
                    'household' => $tx->residentRequest?->full_name ?: 'Household',
                    'purok' => trim((string) ($tx->residentRequest?->purok?->name ?? $tx->residentRequest?->user?->purok?->name ?? '')) ?: '—',
                    'date' => $tx->transaction_date?->format('M d, Y') ?: '—',
                    'conditions' => implode(', ', $tx->inferredConditions()),
                    'medicines' => collect($tx->medicationEntries())
                        ->map(fn (array $entry) => $entry['label'].' '.$entry['pcs'].' pcs')
                        ->implode(', '),
                ];
            })
            ->values()
            ->all();

        $householdsByPurok = $residents
            ->groupBy(function (ResidentRequest $resident) {
                return trim((string) ($resident->purok?->name ?? $resident->user?->purok?->name ?? '')) ?: 'Unassigned';
            })
            ->map(fn (Collection $group, string $purok) => [
                'purok' => $purok,
                'count' => $group
                    ->map(function (ResidentRequest $resident) {
                        $familyNumber = trim((string) ($resident->family_number ?? ''));

                        return $familyNumber !== '' ? 'family:'.$familyNumber : 'resident:'.$resident->id;
                    })
                    ->unique()
                    ->count(),
            ])
            ->sortByDesc('count')
            ->values()
            ->all();

        $illnessByPurok = [];
        foreach ($periodTransactions as $transaction) {
            $conditions = $transaction->inferredConditions();
            if ($conditions === []) {
                continue;
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

        $illnessByPurokRows = collect($illnessByPurok)
            ->sortByDesc('cases')
            ->values()
            ->all();

        $recentActivities = collect()
            ->merge($periodTransactions->take(15)->map(function (ResidentTransaction $tx) {
                $conditions = $tx->inferredConditions();

                return [
                    'sort' => optional($tx->transaction_date)->timestamp ?? 0,
                    'date' => $tx->transaction_date?->format('M d, Y') ?: '—',
                    'type' => 'Transaction',
                    'name' => $tx->recipientLabel(),
                    'detail' => $conditions !== []
                        ? implode(', ', $conditions)
                        : (collect($tx->medicationEntries())->pluck('label')->implode(', ') ?: 'Medicine dispensed'),
                ];
            }))
            ->merge($periodVisits->take(15)->map(function (HouseholdMonitoringVisit $visit) {
                $case = $visit->monitoringCase;

                return [
                    'sort' => optional($visit->visit_date)->timestamp ?? 0,
                    'date' => $visit->visit_date?->format('M d, Y') ?: '—',
                    'type' => 'Monitoring visit',
                    'name' => $case?->member_name ?: ($case?->residentRequest?->full_name ?: 'Member'),
                    'detail' => trim(collect([
                        $case?->typeLabel(),
                        $visit->blood_pressure ? 'BP '.$visit->blood_pressure : null,
                        $visit->bmi_value ? 'BMI '.$visit->bmi_value : null,
                    ])->filter()->implode(' · ')),
                ];
            }))
            ->merge($periodConsultations->take(15)->map(function (Consultation $consultation) {
                return [
                    'sort' => optional($consultation->scheduled_at)->timestamp ?? 0,
                    'date' => $consultation->scheduled_at?->format('M d, Y') ?: '—',
                    'type' => 'Consultation',
                    'name' => $consultation->residentRequest?->full_name ?: 'Resident',
                    'detail' => $consultation->medical_checkup ?: 'Scheduled consultation',
                ];
            }))
            ->sortByDesc('sort')
            ->take(20)
            ->values()
            ->map(fn (array $row) => collect($row)->except('sort')->all())
            ->all();

        $chartGranularity = match ($periodKey) {
            'this_week', 'last_week', 'weekly' => 'daily',
            'this_month', 'last_month', 'monthly' => 'weekly',
            default => 'monthly',
        };

        return [
            'generated_at' => now()->format('M d, Y'),
            'assigned_purok_label' => $assignedPurokLabel,
            'period' => [
                'key' => $periodKey,
                'from_label' => $from?->format('M d, Y') ?? 'All time',
                'to_label' => $to?->format('M d, Y') ?? 'Present',
                'chart_granularity' => $chartGranularity,
            ],
            'overview' => [
                'households' => $householdCount,
                'residents' => $residents->count(),
                'pending_requests' => $pendingRequests,
                'active_monitoring' => $activeMonitoring->count(),
                'health_concern_cases' => count($activeIllnessKeys),
                'upcoming_consultations' => $consultations->filter(
                    fn (Consultation $c) => $c->scheduled_at && $c->scheduled_at->gte(now())
                )->count(),
            ],
            'period_activity' => [
                'transactions' => $periodTransactions->count(),
                'medicine_pcs' => $medicinePcs,
                'households_with_concern' => count($householdsWithConcern),
                'monitoring_visits' => $periodVisits->count(),
                'consultations' => $periodConsultations->count(),
                'pregnant_monitored' => $pregnantMonitored,
            ],
            'health_concerns' => [
                'Hypertension' => $conditionCounts['Hypertension'],
                'Diabetes' => $conditionCounts['Diabetes'],
                'Hyperlipidemia' => $conditionCounts['Hyperlipidemia'],
                'chart' => [
                    'labels' => array_keys($conditionCounts),
                    'values' => array_values($conditionCounts),
                    'colors' => ['#BE123C', '#0EA5E9', '#F59E0B'],
                ],
            ],
            'monitoring' => [
                'pregnant' => $pregnantMonitored,
                'senior' => $seniorMonitored,
                'general' => $generalMonitored,
                'chart' => [
                    'labels' => ['Pregnant', 'Senior', 'General'],
                    'values' => [$pregnantMonitored, $seniorMonitored, $generalMonitored],
                    'colors' => ['#EC4899', '#8B5CF6', '#4A90E2'],
                ],
            ],
            'concern_members' => $concernMembers,
            'households_by_purok' => $householdsByPurok,
            'illness_by_purok' => $illnessByPurokRows,
            'activity_chart' => self::activityChart($periodTransactions, $periodVisits, $periodConsultations, $chartGranularity, $from, $to),
            'recent_activities' => $recentActivities,
        ];
    }

    /**
     * @param  Collection<int, mixed>  $items
     * @return Collection<int, mixed>
     */
    private static function filterByDate(Collection $items, string $dateField, ?Carbon $from, ?Carbon $to): Collection
    {
        return $items->filter(function ($item) use ($dateField, $from, $to) {
            $date = $item->{$dateField} ?? null;
            if (! $date) {
                return false;
            }

            $carbon = $date instanceof Carbon ? $date->copy()->startOfDay() : Carbon::parse($date)->startOfDay();

            if ($from && $carbon->lt($from->copy()->startOfDay())) {
                return false;
            }

            if ($to && $carbon->gt($to->copy()->startOfDay())) {
                return false;
            }

            return true;
        })->values();
    }

    /**
     * @param  Collection<int, ResidentTransaction>  $transactions
     * @param  Collection<int, HouseholdMonitoringVisit>  $visits
     * @param  Collection<int, Consultation>  $consultations
     * @return array{labels: list<string>, transactions: list<int>, visits: list<int>, consultations: list<int>}
     */
    private static function activityChart(
        Collection $transactions,
        Collection $visits,
        Collection $consultations,
        string $granularity,
        ?Carbon $from,
        ?Carbon $to
    ): array {
        $end = ($to ?? now())->copy()->endOfDay();
        $start = ($from ?? now()->copy()->startOfYear())->copy()->startOfDay();

        $labels = [];
        $buckets = [];

        if ($granularity === 'daily') {
            $cursor = $start->copy();
            while ($cursor->lte($end)) {
                $key = $cursor->format('Y-m-d');
                $labels[] = $cursor->format('D');
                $buckets[$key] = ['transactions' => 0, 'visits' => 0, 'consultations' => 0];
                $cursor->addDay();
            }
        } elseif ($granularity === 'weekly') {
            $cursor = $start->copy()->startOfWeek();
            while ($cursor->lte($end)) {
                $key = $cursor->format('o-W');
                $labels[] = $cursor->format('M j');
                $buckets[$key] = ['transactions' => 0, 'visits' => 0, 'consultations' => 0];
                $cursor->addWeek();
            }
        } else {
            $cursor = $start->copy()->startOfMonth();
            while ($cursor->lte($end)) {
                $key = $cursor->format('Y-m');
                $labels[] = $cursor->format('M');
                $buckets[$key] = ['transactions' => 0, 'visits' => 0, 'consultations' => 0];
                $cursor->addMonth();
            }
        }

        $bucketKey = function (Carbon $date) use ($granularity): string {
            return match ($granularity) {
                'daily' => $date->format('Y-m-d'),
                'weekly' => $date->format('o-W'),
                default => $date->format('Y-m'),
            };
        };

        foreach ($transactions as $tx) {
            if (! $tx->transaction_date) {
                continue;
            }
            $key = $bucketKey($tx->transaction_date);
            if (isset($buckets[$key])) {
                $buckets[$key]['transactions']++;
            }
        }

        foreach ($visits as $visit) {
            if (! $visit->visit_date) {
                continue;
            }
            $key = $bucketKey($visit->visit_date);
            if (isset($buckets[$key])) {
                $buckets[$key]['visits']++;
            }
        }

        foreach ($consultations as $consultation) {
            if (! $consultation->scheduled_at) {
                continue;
            }
            $key = $bucketKey($consultation->scheduled_at);
            if (isset($buckets[$key])) {
                $buckets[$key]['consultations']++;
            }
        }

        return [
            'labels' => $labels,
            'transactions' => array_values(array_column($buckets, 'transactions')),
            'visits' => array_values(array_column($buckets, 'visits')),
            'consultations' => array_values(array_column($buckets, 'consultations')),
        ];
    }
}
