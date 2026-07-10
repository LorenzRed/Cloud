<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class PersonAge
{
    /** Infant window for years+months display (0–59 months). */
    public const INFANT_MAX_MONTHS = 59;
    public static function referenceDate(?Carbon $reference = null): Carbon
    {
        return ($reference ?? now())->copy()->startOfDay();
    }

    public static function parseBirthdate(?string $birthdate): ?Carbon
    {
        if (! filled($birthdate)) {
            return null;
        }

        try {
            return Carbon::parse((string) $birthdate)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    public static function ageInWholeMonths(?string $birthdate, ?Carbon $reference = null): ?int
    {
        $born = self::parseBirthdate($birthdate);
        if ($born === null) {
            return null;
        }

        $today = self::referenceDate($reference);
        if ($born->gt($today)) {
            return null;
        }

        $months = ($today->year - $born->year) * 12 + ($today->month - $born->month);
        if ($today->day < $born->day) {
            $months--;
        }

        return max(0, $months);
    }

    public static function ageInYears(?string $birthdate, ?Carbon $reference = null): ?int
    {
        $born = self::parseBirthdate($birthdate);
        if ($born === null) {
            return null;
        }

        $today = self::referenceDate($reference);
        if ($born->gt($today)) {
            return null;
        }

        $years = $today->year - $born->year;
        if ($today->month < $born->month || ($today->month === $born->month && $today->day < $born->day)) {
            $years--;
        }

        return max(0, $years);
    }

    public static function labelFromBirthdate(?string $birthdate, ?Carbon $reference = null): string
    {
        $born = self::parseBirthdate($birthdate);
        if ($born === null) {
            return '';
        }

        $today = self::referenceDate($reference);
        if ($born->gt($today)) {
            return '';
        }

        $diffDays = $born->diffInDays($today);
        if ($diffDays <= 0) {
            return 'Newborn';
        }

        if ($diffDays < 30) {
            return $diffDays === 1 ? '1 day old' : "{$diffDays} days old";
        }

        $months = self::ageInWholeMonths($birthdate, $today);
        if ($months === null) {
            return '';
        }

        if ($months < 12) {
            return $months <= 1 ? '1 month old' : "{$months} months old";
        }

        if ($months > self::INFANT_MAX_MONTHS) {
            $years = self::ageInYears($birthdate, $today);

            return $years === 1 ? '1 year old' : "{$years} years old";
        }

        return self::labelYearsAndMonths($months);
    }

    /** Years + months label for infants aged 12–59 months. */
    public static function labelYearsAndMonths(int $totalMonths): string
    {
        $years = intdiv($totalMonths, 12);
        $remainder = $totalMonths % 12;

        $yearPart = $years === 1 ? '1 year' : "{$years} years";

        if ($remainder === 0) {
            return "{$yearPart} old";
        }

        $monthPart = $remainder === 1 ? '1 month' : "{$remainder} months";

        return "{$yearPart} {$monthPart} old";
    }
}
