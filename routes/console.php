<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('health:check')->everyMinute();
Schedule::command('health:schedule-check-heartbeat')->everyMinute();
Schedule::command('health:queue-check-heartbeat')->everyMinute();

Schedule::command('events:send-reminders')->daily()->at('10:00');
Schedule::command('backup:clean')->daily()->at('01:00');
Schedule::command('backup:run')->daily()->at('01:30');
Schedule::command('sitemap:generate')->daily()->at('02:00');
