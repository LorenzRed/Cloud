<?php

namespace App\Support;

use App\Models\BnsChildWeightLog;
use App\Models\ResidentRequest;
use App\Models\User;
use App\Support\PersonAge;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class BnsChildMonitoring
{
    public static function barangayNameFor(User $bns): ?string
    {
        if ($bns->role !== User::ROLE_BNS) {
            return null;
        }

        $barangay = trim((string) ($bns->barangay ?? ''));

        return $barangay !== '' ? $barangay : null;
    }

    public static function ageInYears(?string $birthdate): ?int
    {
        return PersonAge::ageInYears($birthdate);
    }

    public static function ageLabel(?string $birthdate): string
    {
        return PersonAge::labelFromBirthdate($birthdate);
    }

    public static function ageInMonths(?string $birthdate): ?int
    {
        return PersonAge::ageInWholeMonths($birthdate);
    }

    public static function isChildUnderFive(?string $birthdate): bool
    {
        $years = self::ageInYears($birthdate);

        return $years !== null && $years <= 5;
    }

    public static function ageGroupLabel(int $ageYears): string
    {
        return match (true) {
            $ageYears <= 0 => '0–1 year',
            $ageYears === 1 => '1–2 years',
            $ageYears === 2 => '2–3 years',
            $ageYears === 3 => '3–4 years',
            default => '4–5 years',
        };
    }

    public static function childCategoryLabel(string $currentlyStudying): string
    {
        return match ($currentlyStudying) {
            'infant' => 'Infant',
            'yes' => 'Studying',
            'no' => 'Not studying',
            default => '—',
        };
    }

    /**
     * @return array{father_name: string, mother_name: string, parent_name: string, parent_role: string}
     */
    public static function parentNamesFromRequest(ResidentRequest $request): array
    {
        $parentName = trim((string) ($request->full_name ?? ''));
        $parentRole = trim((string) ($request->parent_role ?? ''));

        $spouseDetails = $request->spouse_details ?? [];
        if (! is_array($spouseDetails)) {
            $spouseDetails = [];
        }

        $spouseName = trim((string) ($request->spouse_full_name ?: ($spouseDetails['full_name'] ?? '')));
        $spouseRole = trim((string) ($spouseDetails['parent_role'] ?? ''));

        $fatherName = '';
        $motherName = '';

        if ($parentRole === ResidentRequest::PARENT_ROLE_FATHER && $parentName !== '') {
            $fatherName = $parentName;
        } elseif ($parentRole === ResidentRequest::PARENT_ROLE_MOTHER && $parentName !== '') {
            $motherName = $parentName;
        }

        if ($spouseName !== '') {
            if ($spouseRole === ResidentRequest::PARENT_ROLE_FATHER) {
                $fatherName = $spouseName;
            } elseif ($spouseRole === ResidentRequest::PARENT_ROLE_MOTHER) {
                $motherName = $spouseName;
            } elseif ($parentRole === ResidentRequest::PARENT_ROLE_FATHER && $motherName === '') {
                $motherName = $spouseName;
            } elseif ($parentRole === ResidentRequest::PARENT_ROLE_MOTHER && $fatherName === '') {
                $fatherName = $spouseName;
            }
        }

        return [
            'father_name' => $fatherName,
            'mother_name' => $motherName,
            'parent_name' => $parentName,
            'parent_role' => $parentRole,
        ];
    }

    /**
     * @return Collection<int, array{
     *     id: string,
     *     resident_request_id: int,
     *     household_user_id: int|null,
     *     representative_name: string,
     *     full_name: string,
     *     birthdate: string,
     *     birthdate_label: string,
     *     age_years: int,
     *     age_label: string,
     *     gender: string,
     *     relationship: string,
     *     purok: string,
     *     barangay: string,
     *     status_label: string,
     *     family_number: string|null,
     *     accepted_at: string|null
     * }>
     */
    public static function childrenUnderFiveForBarangay(string $barangay): Collection
    {
        $normalizedBarangay = mb_strtolower(trim($barangay));

        if ($normalizedBarangay === '') {
            return collect();
        }

        $requests = ResidentRequest::query()
            ->with(['purok', 'user'])
            ->where('status', 'accepted')
            ->whereRaw('LOWER(TRIM(barangay)) = ?', [$normalizedBarangay])
            ->whereNotNull('household_children')
            ->orderByDesc('reviewed_at')
            ->orderByDesc('updated_at')
            ->get();

        $children = collect();

        foreach ($requests as $request) {
            $householdChildren = collect($request->household_children ?? [])
                ->filter(fn ($child) => is_array($child));

            foreach ($householdChildren as $index => $child) {
                $birthdate = trim((string) ($child['birthdate'] ?? ''));

                if (! self::isChildUnderFive($birthdate)) {
                    continue;
                }

                $fullName = trim((string) ($child['full_name'] ?? $child['name'] ?? ''));
                if ($fullName === '') {
                    continue;
                }

                $ageYears = self::ageInYears($birthdate) ?? 0;
                $birthdateLabel = filled($birthdate)
                    ? Carbon::parse($birthdate)->format('M d, Y')
                    : 'N/A';
                $currentlyStudying = (string) ($child['currently_studying'] ?? '');
                $parents = self::parentNamesFromRequest($request);

                $children->push([
                    'id' => $request->id.'-'.$index,
                    'child_index' => (int) $index,
                    'resident_request_id' => (int) $request->id,
                    'household_user_id' => $request->user_id ? (int) $request->user_id : null,
                    'parent_name' => $parents['parent_name'],
                    'parent_role' => $parents['parent_role'],
                    'father_name' => $parents['father_name'],
                    'mother_name' => $parents['mother_name'],
                    'representative_name' => $parents['parent_name'],
                    'contact' => trim((string) ($request->contact ?? ''))
                        ?: (trim((string) ($request->user?->contact_number ?? '')) ?: '—'),
                    'full_name' => $fullName,
                    'birthdate' => $birthdate,
                    'birthdate_label' => $birthdateLabel,
                    'age_years' => $ageYears,
                    'age_in_months' => self::ageInMonths($birthdate) ?? 0,
                    'age_label' => self::ageLabel($birthdate),
                    'age_group' => self::ageGroupLabel($ageYears),
                    'gender' => trim((string) ($child['gender'] ?? '')) ?: '—',
                    'relationship' => trim((string) ($child['relationship'] ?? '')) ?: '—',
                    'category_label' => self::childCategoryLabel($currentlyStudying),
                    'school' => trim((string) ($child['school'] ?? '')) ?: '—',
                    'educational_attainment' => trim((string) ($child['educational_attainment'] ?? '')) ?: '—',
                    'purok' => trim((string) ($request->purok?->name ?? '')) ?: 'Unassigned purok',
                    'barangay' => trim((string) ($request->barangay ?? '')),
                    'status_label' => $currentlyStudying === 'infant' ? 'Infant' : 'Child 0–5',
                    'reviewed_at' => $request->reviewed_at,
                    'family_number' => trim((string) ($request->family_number ?? $request->user?->family_number ?? '')) ?: null,
                    'accepted_at' => optional($request->reviewed_at)->format('M d, Y'),
                ]);
            }
        }

        return $children
            ->sortBy([
                ['purok', 'asc'],
                ['full_name', 'asc'],
            ])
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public static function childrenUnderFiveFor(User $bns): Collection
    {
        $barangay = self::barangayNameFor($bns);

        if ($barangay === null) {
            return collect();
        }

        return self::childrenUnderFiveForBarangay($barangay);
    }

    /**
     * @return array{
     *     barangayName: string|null,
     *     monitoredChildren: Collection<int, array<string, mixed>>,
     *     totalChildren: int,
     *     purokCount: int,
     *     householdCount: int,
     *     nutritionStats: array<string, int>,
     *     immunizationOverview: array<string, int>,
     *     managedBnsCount: int|null,
     *     dashboardCharts: array<string, mixed>
     * }
     */
    public static function dashboardPayloadFor(User $bns): array
    {
        return self::dashboardPayloadForBarangay(self::barangayNameFor($bns));
    }

    /**
     * Barangay-wide child nutrition dashboard (used by BNS and Barangay Captain).
     *
     * @return array{
     *     barangayName: string|null,
     *     monitoredChildren: Collection<int, array<string, mixed>>,
     *     totalChildren: int,
     *     purokCount: int,
     *     householdCount: int,
     *     nutritionStats: array<string, int>,
     *     immunizationOverview: array<string, int>,
     *     managedBnsCount: int|null,
     *     dashboardCharts: array<string, mixed>
     * }
     */
    public static function dashboardPayloadForBarangay(?string $barangayName, bool $includeWorkerCount = false): array
    {
        $barangayName = filled($barangayName) ? trim((string) $barangayName) : null;

        if ($barangayName === null || $barangayName === '') {
            return self::emptyDashboardPayload();
        }

        $monitoredChildren = self::childrenUnderFiveForBarangay($barangayName);
        $totalChildren = $monitoredChildren->count();
        $purokCount = $monitoredChildren->pluck('purok')->unique()->count();
        $householdCount = $monitoredChildren->pluck('resident_request_id')->unique()->count();

        $healthSummaries = BnsChildHealth::summariesForChildren($monitoredChildren);
        $nutritionCounts = BnsChildNutrition::aggregateStats($healthSummaries);

        $nutritionStats = [
            'total_children_0_5' => $totalChildren,
            'households_covered' => $householdCount,
            'puroks_covered' => $purokCount,
            'underweight_cases' => $nutritionCounts['underweight_cases'],
            'severely_underweight_cases' => $nutritionCounts['severely_underweight_cases'],
            'normal_bmi_count' => $nutritionCounts['normal_bmi_count'],
            'overweight_cases' => $nutritionCounts['overweight_cases'],
            'malnutrition_cases' => $nutritionCounts['malnutrition_cases'],
            'no_weight_record' => $nutritionCounts['no_weight_record'],
        ];

        $immunizationOverview = [
            'fully_immunized' => collect($healthSummaries)->filter(fn ($s) => $s['fully_immunized'] ?? false)->count(),
            'with_missed_doses' => collect($healthSummaries)->filter(fn ($s) => ($s['immunization_missed'] ?? 0) > 0)->count(),
            'incomplete' => $totalChildren - collect($healthSummaries)->filter(fn ($s) => $s['fully_immunized'] ?? false)->count(),
        ];

        $chartPeriods = [];
        foreach (['week', 'month', 'year'] as $period) {
            [$from, $to] = self::periodBounds($period);
            $chartPeriods[$period] = [
                'range_label' => self::periodRangeLabel($from, $to),
                'nutrition_categories' => self::nutritionCategoriesForRange($monitoredChildren, $from, $to),
                'weight_trend' => self::weightTrendForRange($monitoredChildren, $period, $from, $to),
            ];
        }

        $managedBnsCount = null;
        if ($includeWorkerCount) {
            $managedBnsCount = User::query()
                ->where('role', User::ROLE_BNS)
                ->where('barangay', $barangayName)
                ->count();
        }

        return [
            'barangayName' => $barangayName,
            'monitoredChildren' => $monitoredChildren,
            'totalChildren' => $totalChildren,
            'purokCount' => $purokCount,
            'householdCount' => $householdCount,
            'nutritionStats' => $nutritionStats,
            'immunizationOverview' => $immunizationOverview,
            'managedBnsCount' => $managedBnsCount,
            'dashboardCharts' => [
                'default_period' => 'month',
                'chart_periods' => $chartPeriods,
                'nutrition_split' => [
                    'labels' => ['Malnourished', 'Normal', 'Overweight', 'No record'],
                    'values' => [
                        $nutritionCounts['malnutrition_cases'],
                        $nutritionCounts['normal_bmi_count'],
                        $nutritionCounts['overweight_cases'],
                        $nutritionCounts['no_weight_record'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array{
     *     barangayName: null,
     *     monitoredChildren: Collection<int, array<string, mixed>>,
     *     totalChildren: int,
     *     purokCount: int,
     *     householdCount: int,
     *     nutritionStats: array<string, int>,
     *     immunizationOverview: array<string, int>,
     *     managedBnsCount: int,
     *     dashboardCharts: array<string, mixed>
     * }
     */
    private static function emptyDashboardPayload(): array
    {
        $emptyCategories = self::formatNutritionCategories([
            'severely' => 0,
            'underweight' => 0,
            'normal' => 0,
            'overweight' => 0,
        ], 0);

        $chartPeriods = [];
        foreach (['week', 'month', 'year'] as $period) {
            [$from, $to] = self::periodBounds($period);
            $chartPeriods[$period] = [
                'range_label' => self::periodRangeLabel($from, $to),
                'nutrition_categories' => $emptyCategories,
                'weight_trend' => ['labels' => [], 'values' => [], 'unit' => 'kg'],
            ];
        }

        return [
            'barangayName' => null,
            'monitoredChildren' => collect(),
            'totalChildren' => 0,
            'purokCount' => 0,
            'householdCount' => 0,
            'nutritionStats' => [
                'total_children_0_5' => 0,
                'households_covered' => 0,
                'puroks_covered' => 0,
                'underweight_cases' => 0,
                'severely_underweight_cases' => 0,
                'normal_bmi_count' => 0,
                'overweight_cases' => 0,
                'malnutrition_cases' => 0,
                'no_weight_record' => 0,
            ],
            'immunizationOverview' => [
                'fully_immunized' => 0,
                'with_missed_doses' => 0,
                'incomplete' => 0,
            ],
            'managedBnsCount' => 0,
            'dashboardCharts' => [
                'default_period' => 'month',
                'chart_periods' => $chartPeriods,
                'nutrition_split' => [
                    'labels' => ['Malnourished', 'Normal', 'Overweight', 'No record'],
                    'values' => [0, 0, 0, 0],
                ],
            ],
        ];
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function periodBounds(string $period): array
    {
        $to = now()->endOfDay();

        return match ($period) {
            'week' => [now()->startOfWeek()->startOfDay(), $to],
            'year' => [now()->startOfYear()->startOfDay(), $to],
            default => [now()->startOfMonth()->startOfDay(), $to],
        };
    }

    public static function periodRangeLabel(Carbon $from, Carbon $to): string
    {
        if ($from->isSameDay($to)) {
            return $from->format('M j, Y');
        }

        if ($from->year === $to->year) {
            return $from->format('M j').' – '.$to->format('M j, Y');
        }

        return $from->format('M j, Y').' – '.$to->format('M j, Y');
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $children
     * @return array{
     *     labels: list<string>,
     *     values: list<int>,
     *     weighed_count: int,
     *     colors: list<string>
     * }
     */
    public static function nutritionCategoriesForRange(Collection $children, Carbon $from, Carbon $to): array
    {
        $counts = [
            'severely' => 0,
            'underweight' => 0,
            'normal' => 0,
            'overweight' => 0,
        ];

        if ($children->isEmpty()) {
            return self::formatNutritionCategories($counts, 0);
        }

        $childKeys = $children->pluck('id')->map(fn ($id) => (string) $id)->all();
        $childrenByKey = $children->keyBy(fn (array $child) => (string) $child['id']);

        $logs = BnsChildWeightLog::query()
            ->whereIn('child_key', $childKeys)
            ->whereDate('recorded_on', '>=', $from->toDateString())
            ->whereDate('recorded_on', '<=', $to->toDateString())
            ->orderByDesc('recorded_on')
            ->get();

        $latestByChild = [];
        foreach ($logs as $log) {
            if (! isset($latestByChild[$log->child_key])) {
                $latestByChild[$log->child_key] = $log;
            }
        }

        foreach ($latestByChild as $childKey => $log) {
            $child = $childrenByKey->get($childKey);
            if (! is_array($child)) {
                continue;
            }

            $ageMonths = $log->age_in_months ?? self::ageInMonths($child['birthdate'] ?? null);
            $nutrition = BnsChildNutrition::assess(
                (float) $log->weight_kg,
                $log->height_cm !== null ? (float) $log->height_cm : null,
                $ageMonths,
                (string) ($child['gender'] ?? '')
            );

            $bucket = BnsChildNutrition::chartBucket($nutrition);
            if ($bucket !== null && isset($counts[$bucket])) {
                $counts[$bucket]++;
            }
        }

        $weighedCount = array_sum($counts);

        return self::formatNutritionCategories($counts, $weighedCount);
    }

    /**
     * @param  array<string, int>  $counts
     * @return array{labels: list<string>, values: list<int>, weighed_count: int, colors: list<string>}
     */
    private static function formatNutritionCategories(array $counts, int $weighedCount): array
    {
        return [
            'labels' => ['Severely malnourished', 'Malnourished', 'Normal', 'Overweight'],
            'values' => [
                $counts['severely'],
                $counts['underweight'],
                $counts['normal'],
                $counts['overweight'],
            ],
            'weighed_count' => $weighedCount,
            'colors' => ['#BE123C', '#F59E0B', '#10B981', '#F97316'],
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $children
     * @return array{labels: list<string>, values: list<float|null>, unit: string}
     */
    public static function weightTrendForRange(Collection $children, string $period, Carbon $from, Carbon $to): array
    {
        $childKeys = $children->pluck('id')->map(fn ($id) => (string) $id)->all();
        $labels = [];
        $values = [];

        if ($period === 'week') {
            $cursor = $from->copy()->startOfDay();
            while ($cursor->lte($to)) {
                $labels[] = $cursor->format('D');
                $values[] = self::averageWeightOnDate($childKeys, $cursor);

                $cursor->addDay();
            }

            return ['labels' => $labels, 'values' => $values, 'unit' => 'kg'];
        }

        if ($period === 'month') {
            $cursor = $from->copy()->startOfWeek();
            while ($cursor->lte($to)) {
                $weekEnd = $cursor->copy()->endOfWeek()->min($to);
                $labels[] = $cursor->format('M j');
                $values[] = self::averageWeightBetween($childKeys, $cursor, $weekEnd);
                $cursor->addWeek();
            }

            return ['labels' => $labels, 'values' => $values, 'unit' => 'kg'];
        }

        $cursor = $from->copy()->startOfMonth();
        while ($cursor->lte($to)) {
            $labels[] = $cursor->format('M');
            $values[] = self::averageWeightInMonth($childKeys, $cursor->year, $cursor->month);
            $cursor->addMonth();
        }

        return ['labels' => $labels, 'values' => $values, 'unit' => 'kg'];
    }

    /**
     * @param  list<string>  $childKeys
     */
    private static function averageWeightOnDate(array $childKeys, Carbon $date): ?float
    {
        if ($childKeys === []) {
            return null;
        }

        $avg = BnsChildWeightLog::query()
            ->whereIn('child_key', $childKeys)
            ->whereDate('recorded_on', $date->toDateString())
            ->avg('weight_kg');

        return $avg !== null ? round((float) $avg, 2) : null;
    }

    /**
     * @param  list<string>  $childKeys
     */
    private static function averageWeightBetween(array $childKeys, Carbon $from, Carbon $to): ?float
    {
        if ($childKeys === []) {
            return null;
        }

        $avg = BnsChildWeightLog::query()
            ->whereIn('child_key', $childKeys)
            ->whereDate('recorded_on', '>=', $from->toDateString())
            ->whereDate('recorded_on', '<=', $to->toDateString())
            ->avg('weight_kg');

        return $avg !== null ? round((float) $avg, 2) : null;
    }

    /**
     * @param  list<string>  $childKeys
     */
    private static function averageWeightInMonth(array $childKeys, int $year, int $month): ?float
    {
        if ($childKeys === []) {
            return null;
        }

        $avg = BnsChildWeightLog::query()
            ->whereIn('child_key', $childKeys)
            ->whereYear('recorded_on', $year)
            ->whereMonth('recorded_on', $month)
            ->avg('weight_kg');

        return $avg !== null ? round((float) $avg, 2) : null;
    }
}
