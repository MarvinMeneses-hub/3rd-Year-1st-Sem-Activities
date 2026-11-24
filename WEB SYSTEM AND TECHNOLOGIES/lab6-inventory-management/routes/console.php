<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Automatically check inventory reorder points every day at 8:00 AM.
Schedule::command('app:check-reorder-points')
    ->dailyAt('08:00')
    ->withoutOverlapping();
