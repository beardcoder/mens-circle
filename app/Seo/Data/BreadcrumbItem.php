<?php

declare(strict_types=1);

namespace App\Seo\Data;

final readonly class BreadcrumbItem
{
    public function __construct(
        public string $name,
        public string $url,
    ) {}
}
