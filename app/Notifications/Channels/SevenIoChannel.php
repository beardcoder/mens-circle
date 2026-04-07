<?php

declare(strict_types=1);

namespace App\Notifications\Channels;

use App\Notifications\Messages\SevenIoMessage;
use Exception;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Seven\Api\Client;
use Seven\Api\Resource\Sms\SmsParams;
use Seven\Api\Resource\Sms\SmsResource;

final class SevenIoChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        /** @var SevenIoMessage $message */
        $message = $notification->toSevenIo($notifiable); // @phpstan-ignore method.notFound

        /** @var string|null $phoneNumber */
        $phoneNumber = $notifiable->routeNotificationFor('sevenIo', $notification); // @phpstan-ignore method.notFound

        if (!$phoneNumber) {
            return;
        }

        /** @var string|null $apiKey */
        $apiKey = config('sevenio.api_key');

        if (!$apiKey) {
            Log::warning('Cannot send SMS - Seven.io API key not configured');

            return;
        }

        try {
            $client = new Client($apiKey);
            $smsResource = new SmsResource($client);
            /** @var string $from */
            $from = $message->from ?? config('sevenio.from', '');
            $params = new SmsParams(text: $message->content, to: $phoneNumber, from: $from);
            $smsResource->dispatch($params);
        } catch (Exception $exception) {
            Log::error('Failed to send SMS via Seven.io', [
                'phone_number' => $phoneNumber,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
