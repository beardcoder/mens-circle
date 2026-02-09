<?php

declare(strict_types=1);

namespace MarkusSommer\MensCircle\Domain\Model;

use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

final class Event extends AbstractEntity
{
    protected string $title = '';

    protected string $slug = '';

    protected string $teaser = '';

    protected string $description = '';

    protected ?\DateTime $eventDate = null;

    protected ?\DateTime $startTime = null;

    protected ?\DateTime $endTime = null;

    protected string $location = '';

    protected string $street = '';

    protected string $postalCode = '';

    protected string $city = '';

    protected string $locationDetails = '';

    protected int $maxParticipants = 20;

    protected string $costBasis = '';

    protected bool $isPublished = false;

    protected ?FileReference $image = null;

    /**
     * @var ObjectStorage<Registration>
     */
    protected ObjectStorage $registrations;

    public function __construct()
    {
        $this->registrations = new ObjectStorage();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getTeaser(): string
    {
        return $this->teaser;
    }

    public function setTeaser(string $teaser): void
    {
        $this->teaser = $teaser;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getEventDate(): ?\DateTime
    {
        return $this->eventDate;
    }

    public function setEventDate(?\DateTime $eventDate): void
    {
        $this->eventDate = $eventDate;
    }

    public function getStartTime(): ?\DateTime
    {
        return $this->startTime;
    }

    public function setStartTime(?\DateTime $startTime): void
    {
        $this->startTime = $startTime;
    }

    public function getEndTime(): ?\DateTime
    {
        return $this->endTime;
    }

    public function setEndTime(?\DateTime $endTime): void
    {
        $this->endTime = $endTime;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function setStreet(string $street): void
    {
        $this->street = $street;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function getLocationDetails(): string
    {
        return $this->locationDetails;
    }

    public function setLocationDetails(string $locationDetails): void
    {
        $this->locationDetails = $locationDetails;
    }

    public function getMaxParticipants(): int
    {
        return $this->maxParticipants;
    }

    public function setMaxParticipants(int $maxParticipants): void
    {
        $this->maxParticipants = max(1, $maxParticipants);
    }

    public function getCostBasis(): string
    {
        return $this->costBasis;
    }

    public function setCostBasis(string $costBasis): void
    {
        $this->costBasis = $costBasis;
    }

    public function isPublished(): bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): void
    {
        $this->isPublished = $isPublished;
    }

    public function getImage(): ?FileReference
    {
        return $this->image;
    }

    public function setImage(?FileReference $image): void
    {
        $this->image = $image;
    }

    /**
     * @return ObjectStorage<Registration>
     */
    public function getRegistrations(): ObjectStorage
    {
        return $this->registrations;
    }

    /**
     * @param ObjectStorage<Registration> $registrations
     */
    public function setRegistrations(ObjectStorage $registrations): void
    {
        $this->registrations = $registrations;
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

        return $endOfDay < new \DateTime();
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
        $eventDate = $this->eventDate instanceof \DateTime ? clone $this->eventDate : new \DateTime();
        $start = clone $eventDate;
        $end = clone $eventDate;

        if ($this->startTime instanceof \DateTime) {
            $start->setTime((int) $this->startTime->format('H'), (int) $this->startTime->format('i'), 0);
        }

        if ($this->endTime instanceof \DateTime) {
            $end->setTime((int) $this->endTime->format('H'), (int) $this->endTime->format('i'), 0);
        } else {
            $end = (clone $start)->modify('+2 hours');
        }

        $uid = sprintf('%s@%s', $this->uid, $siteDomain);
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
