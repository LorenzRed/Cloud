<?php

namespace App\Support;

class BnsChildNutrition
{
    /** @var array<int, float> month => median kg */
    private const BOYS_WFA_MEDIAN = [
        0 => 3.35, 3 => 6.38, 6 => 7.93, 9 => 8.90, 12 => 9.65, 18 => 10.88,
        24 => 12.24, 30 => 13.52, 36 => 14.64, 42 => 15.63, 48 => 16.62, 54 => 17.56, 60 => 18.48,
    ];

    /** @var array<int, float> */
    private const GIRLS_WFA_MEDIAN = [
        0 => 3.24, 3 => 5.85, 6 => 7.30, 9 => 8.17, 12 => 8.95, 18 => 10.15,
        24 => 11.48, 30 => 12.73, 36 => 13.84, 42 => 14.86, 48 => 15.85, 54 => 16.80, 60 => 17.73,
    ];

    /** @var array<int, float> */
    private const BOYS_WFA_SD = [
        0 => 0.44, 3 => 0.71, 6 => 0.87, 9 => 0.94, 12 => 1.00, 18 => 1.09,
        24 => 1.18, 30 => 1.26, 36 => 1.34, 42 => 1.41, 48 => 1.48, 54 => 1.55, 60 => 1.62,
    ];

    /** @var array<int, float> */
    private const GIRLS_WFA_SD = [
        0 => 0.42, 3 => 0.68, 6 => 0.83, 9 => 0.90, 12 => 0.96, 18 => 1.04,
        24 => 1.12, 30 => 1.20, 36 => 1.27, 42 => 1.34, 48 => 1.40, 54 => 1.46, 60 => 1.52,
    ];

    /** @var array<int, float> month => median cm */
    private const BOYS_HFA_MEDIAN = [
        0 => 49.9, 3 => 61.4, 6 => 67.6, 9 => 72.0, 12 => 75.7, 18 => 82.3,
        24 => 87.8, 36 => 96.1, 48 => 103.3, 60 => 109.2,
    ];

    /** @var array<int, float> */
    private const GIRLS_HFA_MEDIAN = [
        0 => 49.1, 3 => 59.8, 6 => 65.7, 9 => 70.1, 12 => 74.0, 18 => 80.7,
        24 => 86.4, 36 => 95.1, 48 => 102.7, 60 => 108.7,
    ];

    /** @var array<int, float> */
    private const BOYS_HFA_SD = [
        0 => 1.9, 3 => 2.1, 6 => 2.2, 9 => 2.3, 12 => 2.4, 18 => 2.5,
        24 => 2.6, 36 => 2.8, 48 => 3.0, 60 => 3.2,
    ];

    /** @var array<int, float> */
    private const GIRLS_HFA_SD = [
        0 => 1.8, 3 => 2.0, 6 => 2.1, 9 => 2.2, 12 => 2.3, 18 => 2.4,
        24 => 2.5, 36 => 2.7, 48 => 2.9, 60 => 3.1,
    ];

    /** @var array<int, float> height cm => median kg */
    private const BOYS_WFH_MEDIAN = [
        45 => 2.5, 50 => 3.3, 55 => 4.5, 60 => 5.8, 65 => 6.9, 70 => 7.8, 75 => 8.8,
        80 => 9.8, 85 => 10.9, 90 => 12.0, 95 => 13.2, 100 => 14.5, 105 => 15.8, 110 => 17.2,
    ];

    /** @var array<int, float> */
    private const GIRLS_WFH_MEDIAN = [
        45 => 2.4, 50 => 3.2, 55 => 4.3, 60 => 5.5, 65 => 6.6, 70 => 7.5, 75 => 8.4,
        80 => 9.3, 85 => 10.3, 90 => 11.4, 95 => 12.5, 100 => 13.7, 105 => 15.0, 110 => 16.4,
    ];

    /** @var array<int, float> */
    private const BOYS_WFH_SD = [
        45 => 0.35, 50 => 0.40, 55 => 0.48, 60 => 0.55, 65 => 0.62, 70 => 0.68, 75 => 0.74,
        80 => 0.80, 85 => 0.86, 90 => 0.92, 95 => 0.98, 100 => 1.05, 105 => 1.12, 110 => 1.18,
    ];

    /** @var array<int, float> */
    private const GIRLS_WFH_SD = [
        45 => 0.33, 50 => 0.38, 55 => 0.45, 60 => 0.52, 65 => 0.58, 70 => 0.64, 75 => 0.70,
        80 => 0.76, 85 => 0.82, 90 => 0.88, 95 => 0.94, 100 => 1.00, 105 => 1.06, 110 => 1.12,
    ];

