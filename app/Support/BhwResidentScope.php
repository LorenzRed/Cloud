<?php

namespace App\Support;

use App\Models\ResidentRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class BhwResidentScope
{
    public static function assignedBarangayName(User $bhw): ?string
    {
        $barangay = trim((string) ($bhw->barangay ?? ''));

        return $barangay !== '' ? $barangay : null;
    }

    /**
     * @return list<int>
     */
    public static function assignedPurokIds(User $bhw): array
    {
        if ($bhw->role !== 'BHW') {
            return [];
        }

        $ids = $bhw->relationLoaded('assignedPuroks')
            ? $bhw->assignedPuroks->pluck('id')->all()
            : $bhw->assignedPuroks()->pluck('puroks.id')->all();

        if ($ids !== []) {
            return array_map('intval', $ids);
        }

        return filled($bhw->purok_id) ? [(int) $bhw->purok_id] : [];
    }

    public static function apply(Builder $query, User $bhw): Builder
    {
        $barangay = self::assignedBarangayName($bhw);
        $purokIds = self::assignedPurokIds($bhw);

        if ($barangay === null || $purokIds === []) {
            return $query->whereRaw('0 = 1');
        }

        return $query
            ->whereRaw('LOWER(TRIM(barangay)) = ?', [mb_strtolower($barangay)])
            ->whereIn('purok_id', $purokIds);
    }

    public static function canAccess(User $bhw, ResidentRequest $residentRequest): bool
    {
        if ($bhw->role !== 'BHW') {
            return false;
        }

        $assignedBarangay = self::assignedBarangayName($bhw);
        $purokIds = self::assignedPurokIds($bhw);

        if ($assignedBarangay === null || $purokIds === [] || $residentRequest->purok_id === null) {
            return false;
        }

        $requestBarangay = trim((string) ($residentRequest->barangay ?? ''));

        return mb_strtolower($requestBarangay) === mb_strtolower($assignedBarangay)
            && in_array((int) $residentRequest->purok_id, $purokIds, true);
    }
}
