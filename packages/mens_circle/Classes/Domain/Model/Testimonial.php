<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

final class Testimonial extends AbstractEntity
{
    protected string $quote = '';

    protected string $authorName = '';

    protected string $email = '';

    protected string $role = '';

    protected bool $isPublished = false;

    protected ?\DateTime $publishedAt = null;

    protected int $sortOrder = 0;

    public function getQuote(): string
    {
        return $this->quote;
    }

    public function setQuote(string $quote): void
    {
        $this->quote = trim($quote);
    }

    public function getAuthorName(): string
    {
        return $this->authorName;
    }

    public function setAuthorName(string $authorName): void
    {
        $this->authorName = trim($authorName);
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = strtolower(trim($email));
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): void
    {
        $this->role = trim($role);
    }

    public function isPublished(): bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): void
    {
        $this->isPublished = $isPublished;
    }

    public function getPublishedAt(): ?\DateTime
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTime $publishedAt): void
    {
        $this->publishedAt = $publishedAt;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): void
    {
        $this->sortOrder = max(0, $sortOrder);
    }
}
