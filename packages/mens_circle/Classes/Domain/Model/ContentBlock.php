<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Domain\Model;

use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class ContentBlock extends AbstractEntity
{
    protected string $type = 'text';
    protected string $title = '';
    protected string $content = '';
    protected ?FileReference $image = null;
    protected string $ctaText = '';
    protected string $ctaUrl = '';
    protected int $foreignId = 0;
    protected string $foreignTable = '';

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
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

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getImage(): ?FileReference
    {
        return $this->image;
    }

    public function setImage(?FileReference $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getCtaText(): string
    {
        return $this->ctaText;
    }

    public function setCtaText(string $ctaText): self
    {
        $this->ctaText = $ctaText;

        return $this;
    }

    public function getCtaUrl(): string
    {
        return $this->ctaUrl;
    }

    public function setCtaUrl(string $ctaUrl): self
    {
        $this->ctaUrl = $ctaUrl;

        return $this;
    }

    public function getForeignId(): int
    {
        return $this->foreignId;
    }

    public function getForeignTable(): string
    {
        return $this->foreignTable;
    }
}
