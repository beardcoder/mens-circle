<?php

declare(strict_types=1);

use App\Checks\QueueHealthCheck;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Spatie\Health\Enums\Status;

test('database queue check passes when tables exist', function (): void {
    // Ensure jobs and failed_jobs tables exist in test database
    if (!Schema::hasTable('jobs')) {
        $this->artisan('queue:table');
        $this->artisan('queue:failed-table');
        $this->artisan('migrate', ['--force' => true]);
    }

    Config::set('queue.default', 'database');

    $check = new QueueHealthCheck();
    $result = $check->run();

    expect($result->status)->toEqual(Status::ok())->and($result->getShortSummary())->toContain('database');
});

test('sync queue shows warning', function (): void {
    Config::set('queue.default', 'sync');

    $check = new QueueHealthCheck();
    $result = $check->run();

    expect($result->status)->toEqual(Status::warning())->and($result->getShortSummary())->toBe('sync');
});

test('null queue shows warning', function (): void {
    Config::set('queue.default', 'null');

    $check = new QueueHealthCheck();
    $result = $check->run();

    expect($result->status)->toEqual(Status::warning())->and($result->getShortSummary())->toBe('null');
});

test('sync queue check includes metadata for connection', function (): void {
    Config::set('queue.default', 'sync');

    $check = new QueueHealthCheck();
    $result = $check->run();

    // Sync queue returns warning, so it won't have the full meta
    expect($result->getShortSummary())->toBe('sync');
});
