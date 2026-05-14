<?php

declare(strict_types=1);

use App\Console\Commands\GenerateSitemap;
use App\Console\Commands\SendEventReminders;
use Illuminate\Support\Facades\Schedule;

// Event Management
Schedule::command(SendEventReminders::class)->everyFifteenMinutes()->withoutOverlapping()->onOneServer();

// SEO
Schedule::command(GenerateSitemap::class)->daily()->at('02:00')->withoutOverlapping()->onOneServer()->runInBackground();

// Health checks Checks
Schedule::command(::class)->everyMinute()->runInBackground();
Schedule::command(::class)->everyMinute()->runInBackground();
Schedule::command(::class)->everyMinute()->runInBackground();
