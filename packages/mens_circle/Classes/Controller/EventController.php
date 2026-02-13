<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Controller;

use BeardCoder\MensCircle\Domain\Enum\RegistrationStatus;
use BeardCoder\MensCircle\Domain\Model\Event;
use BeardCoder\MensCircle\Domain\Model\NewsletterSubscription;
use BeardCoder\MensCircle\Domain\Model\Participant;
use BeardCoder\MensCircle\Domain\Model\Registration;
use BeardCoder\MensCircle\Domain\Repository\EventRepository;
use BeardCoder\MensCircle\Domain\Repository\NewsletterSubscriptionRepository;
use BeardCoder\MensCircle\Domain\Repository\ParticipantRepository;
use BeardCoder\MensCircle\Domain\Repository\RegistrationRepository;
use BeardCoder\MensCircle\Message\SendEventMailMessage;
use BeardCoder\MensCircle\Message\SendEventSmsMessage;
use DateTime;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use TYPO3\CMS\Core\Http\ResponseFactory;
use TYPO3\CMS\Core\Http\StreamFactory;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
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
        private readonly StreamFactory $httpStreamFactory,
    ) {}

    public function listAction(): ResponseInterface
    {
        $events = $this->eventRepository->findUpcomingPublished();

        $this->view->assignMultiple([
            'events' => $events,
            'eventSummaries' => $this->buildEventSummaries($events),
            'nextEvent' => $this->eventRepository->findNextEvent(),
        ]);

        return $this->htmlResponse();
    }

    public function showAction(Event $event): ResponseInterface
    {
        return $this->renderEventDetail($event);
    }

    public function detailAction(?Event $event = null): ResponseInterface
    {
        $resolvedEvent = $this->resolveDetailEvent($event);
        if (! $resolvedEvent instanceof Event) {
            $this->addFlashMessage('Aktuell ist kein passender Termin verfügbar.', '', ContextualFeedbackSeverity::WARNING);

            return $this->redirectToEventOverview();
        }

        return $this->renderEventDetail($resolvedEvent);
    }

    public function registerAction(
        string $event = '',
        string $firstName = '',
        string $lastName = '',
        string $email = '',
        string $phoneNumber = '',
        bool $privacy = false,
        bool $newsletterOptIn = false,
    ): ResponseInterface {
        $resolvedEvent = $this->resolveEventByIdentifier($event);
        if (! $resolvedEvent instanceof Event) {
            $this->addFlashMessage('Ungültiger Termin.', '', ContextualFeedbackSeverity::ERROR);

            return $this->redirectToEventOverview();
        }

        $normalizedFirstName = trim($firstName);
        $normalizedLastName = trim($lastName);
        $normalizedEmail = strtolower(trim($email));
        $normalizedPhoneNumber = trim($phoneNumber);

        if (! $this->isRegistrationInputValid($normalizedFirstName, $normalizedLastName, $normalizedEmail, $privacy)) {
            return $this->redirectToEventDetailWithMessage(
                $resolvedEvent,
                'Bitte fülle alle Pflichtfelder korrekt aus.',
                ContextualFeedbackSeverity::ERROR,
            );
        }

        if (! $resolvedEvent->isPublished()) {
            return $this->redirectToEventDetailWithMessage(
                $resolvedEvent,
                'Dieser Termin ist nicht verfügbar.',
                ContextualFeedbackSeverity::ERROR,
            );
        }

        if ($resolvedEvent->isPast()) {
            return $this->redirectToEventDetailWithMessage(
                $resolvedEvent,
                'Dieser Termin liegt bereits in der Vergangenheit.',
                ContextualFeedbackSeverity::ERROR,
            );
        }

        $activeRegistrations = $this->registrationRepository->countActiveByEvent($resolvedEvent);
        if ($activeRegistrations >= $resolvedEvent->getMaxParticipants()) {
            return $this->redirectToEventDetailWithMessage(
                $resolvedEvent,
                'Dieser Termin ist leider bereits ausgebucht.',
                ContextualFeedbackSeverity::ERROR,
            );
        }

        $participant = $this->upsertParticipant(
            email: $normalizedEmail,
            firstName: $normalizedFirstName,
            lastName: $normalizedLastName,
            phoneNumber: $normalizedPhoneNumber,
        );

        $existingRegistration = $this->registrationRepository->findActiveByEventAndParticipant($resolvedEvent, $participant);
        if ($existingRegistration instanceof Registration) {
            return $this->redirectToEventDetailWithMessage(
                $resolvedEvent,
                'Du bist bereits für diesen Termin angemeldet.',
                ContextualFeedbackSeverity::WARNING,
            );
        }

        $registration = $this->createRegistration($resolvedEvent, $participant);
        $this->registrationRepository->add($registration);

        if ($newsletterOptIn) {
            $this->activateNewsletterSubscription($participant);
        }

        $this->persistenceManager->persistAll();
        $this->dispatchRegistrationNotifications($registration, $participant);

        return $this->redirect('registerSuccess', null, null, ['event' => $resolvedEvent->getUid()]);
    }

    public function registerSuccessAction(Event $event): ResponseInterface
    {
        $this->view->assign('event', $event);

        return $this->htmlResponse();
    }

    public function icalAction(Event $event): ResponseInterface
    {
        $domain = (string)parse_url((string)($this->settings['baseUrl'] ?? ''), PHP_URL_HOST);
        if ($domain === '') {
            $domain = 'mens-circle.local';
        }

        $ical = $event->generateIcalContent($domain);
        $filename = \sprintf('mens-circle-%s.ics', $event->getSlug() !== '' ? $event->getSlug() : $event->getUid());

        $response = $this->httpResponseFactory->createResponse(200)
            ->withHeader('Content-Type', 'text/calendar; charset=utf-8')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->withHeader('Cache-Control', 'public, max-age=3600');

        return $response->withBody($this->httpStreamFactory->createStream($ical));
    }

    private function resolveDetailEvent(?Event $event): ?Event
    {
        if ($event instanceof Event) {
            return $event;
        }

        $candidateIdentifiers = [];
        if ($this->request->hasArgument('event')) {
            $candidateIdentifiers[] = $this->request->getArgument('event');
        }

        foreach (['tx_menscircle_eventdetail', 'tx_menscircle_event'] as $pluginNamespace) {
            $pluginArguments = $this->resolveNamespacedPluginArguments($pluginNamespace);
            if (\is_array($pluginArguments) && \array_key_exists('event', $pluginArguments)) {
                $candidateIdentifiers[] = $pluginArguments['event'];
            }
        }

        if (\array_key_exists('event', $this->settings)) {
            $candidateIdentifiers[] = $this->settings['event'];
        }

        foreach ($candidateIdentifiers as $candidateIdentifier) {
            $resolved = $this->resolveEventByIdentifier($candidateIdentifier);
            if ($resolved instanceof Event) {
                return $resolved;
            }
        }

        return $this->eventRepository->findNextEvent();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolveNamespacedPluginArguments(string $pluginNamespace): ?array
    {
        $queryParams = $this->request->getQueryParams();
        if (isset($queryParams[$pluginNamespace]) && \is_array($queryParams[$pluginNamespace])) {
            return $queryParams[$pluginNamespace];
        }

        $parsedBody = $this->request->getParsedBody();
        if (\is_array($parsedBody) && isset($parsedBody[$pluginNamespace]) && \is_array($parsedBody[$pluginNamespace])) {
            return $parsedBody[$pluginNamespace];
        }

        return null;
    }

    private function resolveEventByIdentifier(mixed $identifier): ?Event
    {
        if ($identifier instanceof Event) {
            return $identifier;
        }

        if (\is_array($identifier)) {
            if (\array_key_exists('__identity', $identifier)) {
                return $this->resolveEventByIdentifier($identifier['__identity']);
            }

            return null;
        }

        if (!\is_scalar($identifier)) {
            return null;
        }

        $value = trim((string)$identifier);
        if ($value === '') {
            return null;
        }

        if (ctype_digit($value)) {
            /** @var Event|null $event */
            $event = $this->eventRepository->findByUid((int)$value);

            return $event;
        }

        if (preg_match('/^tx_menscircle_domain_model_event_(\d+)$/', $value, $matches) === 1) {
            /** @var Event|null $event */
            $event = $this->eventRepository->findByUid((int)$matches[1]);

            return $event;
        }

        return $this->eventRepository->findOneBySlug($value);
    }

    private function renderEventDetail(Event $event): ResponseInterface
    {
        if (! $event->isPublished()) {
            $this->addFlashMessage('Dieser Termin ist nicht öffentlich.', '', ContextualFeedbackSeverity::WARNING);

            return $this->redirectToEventOverview();
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

    private function redirectToEventOverview(): ResponseInterface
    {
        $eventOverviewPath = $this->normalizeOverviewPath((string)($this->settings['eventOverviewPath'] ?? '/event'));

        return $this->redirectToUri($eventOverviewPath);
    }

    private function normalizeOverviewPath(string $path): string
    {
        $normalizedPath = trim($path);
        if ($normalizedPath === '') {
            return '/event';
        }

        return str_starts_with($normalizedPath, '/') ? $normalizedPath : '/' . $normalizedPath;
    }

    /**
     * @param iterable<Event> $events
     * @return list<array{event: Event, availableSpots: int}>
     */
    private function buildEventSummaries(iterable $events): array
    {
        $eventSummaries = [];
        foreach ($events as $event) {
            $registrations = $this->registrationRepository->countActiveByEvent($event);
            $eventSummaries[] = [
                'event' => $event,
                'availableSpots' => max(0, $event->getMaxParticipants() - $registrations),
            ];
        }

        return $eventSummaries;
    }

    private function isRegistrationInputValid(
        string $firstName,
        string $lastName,
        string $email,
        bool $privacy,
    ): bool {
        if ($firstName === '' || $lastName === '' || $email === '') {
            return false;
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        return $privacy;
    }

    private function redirectToEventDetailWithMessage(
        Event $event,
        string $message,
        ContextualFeedbackSeverity $severity,
    ): ResponseInterface {
        $this->addFlashMessage($message, '', $severity);

        return $this->redirect('detail', null, null, ['event' => $event->getUid()]);
    }

    private function upsertParticipant(
        string $email,
        string $firstName,
        string $lastName,
        string $phoneNumber,
    ): Participant {
        $participant = $this->participantRepository->findOneByEmail($email);
        if (! $participant instanceof Participant) {
            $participant = new Participant();
            $participant->setEmail($email);
        }

        $participant->setFirstName($firstName);
        $participant->setLastName($lastName);
        $participant->setPhone($phoneNumber);

        if ((int)$participant->getUid() > 0) {
            $this->participantRepository->update($participant);
        } else {
            $this->participantRepository->add($participant);
        }

        return $participant;
    }

    private function createRegistration(Event $event, Participant $participant): Registration
    {
        $registration = new Registration();
        $registration->setEvent($event);
        $registration->setParticipant($participant);
        $registration->setStatusEnum(RegistrationStatus::Registered);
        $registration->setRegisteredAt(new DateTime());

        return $registration;
    }

    private function activateNewsletterSubscription(Participant $participant): void
    {
        $subscription = $this->newsletterSubscriptionRepository->findOneByParticipant($participant);
        if (! $subscription instanceof NewsletterSubscription) {
            $subscription = new NewsletterSubscription();
            $subscription->setParticipant($participant);
            $subscription->setToken(bin2hex(random_bytes(32)));
            $subscription->setSubscribedAt(new DateTime());
            $this->newsletterSubscriptionRepository->add($subscription);

            return;
        }

        if ($subscription->getToken() === '') {
            $subscription->setToken(bin2hex(random_bytes(32)));
        }
        $subscription->setSubscribedAt(new DateTime());
        $subscription->setUnsubscribedAt(null);
        $this->newsletterSubscriptionRepository->update($subscription);
    }

    private function dispatchRegistrationNotifications(Registration $registration, Participant $participant): void
    {
        $notificationSettings = \is_array($this->settings) ? $this->settings : [];
        $this->messageBus->dispatch(new SendEventMailMessage(
            registrationUid: (int)$registration->getUid(),
            type: SendEventMailMessage::TYPE_REGISTRATION_CONFIRMATION,
            settings: $notificationSettings,
        ));

        if ($participant->getPhone() === '') {
            return;
        }

        $this->messageBus->dispatch(new SendEventSmsMessage(
            registrationUid: (int)$registration->getUid(),
            type: SendEventSmsMessage::TYPE_REGISTRATION_CONFIRMATION,
            settings: $notificationSettings,
        ));
    }
}
