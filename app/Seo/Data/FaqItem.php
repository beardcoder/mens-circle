<?php

declare(strict_types=1);

namespace App\Seo\Data;

final readonly class FaqItem
{
    public function __construct(
        public string $question,
        public string $answer,
    ) {}
}
