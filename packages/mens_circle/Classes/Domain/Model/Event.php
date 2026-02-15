<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Domain\Model;

use DateTime;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

final class Event extends AbstractEntity
{
    public string $title = '';

    public string $slug = '';

    public string $teaser = '';

    public string $description = '';

    public ?DateTime $eventDate = null;

    public ?DateTime $startTime = null;

    public ?DateTime $endTime = null;

    public string $location = '';

    public string $street = '';

    public string $postalCode = '';

    public string $city = '';

    public string $locationDetails = '';

    public int $maxParticipants = 20 {
        set(int $value) => max(1, $value);
    }

    public string $costBasis = '';

    public bool $isPublished = false;

    public ?FileReference $image = null;

    /**
     * @var ObjectStorage<Registration>
     */
    public ObjectStorage $registrations;

    public function __construct()
    {
        $this->registrations = new ObjectStorage();
    }

    public function addRegistration(Registration $registration): void
    {
        $this->registrations->attach($registration);
    }

    public function removeRegistration(Registration $registration): void
    {
        $this->registrations->detach($registration);
    }

    public function isPast(): bool
    {
        if ($this->eventDate === null) {
            return false;
        }

        $endOfDay = (clone $this->eventDate)->setTime(23, 59, 59);

        return $endOfDay < new DateTime();
    }

    public function getFullAddress(): string
    {
        $parts = array_filter([
            trim($this->street),
            trim($this->postalCode . ' ' . $this->city),
        ]);

        if ($parts === []) {
            return trim($this->location);
        }

        return implode(', ', $parts);
    }

    public function generateIcalContent(string $siteDomain = 'mens-circle.local'): string
    {
        $eventDate = $this->eventDate instanceof DateTime ? clone $this->eventDate : new DateTime();
        $start = clone $eventDate;
        $end = clone $eventDate;

        if ($this->startTime instanceof DateTime) {
            $start->setTime((int)$this->startTime->format('H'), (int)$this->startTime->format('i'), 0);
        }

        if ($this->endTime instanceof DateTime) {
            $end->setTime((int)$this->endTime->format('H'), (int)$this->endTime->format('i'), 0);
        } else {
            $end = (clone $start)->modify('+2 hours');
        }

        $uid = \sprintf('%s@%s', $this->uid, $siteDomain);
        $description = preg_replace('/\s+/', ' ', strip_tags($this->description)) ?: '';

        return implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Mens Circle//TYPO3//DE',
            'BEGIN:VEVENT',
            'UID:' . $uid,
            'DTSTAMP:' . gmdate('Ymd\\THis\\Z'),
            'DTSTART:' . $start->format('Ymd\\THis'),
            'DTEND:' . $end->format('Ymd\\THis'),
            'SUMMARY:' . $this->title,
            'DESCRIPTION:' . $description,
            'LOCATION:' . $this->getFullAddress(),
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ]);
    }
}
