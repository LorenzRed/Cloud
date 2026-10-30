<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $household_monitoring_case_id
 * @property int|null $recorded_by
 * @property \Illuminate\Support\Carbon|null $visit_date
 * @property string|null $weight_kg
 * @property string|null $height_cm
 * @property string|null $bmi_value
 * @property string|null $muac_cm
 * @property string|null $anti_tetanus
 * @property string|null $blood_pressure
 * @property string|null $maintenance_medicines
 * @property string|null $notes
 */
class HouseholdMonitoringVisit extends Model
{
    protected $fillable = [
        'household_monitoring_case_id',
        'recorded_by',
        'visit_date',
        'weight_kg',
        'height_cm',
        'bmi_value',
        'muac_cm',
        'anti_tetanus',
        'blood_pressure',
        'maintenance_medicines',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'weight_kg' => 'decimal:2',
            'height_cm' => 'decimal:2',
            'bmi_value' => 'decimal:1',
            'muac_cm' => 'decimal:1',
        ];
    }

    public function monitoringCase(): BelongsTo
    {
        return $this->belongsTo(HouseholdMonitoringCase::class, 'household_monitoring_case_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Human-readable visit order label (First Visit, Second Visit, 11th Visit, …).
     */
    public static function ordinalLabel(int $number): string
    {
        if ($number < 1) {
            return 'Visit';
        }

        $named = [
            1 => 'First Visit',
            2 => 'Second Visit',
            3 => 'Third Visit',
            4 => 'Fourth Visit',
            5 => 'Fifth Visit',
            6 => 'Sixth Visit',
            7 => 'Seventh Visit',
            8 => 'Eighth Visit',
            9 => 'Ninth Visit',
            10 => 'Tenth Visit',
        ];

        if (isset($named[$number])) {
            return $named[$number];
        }

        $mod100 = $number % 100;
        $suffix = match (true) {
            $mod100 >= 11 && $mod100 <= 13 => 'th',
            $number % 10 === 1 => 'st',
            $number % 10 === 2 => 'nd',
            $number % 10 === 3 => 'rd',
            default => 'th',
        };

        return $number.$suffix.' Visit';
    }

    /**
     * Effective vitals for a visit, carrying forward earlier values when fields were left blank.
     *
     * @param  \Illuminate\Support\Collection<int, self>|null  $orderedAsc
     * @return array{
     *     visit_number: int,
     *     visit_label: string,
     *     weight_kg: ?float,
     *     height_cm: ?float,
     *     bmi_value: ?float,
     *     muac_cm: ?float,
     *     blood_pressure: ?string,
     *     anti_tetanus: ?string
     * }
     */
    public function resolvedVitals($orderedAsc = null): array
    {
        $orderedAsc = $orderedAsc
            ? $orderedAsc->values()
            : self::query()
                ->where('household_monitoring_case_id', $this->household_monitoring_case_id)
                ->orderBy('visit_date')
                ->orderBy('id')
                ->get();

        $weight = null;
        $height = null;
        $bmi = null;
        $muac = null;
        $bloodPressure = null;
        $antiTetanus = null;
        $visitNumber = 1;

        foreach ($orderedAsc as $index => $visit) {
            if ($visit->weight_kg !== null) {
                $weight = (float) $visit->weight_kg;
            }
            if ($visit->height_cm !== null) {
                $height = (float) $visit->height_cm;
            }

            $calculatedBmi = self::calculateBmi($weight, $height);
            if ($calculatedBmi !== null) {
                $bmi = $calculatedBmi;
            } elseif ($visit->bmi_value !== null) {
                $bmi = (float) $visit->bmi_value;
            }

            if ($visit->muac_cm !== null) {
                $muac = (float) $visit->muac_cm;
            }
            if (filled($visit->blood_pressure)) {
                $bloodPressure = (string) $visit->blood_pressure;
            }
            if (filled($visit->anti_tetanus)) {
                $antiTetanus = (string) $visit->anti_tetanus;
            }

            if ((int) $visit->id === (int) $this->id) {
                $visitNumber = $index + 1;
                break;
            }
        }

        return [
            'visit_number' => $visitNumber,
            'visit_label' => self::ordinalLabel($visitNumber),
            'weight_kg' => $weight,
            'height_cm' => $height,
            'bmi_value' => $bmi,
            'muac_cm' => $muac,
            'blood_pressure' => $bloodPressure,
            'anti_tetanus' => $antiTetanus,
        ];
    }

    /**
     * Persist carried-forward vitals onto visits that were saved with blank optional fields.
     *
     * @param  \Illuminate\Support\Collection<int, self>|null  $orderedAsc
     */
    public static function backfillCarriedVitalsForCase(int $caseId, $orderedAsc = null): int
    {
        $orderedAsc = $orderedAsc
            ? $orderedAsc->values()
            : self::query()
                ->where('household_monitoring_case_id', $caseId)
                ->orderBy('visit_date')
                ->orderBy('id')
                ->get();

        $updated = 0;
        $weight = null;
        $height = null;
        $bmi = null;
        $muac = null;
        $bloodPressure = null;
        $antiTetanus = null;

        foreach ($orderedAsc as $visit) {
            if ($visit->weight_kg !== null) {
                $weight = (float) $visit->weight_kg;
            }
            if ($visit->height_cm !== null) {
                $height = (float) $visit->height_cm;
            }

            $calculatedBmi = self::calculateBmi($weight, $height);
            if ($calculatedBmi !== null) {
                $bmi = $calculatedBmi;
            } elseif ($visit->bmi_value !== null) {
                $bmi = (float) $visit->bmi_value;
            }

            if ($visit->muac_cm !== null) {
                $muac = (float) $visit->muac_cm;
            }
            if (filled($visit->blood_pressure)) {
                $bloodPressure = (string) $visit->blood_pressure;
            }
            if (filled($visit->anti_tetanus)) {
                $antiTetanus = (string) $visit->anti_tetanus;
            }

            $payload = [];
            if ($visit->weight_kg === null && $weight !== null) {
                $payload['weight_kg'] = $weight;
            }
            if ($visit->height_cm === null && $height !== null) {
                $payload['height_cm'] = $height;
            }
            if ($visit->bmi_value === null && $bmi !== null) {
                $payload['bmi_value'] = $bmi;
            }
            if ($visit->muac_cm === null && $muac !== null) {
                $payload['muac_cm'] = $muac;
            }
            if (! filled($visit->blood_pressure) && filled($bloodPressure)) {
                $payload['blood_pressure'] = $bloodPressure;
            }
            if (! filled($visit->anti_tetanus) && filled($antiTetanus)) {
                $payload['anti_tetanus'] = $antiTetanus;
            }

            if ($payload !== []) {
                $visit->forceFill($payload)->save();
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Normalize height to centimeters. Values under 3 are treated as meters (e.g. 1.65 → 165).
     */
    public static function normalizeHeightCm(mixed $height): ?float
    {
        if ($height === null || $height === '') {
            return null;
        }

        $heightCm = (float) $height;

        if ($heightCm <= 0) {
            return null;
        }

        // Common mistake: entering meters instead of centimeters.
        if ($heightCm < 3) {
            $heightCm = round($heightCm * 100, 1);
        }

        return $heightCm;
    }

    /**
     * Calculate BMI from kg / cm, or null when inputs are incomplete/invalid.
     */
    public static function calculateBmi(?float $weightKg, ?float $heightCm): ?float
    {
        $heightCm = self::normalizeHeightCm($heightCm);

        if ($weightKg === null || $heightCm === null || $weightKg <= 0 || $heightCm < 30) {
            return null;
        }

        $bmi = round($weightKg / (($heightCm / 100) ** 2), 1);

        if (! is_finite($bmi) || $bmi <= 0) {
            return null;
        }

        // Keep values within DECIMAL(5,1) storage limits.
        return min($bmi, 9999.9);
    }

    public static function bmiCategory(?float $bmi): ?string
    {
        if ($bmi === null || $bmi <= 0) {
            return null;
        }

        return match (true) {
            $bmi < 18.5 => 'Underweight',
            $bmi < 25 => 'Normal weight',
            $bmi < 30 => 'Overweight',
            $bmi < 35 => 'Obese class I',
            $bmi < 40 => 'Obese class II',
            default => 'Extremely obese',
        };
    }

    /**
     * @return array{
     *     label: string,
     *     explanation: string,
     *     recommendation: string,
     *     color: string,
     *     is_crisis: bool
     * }|null
     */
    public static function bloodPressureAssessment(?string $bloodPressure): ?array
    {
        if (! filled($bloodPressure) || ! preg_match('/^\s*(\d{2,3})\s*\/\s*(\d{2,3})\s*$/', $bloodPressure, $matches)) {
            return null;
        }

        $systolic = (int) $matches[1];
        $diastolic = (int) $matches[2];

        if ($systolic < 50 || $systolic > 300 || $diastolic < 30 || $diastolic > 200) {
            return null;
        }

        $systolicCategory = match (true) {
            $systolic < 90 => 'low',
            $systolic <= 120 => 'normal',
            $systolic <= 140 => 'pre_high',
            default => 'high',
        };
        $diastolicCategory = match (true) {
            $diastolic < 60 => 'low',
            $diastolic <= 80 => 'normal',
            $diastolic <= 90 => 'pre_high',
            default => 'high',
        };
        $categoryRank = ['low' => 1, 'normal' => 2, 'pre_high' => 3, 'high' => 4];
        $category = $categoryRank[$systolicCategory] >= $categoryRank[$diastolicCategory]
            ? $systolicCategory
            : $diastolicCategory;
        $isCrisis = $systolic > 180 || $diastolic > 120;

        return match ($category) {
            'low' => [
                'label' => 'Low Blood Pressure',
                'explanation' => 'Both systolic and diastolic readings are below the low blood pressure range.',
                'recommendation' => 'Drink enough fluids and seek care if dizziness, fainting, or weakness occurs.',
                'color' => '#7C3AED',
                'is_crisis' => false,
            ],
            'normal' => [
                'label' => 'Normal Blood Pressure',
                'explanation' => 'The reading is within the normal blood pressure range.',
                'recommendation' => 'Maintain healthy habits and continue regular monitoring.',
                'color' => '#16A34A',
                'is_crisis' => false,
            ],
            'pre_high' => [
                'label' => 'Pre-high Blood Pressure',
                'explanation' => 'At least one reading is in the pre-high range, so the higher-risk category is shown.',
                'recommendation' => 'Reduce salt, maintain healthy activity, and schedule a follow-up check.',
                'color' => '#CA8A04',
                'is_crisis' => false,
            ],
            default => [
                'label' => 'High Blood Pressure',
                'explanation' => 'At least one reading is in the high range, so the higher-risk category is shown.',
                'recommendation' => $isCrisis
                    ? 'A reading above 180 systolic or 120 diastolic needs special attention. Seek emergency care immediately if chest pain, shortness of breath, weakness, or vision changes occur.'
                    : 'Arrange a prompt health-center follow-up and take prescribed medicine.',
                'color' => '#DC2626',
                'is_crisis' => $isCrisis,
            ],
        };
    }

    public function notificationSummary(HouseholdMonitoringCase $case): string
    {
        return 'Check the info in Maintenance.';
    }
}