    /**
     * @return array{
     *     has_data: bool,
     *     label: string,
     *     summary_label: string,
     *     severity: string,
     *     is_malnourished: bool,
     *     category: string,
     *     weight_for_age: array<string, mixed>|null,
     *     height_for_age: array<string, mixed>|null,
     *     weight_for_height: array<string, mixed>|null
     * }
     */
    public static function assess(?float $weightKg, ?float $heightCm, ?int $ageMonths, ?string $gender): array
    {
        $empty = [
            'has_data' => false,
            'label' => 'No weight record',
            'summary_label' => 'No record',
            'severity' => 'unknown',
            'is_malnourished' => false,
            'category' => 'unknown',
            'weight_for_age' => null,
            'height_for_age' => null,
            'weight_for_height' => null,
        ];

        if ($weightKg === null || $weightKg <= 0 || $ageMonths === null || $ageMonths < 0 || $ageMonths > 60) {
            return $empty;
        }

        $isFemale = self::isFemale($gender);
        $wfa = self::weightForAge($weightKg, $ageMonths, $isFemale);
        $hfa = ($heightCm !== null && $heightCm > 0)
            ? self::heightForAge($heightCm, $ageMonths, $isFemale)
            : null;
        $wfh = ($heightCm !== null && $heightCm > 0)
            ? self::weightForHeight($weightKg, $heightCm, $isFemale)
            : null;

        $findings = array_values(array_filter([$wfa, $hfa, $wfh]));
        $primary = self::primaryFinding($findings) ?? $wfa;

        $assessment = [
            'has_data' => true,
            'label' => $primary['label'],
            'summary_label' => $primary['summary_label'],
            'severity' => $primary['severity'],
            'is_malnourished' => collect($findings)->contains(fn (array $f) => $f['is_malnourished']),
            'category' => $primary['category'],
            'weight_for_age' => $wfa,
            'height_for_age' => $hfa,
            'weight_for_height' => $wfh,
        ];

        return $assessment;
    }

  /**
     * @param  list<array<string, mixed>>  $findings
     * @return array<string, mixed>|null
     */
    private static function primaryFinding(array $findings): ?array
    {
        if ($findings === []) {
            return null;
        }

        $rank = [
            'severely_wasted' => 0,
            'wasted' => 1,
            'severely_underweight' => 2,
            'underweight' => 3,
            'severely_stunted' => 4,
            'stunted' => 5,
            'overweight' => 6,
            'normal' => 7,
        ];

        return collect($findings)
            ->sortBy(fn (array $f) => $rank[$f['category']] ?? 99)
            ->first();
    }

    /**
     * @return array{label: string, summary_label: string, severity: string, category: string, is_malnourished: bool, z_score: float|null}
     */
    private static function weightForAge(float $weightKg, int $ageMonths, bool $isFemale): array
    {
        $median = self::interpolate($isFemale ? self::GIRLS_WFA_MEDIAN : self::BOYS_WFA_MEDIAN, $ageMonths);
        $sd = self::interpolate($isFemale ? self::GIRLS_WFA_SD : self::BOYS_WFA_SD, $ageMonths);
        $z = self::zScore($weightKg, $median, $sd);

        return self::classifyWeightForAge($z);
    }

    /**
     * @return array{label: string, summary_label: string, severity: string, category: string, is_malnourished: bool, z_score: float|null}
     */
    private static function heightForAge(float $heightCm, int $ageMonths, bool $isFemale): array
    {
        $median = self::interpolate($isFemale ? self::GIRLS_HFA_MEDIAN : self::BOYS_HFA_MEDIAN, $ageMonths);
        $sd = self::interpolate($isFemale ? self::GIRLS_HFA_SD : self::BOYS_HFA_SD, $ageMonths);
        $z = self::zScore($heightCm, $median, $sd);

        return self::classifyHeightForAge($z);
    }

    /**
     * @return array{label: string, summary_label: string, severity: string, category: string, is_malnourished: bool, z_score: float|null}
     */
    private static function weightForHeight(float $weightKg, float $heightCm, bool $isFemale): array
    {
        $median = self::interpolateByMeasurement(
            $isFemale ? self::GIRLS_WFH_MEDIAN : self::BOYS_WFH_MEDIAN,
            $heightCm
        );
        $sd = self::interpolateByMeasurement(
            $isFemale ? self::GIRLS_WFH_SD : self::BOYS_WFH_SD,
            $heightCm
        );
        $z = self::zScore($weightKg, $median, $sd);

        return self::classifyWeightForHeight($z);
    }

