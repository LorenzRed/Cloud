<?php

namespace App\Support;

use App\Models\BnsChildDewormingLog;
use App\Models\BnsChildImmunization;
use App\Models\BnsChildVitaminALog;
use App\Models\BnsChildWeightLog;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class BnsReportBuilder
{
    /**
     * @return array{0: ?string, 1: ?string, 2: string, 3: string}
     */
    public static function resolvePeriod(?string $period, ?string $customFrom = null, ?string $customTo = null): array
    {
        $period = $period ?: 'this_month';
        $now = now();

        if ($period === 'custom' && (filled($customFrom) || filled($customTo))) {
            return [$customFrom, $customTo, 'custom', 'Custom range'];
        }

        return match ($period) {
            'this_week', 'weekly' => [
                $now->copy()->startOfWeek()->toDateString(),
                $now->copy()->endOfWeek()->toDateString(),
                'this_week',
                'This week',
            ],
            'last_week' => [
                $now->copy()->subWeek()->startOfWeek()->toDateString(),
                $now->copy()->subWeek()->endOfWeek()->toDateString(),
                'last_week',
                'Last week',
            ],
            'this_month', 'monthly' => [
                $now->copy()->startOfMonth()->toDateString(),
                $now->copy()->endOfMonth()->toDateString(),
                'this_month',
                'This month',
            ],
            'last_month' => [
                $now->copy()->subMonth()->startOfMonth()->toDateString(),
                $now->copy()->subMonth()->endOfMonth()->toDateString(),
                'last_month',
                'Last month',
            ],
            default => [null, null, 'all', 'All time'],
        };
    }

    /**
     * @return array<string, mixed>
     */
    public static function build(User $bns, ?string $fromDate = null, ?string $toDate = null, string $periodKey = 'this_month'): array
    {
        $barangayName = BnsChildMonitoring::barangayNameFor($bns) ?? 'Unassigned';
        $children = BnsChildMonitoring::childrenUnderFiveFor($bns);
        $childKeys = $children->pluck('id')->map(fn ($id) => (string) $id)->all();

        $from = filled($fromDate) ? Carbon::parse($fromDate)->startOfDay() : null;
        $to = filled($toDate) ? Carbon::parse($toDate)->endOfDay() : null;

        $healthSummaries = BnsChildHealth::summariesForChildren($children);
        $nutritionStats = BnsChildNutrition::aggregateStats($healthSummaries);
        $nutritionChildren = self::nutritionChildrenList($children, $healthSummaries);

        $fullyImmunized = collect($healthSummaries)->filter(fn ($s) => $s['fully_immunized'] ?? false)->count();
        $withMissedDoses = collect($healthSummaries)->filter(fn ($s) => ($s['immunization_missed'] ?? 0) > 0)->count();
        $withWeight = BnsChildWeightLog::query()
            ->whereIn('child_key', $childKeys)
            ->distinct('child_key')
            ->count('child_key');

        $immunizationQuery = BnsChildImmunization::query()->whereIn('child_key', $childKeys);
        $immunizationStats = [
            'given' => (clone $immunizationQuery)->where('status', BnsChildImmunization::STATUS_GIVEN)->count(),
            'missed' => (clone $immunizationQuery)->where('status', BnsChildImmunization::STATUS_MISSED)->count(),
            'pending' => (clone $immunizationQuery)->where('status', BnsChildImmunization::STATUS_PENDING)->count(),
        ];

        $weightInPeriod = self::countInPeriod(
            BnsChildWeightLog::query()->whereIn('child_key', $childKeys),
            'recorded_on',
            $from,
            $to
        );

        $dewormingInPeriod = self::countInPeriod(
            BnsChildDewormingLog::query()->whereIn('child_key', $childKeys),
            'given_on',
            $from,
            $to
        );

        $vitaminAInPeriod = self::countInPeriod(
            BnsChildVitaminALog::query()->whereIn('child_key', $childKeys),
            'given_on',
            $from,
            $to
        );

        $immunizationGivenInPeriod = self::countInPeriod(
            BnsChildImmunization::query()
                ->whereIn('child_key', $childKeys)
                ->where('status', BnsChildImmunization::STATUS_GIVEN),
            'given_date',
            $from,
            $to
        );

        $childrenWeighedInPeriod = self::distinctChildCountInPeriod(
            BnsChildWeightLog::query()->whereIn('child_key', $childKeys),
            'recorded_on',
            $from,
            $to
        );

        $avgWeight = BnsChildWeightLog::query()
            ->whereIn('child_key', $childKeys)
            ->when($from, fn ($q) => $q->whereDate('recorded_on', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('recorded_on', '<=', $to))
            ->avg('weight_kg');

        $childrenByPurok = $children
            ->groupBy('purok')
            ->map(fn (Collection $group, string $purok) => [
                'purok' => $purok,
                'count' => $group->count(),
            ])
            ->sortBy('purok')
            ->values()
            ->all();

        $chartRangeStart = $from?->copy() ?? now()->subMonths(5)->startOfMonth();
        $chartRangeEnd = $to?->copy() ?? now()->endOfMonth();
        if ($chartRangeStart->gt($chartRangeEnd)) {
            $chartRangeStart = $chartRangeEnd->copy()->subMonths(5)->startOfMonth();
        }

        $activityChart = match (true) {
            in_array($periodKey, ['this_week', 'last_week'], true) && $from && $to
                => self::dailyActivitySeries($childKeys, $from, $to),
            in_array($periodKey, ['this_month', 'last_month'], true) && $from && $to
                => self::weeklyActivitySeries($childKeys, $from, $to),
            default => self::monthlyActivitySeries($childKeys, $chartRangeStart, $chartRangeEnd),
        };

        $chartGranularity = match (true) {
            in_array($periodKey, ['this_week', 'last_week'], true) => 'daily',
            in_array($periodKey, ['this_month', 'last_month'], true) => 'weekly',
            default => 'monthly',
        };

        $recentActivities = self::recentActivities($childKeys, $children, $from, $to);

        return [
            'barangay_name' => $barangayName,
            'generated_at' => now()->format('M d, Y g:i A'),
            'period' => [
                'key' => $periodKey,
                'from' => $from?->toDateString(),
                'from_label' => $from?->format('M d, Y') ?? 'All time',
                'to' => $to?->toDateString(),
                'to_label' => $to?->format('M d, Y') ?? 'Present',
                'chart_granularity' => $chartGranularity,
            ],
            'overview' => [
                'total_children' => $children->count(),
                'households' => $children->pluck('resident_request_id')->unique()->count(),
                'puroks' => $children->pluck('purok')->unique()->count(),
                'children_with_weight' => $withWeight,
                'fully_immunized' => $fullyImmunized,
                'incomplete_immunization' => $children->count() - $fullyImmunized,
                'children_with_missed_doses' => $withMissedDoses,
            ],
            'period_activity' => [
                'weight_entries' => $weightInPeriod,
                'children_weighed' => $childrenWeighedInPeriod,
                'avg_weight_kg' => $avgWeight ? round((float) $avgWeight, 2) : null,
                'deworming_given' => $dewormingInPeriod,
                'vitamin_a_given' => $vitaminAInPeriod,
                'immunizations_given' => $immunizationGivenInPeriod,
            ],
            'immunization' => [
                'given' => $immunizationStats['given'],
                'missed' => $immunizationStats['missed'],
                'pending' => $immunizationStats['pending'],
                'total_doses' => array_sum($immunizationStats),
            ],
            'nutrition' => [
                'malnutrition_cases' => $nutritionStats['malnutrition_cases'],
                'severely_malnourished' => $nutritionStats['severely_underweight_cases'],
                'malnourished' => $nutritionStats['underweight_cases'],
                'normal' => $nutritionStats['normal_bmi_count'],
                'overweight' => $nutritionStats['overweight_cases'],
                'no_weight_record' => $nutritionStats['no_weight_record'],
                'chart' => [
                    'labels' => ['Severely malnourished', 'Malnourished', 'Normal', 'Overweight', 'No weight record'],
                    'values' => [
                        $nutritionStats['severely_underweight_cases'],
                        $nutritionStats['underweight_cases'],
                        $nutritionStats['normal_bmi_count'],
                        $nutritionStats['overweight_cases'],
                        $nutritionStats['no_weight_record'],
                    ],
                ],
            ],
            'nutrition_children' => $nutritionChildren,
            'children_by_purok' => $childrenByPurok,
            'activity_chart' => $activityChart,
            'recent_activities' => $recentActivities,
        ];
    }

    private static function countInPeriod($query, string $dateColumn, ?Carbon $from, ?Carbon $to): int
    {
        return $query
            ->when($from, fn ($q) => $q->whereDate($dateColumn, '>=', $from))
            ->when($to, fn ($q) => $q->whereDate($dateColumn, '<=', $to))
            ->count();
    }

    private static function distinctChildCountInPeriod($query, string $dateColumn, ?Carbon $from, ?Carbon $to): int
    {
        return $query
            ->when($from, fn ($q) => $q->whereDate($dateColumn, '>=', $from))
            ->when($to, fn ($q) => $q->whereDate($dateColumn, '<=', $to))
            ->distinct('child_key')
            ->count('child_key');
    }

    /**
     * @param  list<string>  $childKeys
     * @return array{labels: list<string>, weight: list<int>, deworming: list<int>, vitamin_a: list<int>, immunization: list<int>}
     */
    private static function monthlyActivitySeries(array $childKeys, Carbon $start, Carbon $end): array
    {
        $labels = [];
        $weight = [];
        $deworming = [];
        $vitaminA = [];
        $immunization = [];

        $cursor = $start->copy()->startOfMonth();
        $endMonth = $end->copy()->endOfMonth();

        while ($cursor->lte($endMonth)) {
            $monthStart = $cursor->copy()->startOfMonth();
            $monthEnd = $cursor->copy()->endOfMonth();
            $labels[] = $cursor->format('M Y');

            $weight[] = BnsChildWeightLog::query()
                ->whereIn('child_key', $childKeys)
                ->whereBetween('recorded_on', [$monthStart, $monthEnd])
                ->count();

            $deworming[] = BnsChildDewormingLog::query()
                ->whereIn('child_key', $childKeys)
                ->whereBetween('given_on', [$monthStart, $monthEnd])
                ->count();

            $vitaminA[] = BnsChildVitaminALog::query()
                ->whereIn('child_key', $childKeys)
                ->whereBetween('given_on', [$monthStart, $monthEnd])
                ->count();

            $immunization[] = BnsChildImmunization::query()
                ->whereIn('child_key', $childKeys)
                ->where('status', BnsChildImmunization::STATUS_GIVEN)
                ->whereBetween('given_date', [$monthStart, $monthEnd])
                ->count();

            $cursor->addMonth();
        }

        return compact('labels', 'weight', 'deworming', 'vitaminA', 'immunization');
    }

    /**
     * @param  list<string>  $childKeys
     * @return array{labels: list<string>, weight: list<int>, deworming: list<int>, vitamin_a: list<int>, immunization: list<int>}
     */
    private static function dailyActivitySeries(array $childKeys, Carbon $start, Carbon $end): array
    {
        $labels = [];
        $weight = [];
        $deworming = [];
        $vitaminA = [];
        $immunization = [];

        $cursor = $start->copy()->startOfDay();
        $endDay = $end->copy()->endOfDay();

        while ($cursor->lte($endDay)) {
            $dayStart = $cursor->copy()->startOfDay();
            $dayEnd = $cursor->copy()->endOfDay();
            $labels[] = $cursor->format('D, M j');

            $weight[] = self::countBetween(BnsChildWeightLog::query()->whereIn('child_key', $childKeys), 'recorded_on', $dayStart, $dayEnd);
            $deworming[] = self::countBetween(BnsChildDewormingLog::query()->whereIn('child_key', $childKeys), 'given_on', $dayStart, $dayEnd);
            $vitaminA[] = self::countBetween(BnsChildVitaminALog::query()->whereIn('child_key', $childKeys), 'given_on', $dayStart, $dayEnd);
            $immunization[] = self::countBetween(
                BnsChildImmunization::query()->whereIn('child_key', $childKeys)->where('status', BnsChildImmunization::STATUS_GIVEN),
                'given_date',
                $dayStart,
                $dayEnd
            );

            $cursor->addDay();
        }

        return compact('labels', 'weight', 'deworming', 'vitaminA', 'immunization');
    }

    /**
     * @param  list<string>  $childKeys
     * @return array{labels: list<string>, weight: list<int>, deworming: list<int>, vitamin_a: list<int>, immunization: list<int>}
     */
    private static function weeklyActivitySeries(array $childKeys, Carbon $start, Carbon $end): array
    {
        $labels = [];
        $weight = [];
        $deworming = [];
        $vitaminA = [];
        $immunization = [];

        $cursor = $start->copy()->startOfWeek();
        $weekNum = 1;
        $endDay = $end->copy()->endOfDay();

        while ($cursor->lte($endDay)) {
            $weekStart = $cursor->copy()->startOfDay();
            $weekEnd = $cursor->copy()->endOfWeek()->endOfDay();
            if ($weekEnd->gt($endDay)) {
                $weekEnd = $endDay->copy();
            }
            if ($weekStart->lt($start)) {
                $weekStart = $start->copy()->startOfDay();
            }

            $labels[] = 'Week '.$weekNum.' ('.$weekStart->format('M j').'–'.$weekEnd->format('M j').')';

            $weight[] = self::countBetween(BnsChildWeightLog::query()->whereIn('child_key', $childKeys), 'recorded_on', $weekStart, $weekEnd);
            $deworming[] = self::countBetween(BnsChildDewormingLog::query()->whereIn('child_key', $childKeys), 'given_on', $weekStart, $weekEnd);
            $vitaminA[] = self::countBetween(BnsChildVitaminALog::query()->whereIn('child_key', $childKeys), 'given_on', $weekStart, $weekEnd);
            $immunization[] = self::countBetween(
                BnsChildImmunization::query()->whereIn('child_key', $childKeys)->where('status', BnsChildImmunization::STATUS_GIVEN),
                'given_date',
                $weekStart,
                $weekEnd
            );

            $cursor->addWeek()->startOfWeek();
            $weekNum++;
        }

        return compact('labels', 'weight', 'deworming', 'vitaminA', 'immunization');
    }

    private static function countBetween($query, string $dateColumn, Carbon $start, Carbon $end): int
    {
        return $query->whereBetween($dateColumn, [$start->toDateString(), $end->toDateString()])->count();
    }

    /**
     * @param  list<string>  $childKeys
     * @param  Collection<int, array<string, mixed>>  $children
     * @return list<array{type: string, child_name: string, detail: string, date: string}>
     */
    private static function recentActivities(array $childKeys, Collection $children, ?Carbon $from, ?Carbon $to): array
    {
        $nameByKey = $children->keyBy('id')->map(fn ($c) => $c['full_name'] ?? 'Child');

        $activities = collect();

        BnsChildWeightLog::query()
            ->whereIn('child_key', $childKeys)
            ->when($from, fn ($q) => $q->whereDate('recorded_on', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('recorded_on', '<=', $to))
            ->orderByDesc('recorded_on')
            ->limit(15)
            ->get()
            ->each(function (BnsChildWeightLog $log) use ($activities, $nameByKey, $children) {
                $child = $children->firstWhere('id', $log->child_key);
                $ageMonths = $log->age_in_months ?? BnsChildMonitoring::ageInMonths($child['birthdate'] ?? null);
                $nutrition = BnsChildNutrition::assess(
                    (float) $log->weight_kg,
                    $log->height_cm !== null ? (float) $log->height_cm : null,
                    $ageMonths,
                    (string) ($child['gender'] ?? '')
                );
                $heightDetail = $log->height_cm ? ' · '.number_format((float) $log->height_cm, 1).' cm' : '';
                $statusDetail = $nutrition['has_data'] ? ' · '.$nutrition['summary_label'] : '';

                $activities->push([
                    'type' => 'Weight',
                    'child_name' => $nameByKey[$log->child_key] ?? 'Child',
                    'detail' => number_format((float) $log->weight_kg, 2).' kg'.$heightDetail.$statusDetail,
                    'date' => $log->recorded_on?->format('M d, Y') ?? '',
                    'sort' => $log->recorded_on?->timestamp ?? 0,
                ]);
            });

        BnsChildDewormingLog::query()
            ->whereIn('child_key', $childKeys)
            ->when($from, fn ($q) => $q->whereDate('given_on', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('given_on', '<=', $to))
            ->orderByDesc('given_on')
            ->limit(10)
            ->get()
            ->each(function (BnsChildDewormingLog $log) use ($activities, $nameByKey) {
                $activities->push([
                    'type' => 'Deworming',
                    'child_name' => $nameByKey[$log->child_key] ?? 'Child',
                    'detail' => $log->medicine ?: 'Deworming recorded',
                    'date' => $log->given_on?->format('M d, Y') ?? '',
                    'sort' => $log->given_on?->timestamp ?? 0,
                ]);
            });

        BnsChildVitaminALog::query()
            ->whereIn('child_key', $childKeys)
            ->when($from, fn ($q) => $q->whereDate('given_on', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('given_on', '<=', $to))
            ->orderByDesc('given_on')
            ->limit(10)
            ->get()
            ->each(function (BnsChildVitaminALog $log) use ($activities, $nameByKey) {
                $activities->push([
                    'type' => 'Vitamin A',
                    'child_name' => $nameByKey[$log->child_key] ?? 'Child',
                    'detail' => 'Supplement given',
                    'date' => $log->given_on?->format('M d, Y') ?? '',
                    'sort' => $log->given_on?->timestamp ?? 0,
                ]);
            });

        BnsChildImmunization::query()
            ->whereIn('child_key', $childKeys)
            ->where('status', BnsChildImmunization::STATUS_GIVEN)
            ->when($from, fn ($q) => $q->whereDate('given_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('given_date', '<=', $to))
            ->orderByDesc('given_date')
            ->limit(10)
            ->get()
            ->each(function (BnsChildImmunization $log) use ($activities, $nameByKey) {
                $activities->push([
                    'type' => 'Immunization',
                    'child_name' => $nameByKey[$log->child_key] ?? 'Child',
                    'detail' => $log->vaccine_name,
                    'date' => $log->given_date?->format('M d, Y') ?? '',
                    'sort' => $log->given_date?->timestamp ?? 0,
                ]);
            });

        return $activities
            ->sortByDesc('sort')
            ->take(20)
            ->map(fn ($item) => collect($item)->except('sort')->all())
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $children
     * @param  array<string, array<string, mixed>>  $healthSummaries
     * @return list<array{child_name: string, purok: string, age_label: string, status_label: string, status_group: string}>
     */
    private static function nutritionChildrenList(Collection $children, array $healthSummaries): array
    {
        return $children
            ->map(function (array $child) use ($healthSummaries) {
                $key = (string) ($child['id'] ?? '');
                $nutrition = $healthSummaries[$key]['nutrition'] ?? null;
                $hasData = is_array($nutrition) && ($nutrition['has_data'] ?? false);
                $category = $hasData ? (string) ($nutrition['category'] ?? 'unknown') : 'no_record';

                $statusGroup = match (true) {
                    in_array($category, ['severely_underweight', 'severely_stunted', 'severely_wasted'], true) => 'severely_malnourished',
                    in_array($category, ['underweight', 'stunted', 'wasted'], true) => 'malnourished',
                    $category === 'overweight' => 'overweight',
                    $category === 'normal' => 'normal',
                    default => 'no_record',
                };

                return [
                    'child_name' => (string) ($child['full_name'] ?? 'Child'),
                    'purok' => (string) ($child['purok'] ?? '—'),
                    'age_label' => (string) ($child['age_label'] ?? '—'),
                    'status_label' => $hasData
                        ? (string) ($nutrition['summary_label'] ?? $nutrition['label'] ?? 'Unknown')
                        : 'No weight record',
                    'status_group' => $statusGroup,
                ];
            })
            ->sortBy([
                ['status_group', 'asc'],
                ['child_name', 'asc'],
            ])
            ->values()
            ->all();
    }
}
