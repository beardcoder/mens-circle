<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Tests\Unit\Domain;

use BeardCoder\MensCircle\Domain\Model\Event;
use BeardCoder\MensCircle\Domain\Model\Registration;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        $this->event = new Event();
    }

    public function testNewEventIsNotFull(): void
    {
        $this->event->setMaxParticipants(10);
        self::assertFalse($this->event->isFull());
    }

    public function testEventWithZeroMaxParticipantsIsNeverFull(): void
    {
        $this->event->setMaxParticipants(0);
        self::assertFalse($this->event->isFull());
    }

    public function testNewEventIsNotPast(): void
    {
        $this->event->setEventDate(new \DateTime('+1 day'));
        self::assertFalse($this->event->isPast());
    }

    public function testPastEventIsPast(): void
    {
        $this->event->setEventDate(new \DateTime('-1 day'));
        self::assertTrue($this->event->isPast());
    }

    public function testRemainingSpots_UnlimitedCapacity(): void
    {
        $this->event->setMaxParticipants(0);
        self::assertEquals(PHP_INT_MAX, $this->event->getRemainingSpots());
    }

    public function testRemainingSpotsCalculation(): void
    {
        $this->event->setMaxParticipants(5);
        self::assertEquals(5, $this->event->getRemainingSpots());
    }

    public function testTitleGetterSetter(): void
    {
        $this->event->setTitle('Test Event');
        self::assertEquals('Test Event', $this->event->getTitle());
    }

    public function testSlugGetterSetter(): void
    {
        $this->event->setSlug('test-event');
        self::assertEquals('test-event', $this->event->getSlug());
    }

    public function testLocationGetterSetter(): void
    {
        $this->event->setLocation('Straubing');
        self::assertEquals('Straubing', $this->event->getLocation());
    }

    public function testPublishedGetterSetter(): void
    {
        self::assertFalse($this->event->isPublished());
        $this->event->setIsPublished(true);
        self::assertTrue($this->event->isPublished());
    }

    public function testActiveRegistrationsCountInitiallyZero(): void
    {
        self::assertEquals(0, $this->event->getActiveRegistrationsCount());
    }
}
