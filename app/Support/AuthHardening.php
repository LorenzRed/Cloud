<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AuthHardening
{
    /**
     * Kick every web session and API token for this user (e.g. after password reset).
     */
    public static function revokeAllAccess(User $user): void
    {
        $user->forceFill([
            'remember_token' => Str::random(60),
        ])->save();

        app(AuthTokenService::class)->revokeAll($user);

        if (Schema::hasTable('sessions') && Schema::hasColumn('sessions', 'user_id')) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }
    }

    /**
     * Kick other devices after a logged-in password change; keep the current browser session.
     */
    public static function revokeOtherAccess(User $user, ?string $currentSessionId = null): void
    {
        app(AuthTokenService::class)->revokeAll($user);

        $user->forceFill([
            'remember_token' => Str::random(60),
        ])->save();

        if (Schema::hasTable('sessions') && Schema::hasColumn('sessions', 'user_id')) {
            $query = DB::table('sessions')->where('user_id', $user->id);

            if (filled($currentSessionId)) {
                $query->where('id', '!=', $currentSessionId);
            }

            $query->delete();
        }
    }
}
