<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Domain\Model;

use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class Event extends AbstractEntity
{
    protected string $title = '';
    protected string $slug = '';
    protected string $description = '';
    protected string $location = '';
    protected ?\DateTimeInterface $eventDate = null;
    protected ?\DateTimeInterface $eventEndDate = null;
    protected int $maxParticipants = 0;
    protected bool $isPublished = false;
    protected ?FileReference $eventImage = null;

    /**
     * @var ObjectStorage<Registration>
     */
    protected ObjectStorage $registrations;

    public function __construct()
    {
        $this->registrations = new ObjectStorage();
    }

    public function isFull(): bool
    {
        if ($this->maxParticipants === 0) {
            return false;
        }

        return $this->getActiveRegistrationsCount() >= $this->maxParticipants;
    }

    public function isPast(): bool
    {
        return $this->eventDate !== null && $this->eventDate < new \DateTime();
    }

    public function getActiveRegistrationsCount(): int
    {
        return $this->registrations->count();
    }

    public function getRemainingSpots(): int
    {
        if ($this->maxParticipants === 0) {
            return PHP_INT_MAX;
        }

        return max(0, $this->maxParticipants - $this->getActiveRegistrationsCount());
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getEventDate(): ?\DateTimeInterface
    {
        return $this->eventDate;
    }

    public function setEventDate(?\DateTimeInterface $eventDate): self
    {
        $this->eventDate = $eventDate;

        return $this;
    }

    public function getEventEndDate(): ?\DateTimeInterface
    {
        return $this->eventEndDate;
    }

    public function setEventEndDate(?\DateTimeInterface $eventEndDate): self
    {
        $this->eventEndDate = $eventEndDate;

        return $this;
    }

    public function getMaxParticipants(): int
    {
        return $this->maxParticipants;
    }

    public function setMaxParticipants(int $maxParticipants): self
    {
        $this->maxParticipants = $maxParticipants;

        return $this;
    }

    public function isPublished(): bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getEventImage(): ?FileReference
    {
        return $this->eventImage;
    }

    public function setEventImage(?FileReference $eventImage): self
    {
        $this->eventImage = $eventImage;

        return $this;
    }

    public function getRegistrations(): ObjectStorage
    {
        return $this->registrations;
    }
}
