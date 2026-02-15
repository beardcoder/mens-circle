<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Service;

use BeardCoder\MensCircle\Domain\Model\Registration;
use BeardCoder\MensCircle\Domain\Repository\RegistrationRepository;

final readonly class RegistrationNotificationDataService
{
    public function __construct(
        private RegistrationRepository $registrationRepository,
    ) {}

    /**
     * @return array{
     *   registrationUid: int,
     *   status: string,
     *   participantEmail: string,
     *   participantFirstName: string,
     *   participantLastName: string,
     *   participantPhone: string,
     *   eventTitle: string,
     *   eventSlug: string,
     *   eventDate: string,
     *   eventStartTime: string,
     *   eventEndTime: string,
     *   eventLocation: string,
     *   eventCity: string
     * }|null
     */
    public function findByRegistrationUid(int $registrationUid): ?array
    {
        if ($registrationUid <= 0) {
            return null;
        }

        /** @var Registration|null $registration */
        $registration = $this->registrationRepository->findByUid($registrationUid);
        if (!$registration instanceof Registration) {
            return null;
        }

        $participant = $registration->participant;
        $event = $registration->event;

        if ($participant === null || $event === null) {
            return null;
        }

        return [
            'registrationUid' => (int)$registration->getUid(),
            'status' => $registration->status,
            'participantEmail' => $participant->email,
            'participantFirstName' => $participant->firstName,
            'participantLastName' => $participant->lastName,
            'participantPhone' => $participant->phone,
            'eventTitle' => $event->title,
            'eventSlug' => $event->slug,
            'eventDate' => $event->eventDate?->format('Y-m-d H:i:s') ?? '',
            'eventStartTime' => $event->startTime?->format('H:i:s') ?? '',
            'eventEndTime' => $event->endTime?->format('H:i:s') ?? '',
            'eventLocation' => $event->location,
            'eventCity' => $event->city,
        ];
    }
}