    /**
     * @param  array<int, float>  $anchors
     */
    private static function interpolate(array $anchors, int $month): float
    {
        $month = max(0, min(60, $month));

        if (isset($anchors[$month])) {
            return $anchors[$month];
        }

        $keys = array_keys($anchors);
        $lower = $keys[0];
        foreach ($keys as $key) {
            if ($key <= $month) {
                $lower = $key;
            }
            if ($key >= $month) {
                $upper = $key;
                if ($lower === $upper) {
                    return $anchors[$lower];
                }

                $ratio = ($month - $lower) / ($upper - $lower);

                return $anchors[$lower] + (($anchors[$upper] - $anchors[$lower]) * $ratio);
            }
        }

        return $anchors[$lower];
    }

    /**
     * @param  array<int, float>  $anchors
     */
    private static function interpolateByMeasurement(array $anchors, float $value): float
    {
        $keys = array_keys($anchors);
        $min = (float) $keys[0];
        $max = (float) $keys[count($keys) - 1];
        $value = max($min, min($max, $value));

        $lower = $keys[0];
        foreach ($keys as $key) {
            if ($key <= $value) {
                $lower = $key;
            }
            if ($key >= $value) {
                $upper = $key;
                if ($lower === $upper) {
                    return $anchors[$lower];
                }

                $ratio = ($value - $lower) / ($upper - $lower);

                return $anchors[$lower] + (($anchors[$upper] - $anchors[$lower]) * $ratio);
            }
        }

        return $anchors[$lower];
    }

    private static function zScore(float $value, float $median, float $sd): ?float
    {
        if ($sd <= 0) {
            return null;
        }

        return ($value - $median) / $sd;
    }

    /**
     * @return array{label: string, summary_label: string, severity: string, category: string, is_malnourished: bool, z_score: float|null}
     */
    private static function classifyWeightForAge(?float $z): array
    {
        if ($z === null) {
            return self::unknownFinding('Weight-for-age');
        }

        if ($z < -3) {
            return self::finding('Severely underweight', 'Severely UW', 'critical', 'severely_underweight', true, $z);
        }
        if ($z < -2) {
            return self::finding('Underweight', 'Underweight', 'warning', 'underweight', true, $z);
        }
        if ($z > 2) {
            return self::finding('Overweight (weight-for-age)', 'Overweight', 'warning', 'overweight', false, $z);
        }

        return self::finding('Normal (weight-for-age)', 'Normal', 'normal', 'normal', false, $z);
    }

    /**
     * @return array{label: string, summary_label: string, severity: string, category: string, is_malnourished: bool, z_score: float|null}
     */
    private static function classifyHeightForAge(?float $z): array
    {
        if ($z === null) {
            return self::unknownFinding('Height-for-age');
        }

        if ($z < -3) {
            return self::finding('Severely stunted', 'Severely stunted', 'critical', 'severely_stunted', true, $z);
        }
        if ($z < -2) {
            return self::finding('Stunted', 'Stunted', 'warning', 'stunted', true, $z);
        }

        return self::finding('Normal height-for-age', 'Normal height', 'normal', 'normal', false, $z);
    }

    /**
     * @return array{label: string, summary_label: string, severity: string, category: string, is_malnourished: bool, z_score: float|null}
     */
    private static function classifyWeightForHeight(?float $z): array
    {
        if ($z === null) {
            return self::unknownFinding('Weight-for-height');
        }

        if ($z < -3) {
            return self::finding('Severely wasted', 'Severely wasted', 'critical', 'severely_wasted', true, $z);
        }
        if ($z < -2) {
            return self::finding('Wasted', 'Wasted', 'warning', 'wasted', true, $z);
        }
        if ($z > 2) {
            return self::finding('Overweight (weight-for-height)', 'Overweight', 'warning', 'overweight', false, $z);
        }

        return self::finding('Normal weight-for-height', 'Normal weight', 'normal', 'normal', false, $z);
    }

    /**
     * @return array{label: string, summary_label: string, severity: string, category: string, is_malnourished: bool, z_score: float|null}
     */
    private static function finding(
        string $label,
        string $summaryLabel,
        string $severity,
        string $category,
        bool $isMalnourished,
        ?float $z
    ): array {
        return [
            'label' => $label,
            'summary_label' => $summaryLabel,
            'severity' => $severity,
            'category' => $category,
            'is_malnourished' => $isMalnourished,
            'z_score' => $z !== null ? round($z, 2) : null,
        ];
    }

