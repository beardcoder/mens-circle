<?php

declare(strict_types=1);

use App\Console\Commands\GenerateSitemap;
use App\Console\Commands\SendEventReminders;
use Illuminate\Support\Facades\Schedule;

// Event Management
Schedule::command(SendEventReminders::class)->dailyAt('10:00');->everyFifteenMinutes()->withoutOverlapping()->onOneServer();

// SEO
Schedule::command(GenerateSitemap::class)->dailyAt('02:00');->daily()->at('02:00')->withoutOverlapping()->onOneServer()->runInBackground();

 Checks

