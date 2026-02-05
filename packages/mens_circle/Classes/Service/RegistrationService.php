<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Service;

use BeardCoder\MensCircle\Domain\Model\Event;
use BeardCoder\MensCircle\Domain\Model\Registration;
use BeardCoder\MensCircle\Domain\Repository\ParticipantRepository;
use BeardCoder\MensCircle\Domain\Repository\RegistrationRepository;
use BeardCoder\MensCircle\Event\RegistrationCreatedEvent;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

final class RegistrationService
{
    public function __construct(
        private readonly ParticipantRepository $participantRepository,
        private readonly RegistrationRepository $registrationRepository,
        private readonly PersistenceManagerInterface $persistenceManager,
        private readonly EventDispatcher $eventDispatcher,
    ) {}

    /**
     * Register a participant for an event.
     *
     * Expects pre-validated and sanitized data (e.g. from FormValidator).
     *
     * @param array<string, mixed> $data Validated form data with keys: firstName, lastName, email, phone, notes
     */
    public function register(Event $event, array $data): Registration
    {
        if ($event->isFull()) {
            throw new \RuntimeException('Diese Veranstaltung ist bereits ausgebucht.');
        }

        if ($event->isPast()) {
            throw new \RuntimeException('Diese Veranstaltung hat bereits stattgefunden.');
        }

        $firstName = (string) ($data['firstName'] ?? '');
        $lastName = (string) ($data['lastName'] ?? '');
        $email = (string) ($data['email'] ?? '');
        $phone = (string) ($data['phone'] ?? '');
        $notes = (string) ($data['notes'] ?? '');

        if (empty($firstName) || empty($lastName)) {
            throw new \RuntimeException('Vor- und Nachname sind erforderlich.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException('Bitte gib eine gültige E-Mail-Adresse ein.');
        }

        // Find or create participant
        $participant = $this->participantRepository->findOrCreateByEmail(
            $email,
            $firstName,
            $lastName,
            $phone,
        );

        // Create registration
        $registration = new Registration();
        $registration->setEvent($event);
        $registration->setParticipant($participant);
        $registration->setNotes($notes);
        $registration->setIsConfirmed(true); // Auto-confirm for now

        // Add registration to participant
        $participant->addEventRegistration($registration);

        $this->registrationRepository->add($registration);
        $this->participantRepository->update($participant);
        $this->persistenceManager->persistAll();

        $sendSms = !empty($phone) && $this->isSmsEnabled();
        $this->eventDispatcher->dispatch(
            new RegistrationCreatedEvent($registration, $sendSms),
        );

        return $registration;
    }

    private function isSmsEnabled(): bool
    {
        $settings = $this->getSettings();

        return ($settings['features']['enableSmsNotifications'] ?? false)
            && !empty($settings['sms']['apiKey']);
    }

    private function getSettings(): array
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class,
        )->get('mens_circle') ?? [];
    }
}
