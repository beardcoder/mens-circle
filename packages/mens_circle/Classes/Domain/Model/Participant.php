<?php

declare(strict_types=1);

namespace MarkusSommer\MensCircle\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

final class Participant extends AbstractEntity
{
    protected string $firstName = '';

    protected string $lastName = '';

    protected string $email = '';

    protected string $phone = '';

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = trim($firstName);
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = trim($lastName);
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = strtolower(trim($email));
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): void
    {
        $this->phone = trim($phone);
    }

    public function getFullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }
}
