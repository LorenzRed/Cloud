<?php

namespace App\Support;

use App\Models\ResidentRequest;
use App\Models\User;

class VerificationIdAccess
{
    public static function canView(User $viewer, ResidentRequest $residentRequest): bool
    {
        if ((int) $residentRequest->user_id === (int) $viewer->id) {
            return true;
        }

        if ($viewer->role === User::ROLE_BHW) {
            return BhwResidentScope::canAccess($viewer, $residentRequest);
        }

        $requestBarangay = trim((string) ($residentRequest->barangay ?? ''));
        $viewerBarangay = trim((string) ($viewer->barangay ?? ''));

        if ($viewer->role === User::ROLE_BRG_CAPTAIN || $viewer->role === User::ROLE_BNS) {
            return $requestBarangay !== ''
                && $viewerBarangay !== ''
                && strcasecmp($requestBarangay, $viewerBarangay) === 0;
        }

        if ($viewer->role === User::ROLE_BHW_HEAD) {
            return $requestBarangay !== ''
                && $viewerBarangay !== ''
                && strcasecmp($requestBarangay, $viewerBarangay) === 0;
        }

        if ($viewer->role === User::ROLE_LGU) {
            // LGU may review any barangay registration ID in the municipality.
            return true;
        }

        return false;
    }
}
