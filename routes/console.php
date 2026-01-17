<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();
/*
 * Command to calculate daily profit and loss
 * This command will be executed daily at 00:00
 * The command will log the output to a file
 */
Schedule::command('command:profit-loss-daily')
    ->dailyAt('00:00')
    ->sendOutputTo(storage_path('logs/cmd-profit-loss.log'))
    ->appendOutputTo(storage_path('logs/cmd-profit-loss.log'));
