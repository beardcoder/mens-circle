<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

final class Participant extends AbstractEntity
{
    public string $firstName = '' {
        set(string $value) => trim($value);
    }

    public string $lastName = '' {
        set(string $value) => trim($value);
    }

    public string $email = '' {
        set(string $value) => strtolower(trim($value));
    }

    public string $phone = '' {
        set(string $value) => trim($value);
    }

    public function getFullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }
}
