<?php

declare(strict_types=1);

namespace MarkusSommer\MensCircle\Controller;

use MarkusSommer\MensCircle\Domain\Enum\RegistrationStatus;
use MarkusSommer\MensCircle\Domain\Model\Event;
use MarkusSommer\MensCircle\Domain\Model\NewsletterSubscription;
use MarkusSommer\MensCircle\Domain\Model\Participant;
use MarkusSommer\MensCircle\Domain\Model\Registration;
use MarkusSommer\MensCircle\Domain\Repository\EventRepository;
use MarkusSommer\MensCircle\Domain\Repository\NewsletterSubscriptionRepository;
use MarkusSommer\MensCircle\Domain\Repository\ParticipantRepository;
use MarkusSommer\MensCircle\Domain\Repository\RegistrationRepository;
use MarkusSommer\MensCircle\Message\SendEventMailMessage;
use MarkusSommer\MensCircle\Message\SendEventSmsMessage;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use TYPO3\CMS\Core\Http\ResponseFactory;
use TYPO3\CMS\Core\Http\StreamFactory;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

final class EventController extends ActionController
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly ParticipantRepository $participantRepository,
        private readonly RegistrationRepository $registrationRepository,
        private readonly NewsletterSubscriptionRepository $newsletterSubscriptionRepository,
        private readonly PersistenceManager $persistenceManager,
        private readonly MessageBusInterface $messageBus,
        private readonly ResponseFactory $httpResponseFactory,
        private readonly StreamFactory $httpStreamFactory
    ) {
    }

    public function listAction(): ResponseInterface
    {
        $events = $this->eventRepository->findUpcomingPublished();
        $nextEvent = $this->eventRepository->findNextEvent();

        $eventSummaries = [];
        foreach ($events as $event) {
            $registrations = $this->registrationRepository->countActiveByEvent($event);
            $eventSummaries[] = [
                'event' => $event,
                'availableSpots' => max(0, $event->getMaxParticipants() - $registrations),
            ];
        }

        $this->view->assignMultiple([
            'events' => $events,
            'eventSummaries' => $eventSummaries,
            'nextEvent' => $nextEvent,
        ]);

        return $this->htmlResponse();
    }

    public function showAction(Event $event): ResponseInterface
    {
        if (! $event->isPublished()) {
            $this->addFlashMessage('Dieser Termin ist nicht öffentlich.', '', AbstractMessage::WARNING);

            return $this->redirect('list');
        }

        $activeRegistrations = $this->registrationRepository->countActiveByEvent($event);
        $availableSpots = max(0, $event->getMaxParticipants() - $activeRegistrations);
        $eventDate = $event->getEventDate();
        $startTime = $event->getStartTime();
        $endTime = $event->getEndTime();

        $this->view->assignMultiple([
            'event' => $event,
            'activeRegistrations' => $activeRegistrations,
            'availableSpots' => $availableSpots,
            'isFull' => $availableSpots <= 0,
            'isPast' => $event->isPast(),
            'canRegister' => !($availableSpots <= 0 || $event->isPast()),
            'eventData' => [
                'title' => $event->getTitle(),
                'description' => trim(strip_tags($event->getDescription())),
                'location' => $event->getLocation(),
                'startDate' => $eventDate?->format('Y-m-d') ?? '',
                'startTime' => $startTime?->format('H:i') ?? '19:00',
                'endDate' => $eventDate?->format('Y-m-d') ?? '',
                'endTime' => $endTime?->format('H:i') ?? '21:30',
            ],
        ]);

        return $this->htmlResponse();
    }

    public function registerAction(): ResponseInterface
    {
        $arguments = $this->request->getArguments();
        $eventUid = (int) ($arguments['event_id'] ?? $arguments['event'] ?? 0);

        /** @var Event|null $event */
        $event = $eventUid > 0 ? $this->eventRepository->findByUid($eventUid) : null;
        if (! $event instanceof Event) {
            $this->addFlashMessage('Ungültiger Termin.', '', AbstractMessage::ERROR);

            return $this->redirect('list');
        }

        $firstName = trim((string) ($arguments['first_name'] ?? $arguments['firstName'] ?? ''));
        $lastName = trim((string) ($arguments['last_name'] ?? $arguments['lastName'] ?? ''));
        $email = strtolower(trim((string) ($arguments['email'] ?? '')));
        $phone = trim((string) ($arguments['phone_number'] ?? $arguments['phone'] ?? ''));
        $privacyAccepted = $this->isTruthy($arguments['privacy'] ?? null);
        $newsletterOptIn = $this->isTruthy($arguments['newsletterOptIn'] ?? null);

        if ($firstName === '' || $lastName === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || ! $privacyAccepted) {
            $this->addFlashMessage('Bitte fülle alle Pflichtfelder korrekt aus.', '', AbstractMessage::ERROR);

            return $this->redirect('show', null, null, ['event' => $event->getUid()]);
        }

        if (! $event->isPublished()) {
            $this->addFlashMessage('Dieser Termin ist nicht verfügbar.', '', AbstractMessage::ERROR);

            return $this->redirect('show', null, null, ['event' => $event->getUid()]);
        }

        if ($event->isPast()) {
            $this->addFlashMessage('Dieser Termin liegt bereits in der Vergangenheit.', '', AbstractMessage::ERROR);

            return $this->redirect('show', null, null, ['event' => $event->getUid()]);
        }

        $activeRegistrations = $this->registrationRepository->countActiveByEvent($event);
        if ($activeRegistrations >= $event->getMaxParticipants()) {
            $this->addFlashMessage('Dieser Termin ist leider bereits ausgebucht.', '', AbstractMessage::ERROR);

            return $this->redirect('show', null, null, ['event' => $event->getUid()]);
        }

        $participant = $this->participantRepository->findOneByEmail($email);
        if (! $participant instanceof Participant) {
            $participant = new Participant();
            $participant->setEmail($email);
            $participant->setFirstName($firstName);
            $participant->setLastName($lastName);
            $participant->setPhone($phone);
            $this->participantRepository->add($participant);
        } else {
            $participant->setFirstName($firstName);
            $participant->setLastName($lastName);
            $participant->setPhone($phone);
            $this->participantRepository->update($participant);
        }

        $existingRegistration = $this->registrationRepository->findActiveByEventAndParticipant($event, $participant);
        if ($existingRegistration instanceof Registration) {
            $this->addFlashMessage('Du bist bereits für diesen Termin angemeldet.', '', AbstractMessage::WARNING);

            return $this->redirect('show', null, null, ['event' => $event->getUid()]);
        }

        $registration = new Registration();
        $registration->setEvent($event);
        $registration->setParticipant($participant);
        $registration->setStatus(RegistrationStatus::Registered);
        $registration->setRegisteredAt(new \DateTime());
        $this->registrationRepository->add($registration);

        if ($newsletterOptIn) {
            $subscription = $this->newsletterSubscriptionRepository->findOneByParticipant($participant);
            if (! $subscription instanceof NewsletterSubscription) {
                $subscription = new NewsletterSubscription();
                $subscription->setParticipant($participant);
                $subscription->setToken(bin2hex(random_bytes(32)));
                $subscription->setSubscribedAt(new \DateTime());
                $this->newsletterSubscriptionRepository->add($subscription);
            } else {
                if ($subscription->getToken() === '') {
                    $subscription->setToken(bin2hex(random_bytes(32)));
                }
                $subscription->setSubscribedAt(new \DateTime());
                $subscription->setUnsubscribedAt(null);
                $this->newsletterSubscriptionRepository->update($subscription);
            }
        }

        $this->persistenceManager->persistAll();

        $notificationSettings = is_array($this->settings) ? $this->settings : [];
        $this->messageBus->dispatch(new SendEventMailMessage(
            registrationUid: (int) $registration->getUid(),
            type: SendEventMailMessage::TYPE_REGISTRATION_CONFIRMATION,
            settings: $notificationSettings
        ));

        if ($participant->getPhone() !== '') {
            $this->messageBus->dispatch(new SendEventSmsMessage(
                registrationUid: (int) $registration->getUid(),
                type: SendEventSmsMessage::TYPE_REGISTRATION_CONFIRMATION,
                settings: $notificationSettings
            ));
        }

        return $this->redirect('registerSuccess', null, null, ['event' => $event->getUid()]);
    }

    public function registerSuccessAction(Event $event): ResponseInterface
    {
        $this->view->assign('event', $event);

        return $this->htmlResponse();
    }

    public function icalAction(Event $event): ResponseInterface
    {
        $domain = (string) parse_url((string) ($this->settings['baseUrl'] ?? ''), PHP_URL_HOST);
        if ($domain === '') {
            $domain = 'mens-circle.local';
        }

        $ical = $event->generateIcalContent($domain);
        $filename = sprintf('mens-circle-%s.ics', $event->getSlug() !== '' ? $event->getSlug() : $event->getUid());

        $response = $this->httpResponseFactory->createResponse(200)
            ->withHeader('Content-Type', 'text/calendar; charset=utf-8')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->withHeader('Cache-Control', 'public, max-age=3600');

        return $response->withBody($this->httpStreamFactory->createStream($ical));
    }

    private function isTruthy(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if ($value === null) {
            return false;
        }

        if (is_int($value) || is_float($value)) {
            return (int) $value > 0;
        }

        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'on', 'yes'], true);
    }
}
