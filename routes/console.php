<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule::command('app:event-crawler-command')->monthly();
Schedule::command('app:change-campaign-status')->daily();

Schedule::command('app:notify-session-completion')->sundays()->at('00:00');

Schedule::command('app:send-session-reminder')->fridays()->at('18:00');

Schedule::command('app:leave-deduction')->mondays()->at('00:00');

Schedule::command('tokens:clear-inactive')->weekly();

