<?php

use App\Models\Event;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('events:purge-expired', function () {
    $deleted = Event::purgeExpired();
    $this->info("Deleted {$deleted} expired announcement record(s).");
})->purpose('Delete announcements whose show-until date has passed');

Schedule::command('events:purge-expired')->everyMinute();
