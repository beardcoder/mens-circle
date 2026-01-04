<?php

declare(strict_types=1);

use App\Console\Commands\GenerateSitemap;
use App\Console\Commands\SendEventReminders;
use Illuminate\Support\Facades\Schedule;


Schedule::command(SendEventReminders::class)->daily()->at('10:00');
Schedule::command(GenerateSitemap::class)->daily()->at('02:00');
