<?php

declare(strict_types=1);

namespace App\Seo\Schemas;

use App\Seo\Data\BreadcrumbItem;
use Spatie\SchemaOrg\Schema;

final readonly class BreadcrumbSchema
{
    /** @param list<BreadcrumbItem> $items */
    public function __construct(private array $items) {}

    public function toScript(): string
    {
        $listItems = [];

        foreach ($this->items as $index => $item) {
            $listItems[] = Schema::listItem()
                ->position($index + 1)
                ->name($item->name)
                ->setProperty('item', $item->url);
        }

        return Schema::breadcrumbList()
            ->itemListElement($listItems)
            ->toScript();
    }
}
