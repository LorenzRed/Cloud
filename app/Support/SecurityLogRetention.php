<?php

namespace App\Support;

use App\Models\SystemActivityLog;
use App\Models\SystemError;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class SecurityLogRetention
{
    public static function purge(): int
    {
        return DB::transaction(function () {
            $cutoff = now()->subDays(30);
            return SystemActivityLog::where('created_at', '<', $cutoff)->delete()
                + SystemError::where('created_at', '<', $cutoff)->delete();
        });
    }

    public static function purgeIfDue(): void
    {
        if (! Cache::add('security-logs:last-purge', true, 3600)) {
            return;
        }
        try {
            self::purge();
        } catch (Throwable) {
            Cache::forget('security-logs:last-purge');
        }
    }
}
