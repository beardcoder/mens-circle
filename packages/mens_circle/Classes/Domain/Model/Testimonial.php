<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Domain\Model;

use DateTime;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

final class Testimonial extends AbstractEntity
{
    public string $quote = '' {
        set(string $value) => trim($value);
    }

    public string $authorName = '' {
        set(string $value) => trim($value);
    }

    public string $email = '' {
        set(string $value) => strtolower(trim($value));
    }

    public string $role = '' {
        set(string $value) => trim($value);
    }

    public bool $isPublished = false;

    public ?DateTime $publishedAt = null;

    public int $sortOrder = 0 {
        set(int $value) => max(0, $value);
    }
}