    /**
     * @return array{label: string, summary_label: string, severity: string, category: string, is_malnourished: bool, z_score: float|null}
     */
    private static function unknownFinding(string $label): array
    {
        return [
            'label' => $label.' unavailable',
            'summary_label' => 'Unknown',
            'severity' => 'unknown',
            'category' => 'unknown',
            'is_malnourished' => false,
            'z_score' => null,
        ];
    }

    private static function isFemale(?string $gender): bool
    {
        $normalized = mb_strtolower(trim((string) $gender));

        return in_array($normalized, ['female', 'f', 'girl'], true);
    }

    /**
     * @param  array<string, array<string, mixed>>  $summaries
     * @return array{
     *     underweight_cases: int,
     *     severely_underweight_cases: int,
     *     normal_bmi_count: int,
     *     malnutrition_cases: int,
     *     overweight_cases: int,
     *     no_weight_record: int
     * }
     */
    /**
     * Map an assessment into the four dashboard nutrition buckets.
     *
     * @param  array<string, mixed>|null  $nutrition
     */
    public static function chartBucket(?array $nutrition): ?string
    {
        if (! is_array($nutrition) || ! ($nutrition['has_data'] ?? false)) {
            return null;
        }

        $category = (string) ($nutrition['category'] ?? 'unknown');

        if ($category === 'overweight') {
            return 'overweight';
        }

        if (in_array($category, ['severely_underweight', 'severely_stunted', 'severely_wasted'], true)) {
            return 'severely';
        }

        if (in_array($category, ['underweight', 'stunted', 'wasted'], true)) {
            return 'underweight';
        }

        if ($category === 'normal') {
            return 'normal';
        }

        if ($nutrition['is_malnourished'] ?? false) {
            return 'underweight';
        }

        return null;
    }

    public static function aggregateStats(array $summaries): array
    {
        $underweight = 0;
        $severelyUnderweight = 0;
        $normal = 0;
        $malnutrition = 0;
        $overweight = 0;
        $noRecord = 0;

        foreach ($summaries as $summary) {
            $nutrition = $summary['nutrition'] ?? null;
            if (! is_array($nutrition) || ! ($nutrition['has_data'] ?? false)) {
                $noRecord++;

                continue;
            }

            $category = (string) ($nutrition['category'] ?? 'unknown');
            if ($category === 'overweight') {
                $overweight++;

                continue;
            }

            if ($category === 'severely_underweight' || $category === 'severely_stunted' || $category === 'severely_wasted') {
                $severelyUnderweight++;
                $malnutrition++;
            } elseif (in_array($category, ['underweight', 'stunted', 'wasted'], true)) {
                $underweight++;
                $malnutrition++;
            } elseif ($category === 'normal') {
                $normal++;
            } elseif (($nutrition['is_malnourished'] ?? false)) {
                $malnutrition++;
            }
        }

        return [
            'underweight_cases' => $underweight,
            'severely_underweight_cases' => $severelyUnderweight,
            'normal_bmi_count' => $normal,
            'malnutrition_cases' => $malnutrition,
            'overweight_cases' => $overweight,
            'no_weight_record' => $noRecord,
        ];
    }

    /**
     * MUAC (mid-upper arm circumference) for child malnutrition screening (6–59 months).
     *
     * @return array{label: string, summary_label: string, severity: string, category: string}|null
     */
    public static function assessMuac(?float $muacCm, ?int $ageMonths): ?array
    {
        if ($muacCm === null || $muacCm <= 0) {
            return null;
        }

        if ($ageMonths === null || $ageMonths < 6) {
            return [
                'label' => number_format($muacCm, 1).' cm (recorded)',
                'summary_label' => 'Recorded',
                'severity' => 'unknown',
                'category' => 'recorded',
            ];
        }

        if ($muacCm < 11.5) {
            return [
                'label' => 'Severely malnourished (MUAC < 11.5 cm)',
                'summary_label' => 'SAM',
                'severity' => 'critical',
                'category' => 'severely_malnourished',
            ];
        }

        if ($muacCm < 12.5) {
            return [
                'label' => 'Moderately malnourished (MUAC 11.5–12.5 cm)',
                'summary_label' => 'MAM',
                'severity' => 'warning',
                'category' => 'malnourished',
            ];
        }

        return [
            'label' => 'Normal (MUAC ≥ 12.5 cm)',
            'summary_label' => 'Normal',
            'severity' => 'normal',
            'category' => 'normal',
        ];
    }
}
