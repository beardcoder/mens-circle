<?php

declare(strict_types=1);

namespace App\Actions;

use App\Mail\EventParticipantMessage;
use App\Models\Event;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final readonly class SendMessageToEventParticipants
{
    /**
     * Queue a templated message for every active registration on the event.
     *
     * @return array{sent: int, failed: int}
     */
    public function execute(Event $event, string $subject, string $content): array
    {
        $registrations = $event->activeRegistrations()->with('participant')->get();

        $sent = 0;
        $failed = 0;

        foreach ($registrations as $registration) {
            try {
                Mail::to($registration->participant->email)->queue(new EventParticipantMessage(
                    mailSubject: $subject,
                    mailContent: $content,
                    event: $event,
                    participantName: $registration->participant->first_name,
                ));
                $sent++;
            } catch (Exception $exception) {
                Log::error('Failed to send participant message', [
                    'event_id' => $event->id,
                    'participant_id' => $registration->participant->id,
                    'error' => $exception->getMessage(),
                ]);
                $failed++;
            }
        }

        return ['sent' => $sent, 'failed' => $failed];
    }
}
