<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Tests\Unit\Domain;

use BeardCoder\MensCircle\Domain\Model\Event;
use BeardCoder\MensCircle\Domain\Model\Participant;
use BeardCoder\MensCircle\Domain\Model\Registration;
use PHPUnit\Framework\TestCase;

class RegistrationTest extends TestCase
{
    private Registration $registration;

    protected function setUp(): void
    {
        parent::setUp();
        $this->registration = new Registration();
    }

    public function testNewRegistrationHasConfirmationToken(): void
    {
        self::assertNotEmpty($this->registration->getConfirmationToken());
        self::assertGreaterThanOrEqual(64, \strlen($this->registration->getConfirmationToken()));
    }

    public function testNewRegistrationHasCreatedAt(): void
    {
        self::assertNotNull($this->registration->getCreatedAt());
        self::assertInstanceOf(\DateTimeInterface::class, $this->registration->getCreatedAt());
    }

    public function testGetFullName(): void
    {
        $participant = new Participant();
        $participant->setFirstName('Hans');
        $participant->setLastName('Müller');
        $this->registration->setParticipant($participant);

        self::assertEquals('Hans Müller', $this->registration->getFullName());
    }

    public function testGetFullNameWithOnlyFirstName(): void
    {
        $participant = new Participant();
        $participant->setFirstName('Hans');
        $this->registration->setParticipant($participant);

        self::assertEquals('Hans', $this->registration->getFullName());
    }

    public function testConfirmedGetterSetter(): void
    {
        self::assertFalse($this->registration->isConfirmed());
        $this->registration->setIsConfirmed(true);
        self::assertTrue($this->registration->isConfirmed());
    }

    public function testEventGetterSetter(): void
    {
        $event = new Event();
        $event->setTitle('Test Event');
        $this->registration->setEvent($event);
        self::assertNotNull($this->registration->getEvent());
        self::assertEquals('Test Event', $this->registration->getEvent()->getTitle());
    }
}
