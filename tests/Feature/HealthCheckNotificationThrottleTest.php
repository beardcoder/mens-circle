<?php

declare(strict_types=1);

use App\Notifications\HealthCheckFailedNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Spatie\Health\Checks\Result;
use Spatie\Health\Notifications\Notifiable;

test('notification is sent when no previous notification exists', function (): void {
    Config::set('health.notifications.enabled', true);
    Config::set('health.notifications.throttle_notifications_for_minutes', 60);

    Cache::flush();

    $results = [
        Result::make()
            ->failed()
            ->notificationMessage('Test check failed'),
    ];

    $notification = new HealthCheckFailedNotification($results);
    $notifiable = new Notifiable();

    expect($notification->shouldSend($notifiable, 'mail'))->toBeTrue();
});

test('notification is throttled within throttle window', function (): void {
    Config::set('health.notifications.enabled', true);
    Config::set('health.notifications.throttle_notifications_for_minutes', 60);

    Cache::flush();

    $results = [
        Result::make()
            ->failed()
            ->notificationMessage('Test check failed'),
    ];

    $notification = new HealthCheckFailedNotification($results);
    $notifiable = new Notifiable();

    // First notification should be sent
    expect($notification->shouldSend($notifiable, 'mail'))->toBeTrue();

    // Second notification within throttle window should be blocked
    $secondNotification = new HealthCheckFailedNotification($results);
    expect($secondNotification->shouldSend($notifiable, 'mail'))->toBeFalse();
});

test('notification is sent after throttle window expires', function (): void {
    Config::set('health.notifications.enabled', true);
    Config::set('health.notifications.throttle_notifications_for_minutes', 60);

    Cache::flush();

    $results = [
        Result::make()
            ->failed()
            ->notificationMessage('Test check failed'),
    ];

    $notification = new HealthCheckFailedNotification($results);
    $notifiable = new Notifiable();

    // First notification should be sent
    expect($notification->shouldSend($notifiable, 'mail'))->toBeTrue();

    // Simulate time passing (61 minutes)
    $cacheKey = config('health.notifications.throttle_notifications_key') . 'mail';
    Cache::put($cacheKey, now()->subMinutes(61), now()->addHour());

    // Second notification after throttle window should be sent
    $secondNotification = new HealthCheckFailedNotification($results);
    expect($secondNotification->shouldSend($notifiable, 'mail'))->toBeTrue();
});

test('notification is not sent when notifications are disabled', function (): void {
    Config::set('health.notifications.enabled', false);

    Cache::flush();

    $results = [
        Result::make()
            ->failed()
            ->notificationMessage('Test check failed'),
    ];

    $notification = new HealthCheckFailedNotification($results);
    $notifiable = new Notifiable();

    expect($notification->shouldSend($notifiable, 'mail'))->toBeFalse();
});

test('notification is always sent when throttle is set to zero', function (): void {
    Config::set('health.notifications.enabled', true);
    Config::set('health.notifications.throttle_notifications_for_minutes', 0);

    Cache::flush();

    $results = [
        Result::make()
            ->failed()
            ->notificationMessage('Test check failed'),
    ];

    $notification = new HealthCheckFailedNotification($results);
    $notifiable = new Notifiable();

    // First notification should be sent
    expect($notification->shouldSend($notifiable, 'mail'))->toBeTrue();

    // Second notification should also be sent (no throttling)
    $secondNotification = new HealthCheckFailedNotification($results);
    expect($secondNotification->shouldSend($notifiable, 'mail'))->toBeTrue();
});

test('throttling is independent per channel', function (): void {
    Config::set('health.notifications.enabled', true);
    Config::set('health.notifications.throttle_notifications_for_minutes', 60);

    Cache::flush();

    $results = [
        Result::make()
            ->failed()
            ->notificationMessage('Test check failed'),
    ];

    $notification = new HealthCheckFailedNotification($results);
    $notifiable = new Notifiable();

    // First notification via mail should be sent
    expect($notification->shouldSend($notifiable, 'mail'))->toBeTrue();

    // First notification via slack should also be sent (different channel)
    expect($notification->shouldSend($notifiable, 'slack'))->toBeTrue();

    // Second notification via mail should be throttled
    expect($notification->shouldSend($notifiable, 'mail'))->toBeFalse();

    // Second notification via slack should also be throttled
    expect($notification->shouldSend($notifiable, 'slack'))->toBeFalse();
});
