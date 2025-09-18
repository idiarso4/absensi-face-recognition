<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Schedule automated backups
Schedule::command('backup:clean')->daily()->at('01:00');
Schedule::command('backup:run')->daily()->at('02:00');
Schedule::command('backup:monitor')->daily()->at('06:00');

// Schedule data cleanup
Schedule::command('app:cleanup-old-data')->weekly()->sundays()->at('03:00');

// Schedule automated report generation
Schedule::command('app:generate-reports monthly-summary --month=' . now()->subMonth()->format('Y-m') . ' --save')
        ->monthlyOn(1, '06:00')
        ->description('Generate monthly attendance summary report');

Schedule::command('app:generate-reports attendance --save')
        ->weekly()->mondays()->at('07:00')
        ->description('Generate weekly attendance report');

Schedule::command('app:generate-reports leave --save')
        ->weekly()->mondays()->at('07:30')
        ->description('Generate weekly leave report');

// Schedule automated report email sending
Schedule::command('app:generate-reports monthly-summary --month=' . now()->subMonth()->format('Y-m') . ' --email=admin@smkn1punggelan.sch.id')
        ->monthlyOn(1, '08:00')
        ->description('Send monthly attendance summary report via email');

// Schedule automated report generation based on templates
Schedule::command('app:generate-scheduled-reports')
        ->everyMinute()
        ->description('Generate reports for scheduled templates');
