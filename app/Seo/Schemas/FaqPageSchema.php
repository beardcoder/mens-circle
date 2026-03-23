<?php

declare(strict_types=1);

namespace App\Seo\Schemas;

use App\Seo\Data\FaqItem;
use Spatie\SchemaOrg\Schema;

final readonly class FaqPageSchema
{
    /** @param list<FaqItem> $items */
    public function __construct(private array $items) {}

    public function toScript(): string
    {
        $questions = array_map(
            static fn(FaqItem $item): \Spatie\SchemaOrg\Question => Schema::question()
                ->name($item->question)
                ->acceptedAnswer(
                    Schema::answer()->text(strip_tags($item->answer))
                ),
            $this->items,
        );

        return Schema::fAQPage()
            ->mainEntity($questions)
            ->toScript();
    }
}
