<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class BnsImmunizationSchedule
{
    /**
     * DOH EPI routine immunization schedule for children 0–59 months.
     * due_months uses fractional months (6 weeks ≈ 1.5 months).
     *
     * @return list<array{code: string, name: string, due_months: float}>
     */
    public static function vaccines(): array
    {
        return [
            ['code' => 'bcg', 'name' => 'BCG', 'due_months' => 0],
            ['code' => 'hep_b_birth', 'name' => 'Hepatitis B (Birth dose)', 'due_months' => 0],
            ['code' => 'penta_1', 'name' => 'Pentavalent Vaccine (DPT-HepB-Hib)', 'due_months' => 1.5],
            ['code' => 'opv_1', 'name' => 'Oral Polio Vaccine (OPV)', 'due_months' => 1.5],
            ['code' => 'pcv_1', 'name' => 'Pneumococcal Conjugated Vaccine (PCV)', 'due_months' => 1.5],
            ['code' => 'penta_2', 'name' => 'Pentavalent Vaccine (DPT-HepB-Hib)', 'due_months' => 2.5],
            ['code' => 'opv_2', 'name' => 'Oral Polio Vaccine (OPV)', 'due_months' => 2.5],
            ['code' => 'pcv_2', 'name' => 'Pneumococcal Conjugated Vaccine (PCV)', 'due_months' => 2.5],
            ['code' => 'penta_3', 'name' => 'Pentavalent Vaccine (DPT-HepB-Hib)', 'due_months' => 3.5],
            ['code' => 'opv_3', 'name' => 'Oral Polio Vaccine (OPV)', 'due_months' => 3.5],
            ['code' => 'pcv_3', 'name' => 'Pneumococcal Conjugated Vaccine (PCV)', 'due_months' => 3.5],
            ['code' => 'ipv', 'name' => 'Inactivated Polio Vaccine (IPV)', 'due_months' => 3.5],
            ['code' => 'mmr_1', 'name' => 'Measles, Mumps, Rubella (MMR)', 'due_months' => 9],
            ['code' => 'mmr_2', 'name' => 'Measles, Mumps, Rubella (MMR)', 'due_months' => 12],
        ];
    }

    public static function vaccineCodes(): array
    {
        return array_column(self::vaccines(), 'code');
    }

    public static function dueDateFor(string $birthdate, float $dueMonths): Carbon
    {
        $bornAt = Carbon::parse($birthdate)->startOfDay();
        $wholeMonths = (int) floor($dueMonths);
        $extraDays = (int) round(($dueMonths - $wholeMonths) * 30);

        return $bornAt->copy()->addMonths($wholeMonths)->addDays($extraDays);
    }

    /**
     * @return list<array{code: string, name: string, due_date: string, due_date_label: string}>
     */
    public static function scheduleForBirthdate(string $birthdate): array
    {
        return collect(self::vaccines())
            ->map(function (array $vaccine) use ($birthdate) {
                $dueDate = self::dueDateFor($birthdate, $vaccine['due_months']);

                return [
                    'code' => $vaccine['code'],
                    'name' => $vaccine['name'],
                    'due_date' => $dueDate->toDateString(),
                    'due_date_label' => $dueDate->format('M d, Y'),
                    'due_months' => $vaccine['due_months'],
                ];
            })
            ->all();
    }

    /**
     * Vitamin A supplementation rounds (6–59 months, every 6 months).
     *
     * @return list<int>
     */
    public static function vitaminADueMonths(): array
    {
        return [6, 12, 18, 24, 30, 36, 42, 48, 54];
    }

    public static function isEligibleForVitaminA(int $ageInMonths): bool
    {
        return $ageInMonths >= 6 && $ageInMonths <= 59;
    }

    public static function dueAgeLabel(float $dueMonths): string
    {
        if ($dueMonths <= 0) {
            return 'At birth';
        }

        return match ($dueMonths) {
            1.5 => '1 and 1/2 months old',
            2.5 => '2 and 1/2 months old',
            3.5 => '3 and 1/2 months old',
            9 => '9 months old',
            12 => '1 year old',
            default => ((int) $dueMonths).' months old',
        };
    }

    public static function dueMonthsForCode(string $code): ?float
    {
        foreach (self::vaccines() as $vaccine) {
            if ($vaccine['code'] === $code) {
                return $vaccine['due_months'];
            }
        }

        return null;
    }

    /**
     * UI row layout for BNS monitoring immunization tab.
     *
     * @return list<array{cols: int, codes: list<string>}>
     */
    public static function displayLayout(): array
    {
        return [
            ['cols' => 2, 'codes' => ['bcg', 'hep_b_birth']],
            ['cols' => 3, 'codes' => ['penta_1', 'opv_1', 'pcv_1']],
            ['cols' => 1, 'codes' => ['ipv']],
            ['cols' => 3, 'codes' => ['penta_2', 'opv_2', 'pcv_2']],
            ['cols' => 3, 'codes' => ['penta_3', 'opv_3', 'pcv_3']],
            ['cols' => 2, 'codes' => ['mmr_1', 'mmr_2']],
        ];
    }
}
