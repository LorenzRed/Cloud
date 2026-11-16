<?php

use App\Models\Event;
use App\Models\ResidentRequest;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('events:purge-expired', function () {
    $deleted = Event::purgeExpired();
    $this->info("Deleted {$deleted} expired announcement(s).");
})->purpose('Delete announcements that reached their end time');

Artisan::command('verification-ids:purge-expired', function () {
    $purged = ResidentRequest::purgeExpiredVerificationIds();
    $this->info("Purged verification ID files from {$purged} reviewed request(s).");
})->purpose('Delete accepted/rejected verification ID files after 7 days');

Schedule::command('events:purge-expired')->everyMinute();
Schedule::command('verification-ids:purge-expired')->daily();

Artisan::command('security-logs:purge-expired', function () {
    $deleted = \App\Support\SecurityLogRetention::purge();
    $this->info("Deleted {$deleted} login/error log entries older than 30 days.");
})->purpose('Delete system login and error logs older than 30 days');

Schedule::command('security-logs:purge-expired')->hourly()->withoutOverlapping();
