<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Tests\Unit\Service;

use BeardCoder\MensCircle\Domain\Model\Event;
use BeardCoder\MensCircle\Domain\Model\Participant;
use BeardCoder\MensCircle\Domain\Model\Registration;
use BeardCoder\MensCircle\Domain\Repository\ParticipantRepository;
use BeardCoder\MensCircle\Domain\Repository\RegistrationRepository;
use BeardCoder\MensCircle\Service\RegistrationService;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

class RegistrationServiceTest extends TestCase
{
    private RegistrationService $registrationService;
    private ParticipantRepository $participantRepository;
    private RegistrationRepository $registrationRepository;
    private PersistenceManagerInterface $persistenceManager;
    private EventDispatcher $eventDispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->participantRepository = $this->createMock(ParticipantRepository::class);
        $this->registrationRepository = $this->createMock(RegistrationRepository::class);
        $this->persistenceManager = $this->createMock(PersistenceManagerInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcher::class);

        $this->registrationService = new RegistrationService(
            $this->participantRepository,
            $this->registrationRepository,
            $this->persistenceManager,
            $this->eventDispatcher
        );
    }

    public function testRegisterThrowsExceptionWhenEventIsFull(): void
    {
        $event = new Event();
        $event->setMaxParticipants(1);
        $event->setEventDate(new \DateTime('+1 day'));

        // Simulate that event is already full
        $registration = new Registration();
        $event->getRegistrations()->attach($registration);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Diese Veranstaltung ist bereits ausgebucht.');

        $this->registrationService->register($event, [
            'firstName' => 'Max',
            'lastName' => 'Mustermann',
            'email' => 'max@example.com',
        ]);
    }

    public function testRegisterThrowsExceptionWhenEventIsPast(): void
    {
        $event = new Event();
        $event->setEventDate(new \DateTime('-1 day'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Diese Veranstaltung hat bereits stattgefunden.');

        $this->registrationService->register($event, [
            'firstName' => 'Max',
            'lastName' => 'Mustermann',
            'email' => 'max@example.com',
        ]);
    }

    public function testRegisterThrowsExceptionWhenFirstNameIsMissing(): void
    {
        $event = new Event();
        $event->setEventDate(new \DateTime('+1 day'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Vor- und Nachname sind erforderlich.');

        $this->registrationService->register($event, [
            'firstName' => '',
            'lastName' => 'Mustermann',
            'email' => 'max@example.com',
        ]);
    }

    public function testRegisterThrowsExceptionWhenEmailIsInvalid(): void
    {
        $event = new Event();
        $event->setEventDate(new \DateTime('+1 day'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Bitte gib eine gültige E-Mail-Adresse ein.');

        $this->registrationService->register($event, [
            'firstName' => 'Max',
            'lastName' => 'Mustermann',
            'email' => 'invalid-email',
        ]);
    }

    public function testRegisterCreatesRegistrationSuccessfully(): void
    {
        $event = new Event();
        $event->setEventDate(new \DateTime('+1 day'));
        $event->setMaxParticipants(10);

        $participant = new Participant();
        $participant->setEmail('max@example.com');
        $participant->setFirstName('Max');
        $participant->setLastName('Mustermann');

        $this->participantRepository
            ->expects(self::once())
            ->method('findOrCreateByEmail')
            ->with('max@example.com', 'Max', 'Mustermann', '')
            ->willReturn($participant);

        $this->registrationRepository
            ->expects(self::once())
            ->method('add');

        $this->participantRepository
            ->expects(self::once())
            ->method('update')
            ->with($participant);

        $this->persistenceManager
            ->expects(self::once())
            ->method('persistAll');

        $this->eventDispatcher
            ->expects(self::once())
            ->method('dispatch');

        $registration = $this->registrationService->register($event, [
            'firstName' => 'Max',
            'lastName' => 'Mustermann',
            'email' => 'max@example.com',
            'notes' => 'Test notes',
        ]);

        self::assertInstanceOf(Registration::class, $registration);
        self::assertSame($event, $registration->getEvent());
        self::assertSame($participant, $registration->getParticipant());
        self::assertTrue($registration->isConfirmed());
    }
}

