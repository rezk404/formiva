<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 | Scheduled content becomes published content here, and only here. Ten
 | minutes is fine granularity for a journal; anything tighter would give
 | the impression the site publishes to the second, which it does not.
 */
Schedule::command('formiva:publish-due')->everyTenMinutes()->withoutOverlapping();
