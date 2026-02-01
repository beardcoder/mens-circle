<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Service;

use BeardCoder\MensCircle\Domain\Model\Event;
use BeardCoder\MensCircle\Domain\Model\Registration;
use BeardCoder\MensCircle\Domain\Repository\RegistrationRepository;
use BeardCoder\MensCircle\Event\RegistrationCreatedEvent;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

final class RegistrationService
{
    public function __construct(
        private readonly RegistrationRepository $registrationRepository,
        private readonly PersistenceManagerInterface $persistenceManager,
        private readonly EventDispatcher $eventDispatcher,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public function register(Event $event, array $data): Registration
    {
        if ($event->isFull()) {
            throw new \RuntimeException('Diese Veranstaltung ist bereits ausgebucht.');
        }

        if ($event->isPast()) {
            throw new \RuntimeException('Diese Veranstaltung hat bereits stattgefunden.');
        }

        $firstName = htmlspecialchars((string) ($data['firstName'] ?? ''));
        $lastName = htmlspecialchars((string) ($data['lastName'] ?? ''));
        $email = filter_var((string) ($data['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $phone = (string) ($data['phone'] ?? '');
        $notes = htmlspecialchars((string) ($data['notes'] ?? ''));

        if (empty($firstName) || empty($lastName)) {
            throw new \RuntimeException('Vor- und Nachname sind erforderlich.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException('Bitte gib eine gültige E-Mail-Adresse ein.');
        }

        $registration = new Registration();
        $registration->setEvent($event);
        $registration->setFirstName($firstName);
        $registration->setLastName($lastName);
        $registration->setEmail($email);
        $registration->setPhone($phone);
        $registration->setNotes($notes);
        $registration->setIsConfirmed(true);

        $this->registrationRepository->add($registration);
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
