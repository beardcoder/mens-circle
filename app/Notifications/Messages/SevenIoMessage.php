<?php

declare(strict_types=1);

namespace App\Notifications\Messages;

final class SevenIoMessage
{
    public ?string $from = null;

    public static function create(string $content = ''): self
    {
        return new self($content);
    }

    public function __construct(public string $content = '') {}

    public function content(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function from(string $from): self
    {
        $this->from = $from;

        return $this;
    }
}
