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
    $this->info("Expired announcements are kept in Posts (marked expired). Nothing deleted ({$deleted}).");
})->purpose('No-op: expired announcements stay in Posts and are hidden from residents');

Artisan::command('verification-ids:purge-expired', function () {
    $purged = ResidentRequest::purgeExpiredVerificationIds();
    $this->info("Purged verification ID files from {$purged} reviewed request(s).");
})->purpose('Delete accepted/rejected verification ID files after 7 days');

Schedule::command('events:purge-expired')->everyMinute();
Schedule::command('verification-ids:purge-expired')->daily();
