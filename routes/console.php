<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('users:cleanup')
    ->daily()
    ->sendOutputTo(storage_path('logs/cleanup_users.log'))
    ->appendOutputTo(storage_path('logs/cleanup_users.log'))
    ->withoutOverlapping()
    ->timezone('UTC')
    ->description('Delete soft-deleted users older than 30 days');
