<?php

namespace App\Support;

use App\Models\ResidentRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class BhwHeadScope
{
    public static function assignedBarangayName(User $head): ?string
    {
        if ($head->role !== User::ROLE_BHW_HEAD) {
            return null;
        }

        $barangay = trim((string) ($head->barangay ?? ''));

        return $barangay !== '' ? $barangay : null;
    }

    /**
     * @return Collection<int, User>
     */
    public static function managedBhwWorkers(User $head): Collection
    {
        if ($head->role !== User::ROLE_BHW_HEAD) {
            return collect();
        }

        return User::query()
            ->where('role', User::ROLE_BHW)
            ->whereRaw('LOWER(TRIM(barangay)) = ?', [mb_strtolower((string) $head->barangay)])
            ->where(function ($query) use ($head): void {
                $query
                    ->whereNull('managed_by_user_id')
                    ->orWhere('managed_by_user_id', $head->id);
            })
            ->with('assignedPuroks')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return list<int>
     */
    public static function assignedPurokIds(User $head): array
    {
        $ids = self::managedBhwWorkers($head)
            ->flatMap(fn (User $worker) => $worker->assignedPuroks->pluck('id'))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        return $ids;
    }

    public static function apply(Builder $query, User $head): Builder
    {
        $barangay = self::assignedBarangayName($head);
        $purokIds = self::assignedPurokIds($head);

        if ($barangay === null || $purokIds === []) {
            return $query->whereRaw('0 = 1');
        }

        return $query
            ->whereRaw('LOWER(TRIM(barangay)) = ?', [mb_strtolower($barangay)])
            ->whereIn('purok_id', $purokIds);
    }

    public static function canAccess(User $head, ResidentRequest $residentRequest): bool
    {
        if ($head->role !== User::ROLE_BHW_HEAD) {
            return false;
        }

        $assignedBarangay = self::assignedBarangayName($head);
        $purokIds = self::assignedPurokIds($head);

        if ($assignedBarangay === null || $purokIds === [] || $residentRequest->purok_id === null) {
            return false;
        }

        $requestBarangay = trim((string) ($residentRequest->barangay ?? ''));

        return mb_strtolower($requestBarangay) === mb_strtolower($assignedBarangay)
            && in_array((int) $residentRequest->purok_id, $purokIds, true);
    }

    public static function canManageWorker(User $head, User $worker): bool
    {
        return $head->role === User::ROLE_BHW_HEAD
            && $worker->role === User::ROLE_BHW
            && mb_strtolower(trim((string) $worker->barangay)) === mb_strtolower(trim((string) $head->barangay))
            && (
                $worker->managed_by_user_id === null
                || (int) $worker->managed_by_user_id === (int) $head->id
            );
    }
}
