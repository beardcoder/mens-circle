<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Participant represents a person who can register for events and/or subscribe to the newsletter
 */
class Participant extends AbstractEntity
{
    protected string $firstName = '';
    protected string $lastName = '';
    protected string $email = '';
    protected string $phone = '';
    protected ?\DateTimeInterface $createdAt = null;

    /**
     * @var ObjectStorage<Registration>
     */
    protected ObjectStorage $eventRegistrations;

    /**
     * @var ObjectStorage<NewsletterSubscription>
     */
    protected ObjectStorage $newsletterSubscriptions;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->eventRegistrations = new ObjectStorage();
        $this->newsletterSubscriptions = new ObjectStorage();
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getEventRegistrations(): ObjectStorage
    {
        return $this->eventRegistrations;
    }

    public function addEventRegistration(Registration $registration): self
    {
        $this->eventRegistrations->attach($registration);

        return $this;
    }

    public function removeEventRegistration(Registration $registration): self
    {
        $this->eventRegistrations->detach($registration);

        return $this;
    }

    public function getNewsletterSubscriptions(): ObjectStorage
    {
        return $this->newsletterSubscriptions;
    }

    public function addNewsletterSubscription(NewsletterSubscription $subscription): self
    {
        $this->newsletterSubscriptions->attach($subscription);

        return $this;
    }

    public function removeNewsletterSubscription(NewsletterSubscription $subscription): self
    {
        $this->newsletterSubscriptions->detach($subscription);

        return $this;
    }

    public function hasActiveNewsletterSubscription(): bool
    {
        foreach ($this->newsletterSubscriptions as $subscription) {
            if ($subscription->isConfirmed()) {
                return true;
            }
        }

        return false;
    }
}
