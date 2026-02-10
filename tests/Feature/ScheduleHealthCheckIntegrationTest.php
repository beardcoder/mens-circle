<?php

declare(strict_types=1);

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Checks\ScheduleCheck;
use Spatie\Health\Commands\ScheduleCheckHeartbeatCommand;
use Spatie\Health\Facades\Health;

test('schedule check uses configured cache store', function (): void {
    $scheduleCheck = Health::registeredChecks()
        ->first(fn (Check $check): bool => $check instanceof ScheduleCheck);

    expect($scheduleCheck)
        ->toBeInstanceOf(ScheduleCheck::class)
        ->and($scheduleCheck->getCacheStoreName())
        ->toBe((string) config('health.schedule.cache_store'));
});

test('schedule heartbeat survives default cache clear', function (): void {
    $scheduleCheck = Health::registeredChecks()
        ->first(fn (Check $check): bool => $check instanceof ScheduleCheck);

    expect($scheduleCheck)->toBeInstanceOf(ScheduleCheck::class);

    /** @var ScheduleCheck $scheduleCheck */
    $cacheStoreName = $scheduleCheck->getCacheStoreName();
    $cacheKey = $scheduleCheck->getCacheKey();

    cache()->store($cacheStoreName)->forget($cacheKey);

    $this->artisan(ScheduleCheckHeartbeatCommand::class)->assertExitCode(0);

    $heartbeatBeforeClear = cache()->store($cacheStoreName)->get($cacheKey);
    expect($heartbeatBeforeClear)->not->toBeNull();

    $this->artisan('cache:clear')->assertExitCode(0);

    expect(cache()->store($cacheStoreName)->get($cacheKey))
        ->toBe($heartbeatBeforeClear);

    $result = $scheduleCheck->run();

    expect($result->status->value)->toBe('ok');
});
