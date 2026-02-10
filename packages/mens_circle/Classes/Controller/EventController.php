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
        mixed $event = null,
        string $firstName = '',
        string $lastName = '',
        string $email = '',
        string $phoneNumber = '',
        bool $privacy = false,
        bool $newsletterOptIn = false
    ): ResponseInterface
    {
        $resolvedEvent = $this->resolveEventByIdentifier($event);
        if (! $resolvedEvent instanceof Event) {
            $this->addFlashMessage('Ungültiger Termin.', '', ContextualFeedbackSeverity::ERROR);

            return $this->redirectToEventOverview();
        }

        $firstName = trim($firstName);
        $lastName = trim($lastName);
        $email = strtolower(trim($email));
        $phoneNumber = trim($phoneNumber);

        if ($firstName === '' || $lastName === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || ! $privacy) {
            $this->addFlashMessage('Bitte fülle alle Pflichtfelder korrekt aus.', '', ContextualFeedbackSeverity::ERROR);

            return $this->redirect('detail', null, null, ['event' => $resolvedEvent->getUid()]);
        }

        if (! $resolvedEvent->isPublished()) {
            $this->addFlashMessage('Dieser Termin ist nicht verfügbar.', '', ContextualFeedbackSeverity::ERROR);

            return $this->redirect('detail', null, null, ['event' => $resolvedEvent->getUid()]);
        }

        if ($resolvedEvent->isPast()) {
            $this->addFlashMessage('Dieser Termin liegt bereits in der Vergangenheit.', '', ContextualFeedbackSeverity::ERROR);

            return $this->redirect('detail', null, null, ['event' => $resolvedEvent->getUid()]);
        }

        $activeRegistrations = $this->registrationRepository->countActiveByEvent($resolvedEvent);
        if ($activeRegistrations >= $resolvedEvent->getMaxParticipants()) {
            $this->addFlashMessage('Dieser Termin ist leider bereits ausgebucht.', '', ContextualFeedbackSeverity::ERROR);

            return $this->redirect('detail', null, null, ['event' => $resolvedEvent->getUid()]);
        }

        $participant = $this->participantRepository->findOneByEmail($email);
        if (! $participant instanceof Participant) {
            $participant = new Participant();
            $participant->setEmail($email);
            $participant->setFirstName($firstName);
            $participant->setLastName($lastName);
            $participant->setPhone($phoneNumber);
            $this->participantRepository->add($participant);
        } else {
            $participant->setFirstName($firstName);
            $participant->setLastName($lastName);
            $participant->setPhone($phoneNumber);
            $this->participantRepository->update($participant);
        }

        $existingRegistration = $this->registrationRepository->findActiveByEventAndParticipant($resolvedEvent, $participant);
        if ($existingRegistration instanceof Registration) {
            $this->addFlashMessage('Du bist bereits für diesen Termin angemeldet.', '', ContextualFeedbackSeverity::WARNING);

            return $this->redirect('detail', null, null, ['event' => $resolvedEvent->getUid()]);
        }

        $registration = new Registration();
        $registration->setEvent($resolvedEvent);
        $registration->setParticipant($participant);
        $registration->setStatusEnum(RegistrationStatus::Registered);
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

        return $this->redirect('registerSuccess', null, null, ['event' => $resolvedEvent->getUid()]);
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

    private function resolveDetailEvent(?Event $event): ?Event
    {
        if ($event instanceof Event) {
            return $event;
        }

        if ($this->request->hasArgument('event')) {
            $resolved = $this->resolveEventByIdentifier($this->request->getArgument('event'));
            if ($resolved instanceof Event) {
                return $resolved;
            }
        }

        $pluginArgumentNamespaces = [
            'tx_menscircle_eventdetail',
            'tx_menscircle_event',
        ];
        foreach ($pluginArgumentNamespaces as $pluginNamespace) {
            $pluginArguments = $this->resolveNamespacedPluginArguments($pluginNamespace);
            if (is_array($pluginArguments) && array_key_exists('event', $pluginArguments)) {
                $resolved = $this->resolveEventByIdentifier($pluginArguments['event']);
                if ($resolved instanceof Event) {
                    return $resolved;
                }
            }
        }

        if (array_key_exists('event', $this->settings)) {
            $resolved = $this->resolveEventByIdentifier($this->settings['event']);
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
        if (isset($queryParams[$pluginNamespace]) && is_array($queryParams[$pluginNamespace])) {
            return $queryParams[$pluginNamespace];
        }

        $parsedBody = $this->request->getParsedBody();
        if (is_array($parsedBody) && isset($parsedBody[$pluginNamespace]) && is_array($parsedBody[$pluginNamespace])) {
            return $parsedBody[$pluginNamespace];
        }

        return null;
    }

    private function resolveEventByIdentifier(mixed $identifier): ?Event
    {
        if ($identifier instanceof Event) {
            return $identifier;
        }

        if (is_array($identifier)) {
            if (array_key_exists('__identity', $identifier)) {
                return $this->resolveEventByIdentifier($identifier['__identity']);
            }

            return null;
        }

        if (!is_scalar($identifier)) {
            return null;
        }

        $value = trim((string) $identifier);
        if ($value === '') {
            return null;
        }

        if (ctype_digit($value)) {
            /** @var Event|null $event */
            $event = $this->eventRepository->findByUid((int) $value);

            return $event;
        }

        if (preg_match('/^tx_menscircle_domain_model_event_(\d+)$/', $value, $matches) === 1) {
            /** @var Event|null $event */
            $event = $this->eventRepository->findByUid((int) $matches[1]);

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
        $eventOverviewPath = trim((string) ($this->settings['eventOverviewPath'] ?? '/event'));
        if ($eventOverviewPath === '') {
            $eventOverviewPath = '/event';
        }
        if (!str_starts_with($eventOverviewPath, '/')) {
            $eventOverviewPath = '/' . $eventOverviewPath;
        }

        return $this->redirectToUri($eventOverviewPath);
    }
}
