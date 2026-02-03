<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Tests\Unit\Domain;

use BeardCoder\MensCircle\Domain\Model\Participant;
use BeardCoder\MensCircle\Domain\Model\Registration;
use BeardCoder\MensCircle\Domain\Model\NewsletterSubscription;
use PHPUnit\Framework\TestCase;

class ParticipantTest extends TestCase
{
    private Participant $participant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->participant = new Participant();
    }

    public function testFirstNameGetterSetter(): void
    {
        $this->participant->setFirstName('Max');
        self::assertEquals('Max', $this->participant->getFirstName());
    }

    public function testLastNameGetterSetter(): void
    {
        $this->participant->setLastName('Mustermann');
        self::assertEquals('Mustermann', $this->participant->getLastName());
    }

    public function testGetFullName(): void
    {
        $this->participant->setFirstName('Max');
        $this->participant->setLastName('Mustermann');
        self::assertEquals('Max Mustermann', $this->participant->getFullName());
    }

    public function testEmailGetterSetter(): void
    {
        $email = 'max@example.com';
        $this->participant->setEmail($email);
        self::assertEquals($email, $this->participant->getEmail());
    }

    public function testPhoneGetterSetter(): void
    {
        $phone = '+49 123 456789';
        $this->participant->setPhone($phone);
        self::assertEquals($phone, $this->participant->getPhone());
    }

    public function testCreatedAtIsSetInConstructor(): void
    {
        self::assertInstanceOf(\DateTimeInterface::class, $this->participant->getCreatedAt());
    }

    public function testAddEventRegistration(): void
    {
        $registration = new Registration();
        $this->participant->addEventRegistration($registration);

        self::assertCount(1, $this->participant->getEventRegistrations());
        self::assertTrue($this->participant->getEventRegistrations()->contains($registration));
    }

    public function testRemoveEventRegistration(): void
    {
        $registration = new Registration();
        $this->participant->addEventRegistration($registration);
        $this->participant->removeEventRegistration($registration);

        self::assertCount(0, $this->participant->getEventRegistrations());
    }

    public function testAddNewsletterSubscription(): void
    {
        $subscription = new NewsletterSubscription();
        $this->participant->addNewsletterSubscription($subscription);

        self::assertCount(1, $this->participant->getNewsletterSubscriptions());
        self::assertTrue($this->participant->getNewsletterSubscriptions()->contains($subscription));
    }

    public function testHasActiveNewsletterSubscription_NoSubscriptions(): void
    {
        self::assertFalse($this->participant->hasActiveNewsletterSubscription());
    }

    public function testHasActiveNewsletterSubscription_WithConfirmedSubscription(): void
    {
        $subscription = new NewsletterSubscription();
        $subscription->confirm();
        $this->participant->addNewsletterSubscription($subscription);

        self::assertTrue($this->participant->hasActiveNewsletterSubscription());
    }

    public function testHasActiveNewsletterSubscription_WithUnconfirmedSubscription(): void
    {
        $subscription = new NewsletterSubscription();
        $this->participant->addNewsletterSubscription($subscription);

        self::assertFalse($this->participant->hasActiveNewsletterSubscription());
    }
}

