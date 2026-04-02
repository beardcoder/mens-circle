<?php

declare(strict_types=1);

namespace App\Seo\Schemas;

use App\Settings\GeneralSettings;
use Spatie\SchemaOrg\Schema;

final readonly class WebPageSchema
{
    public function __construct(
        private string $title,
        private string $description,
        private ?string $url = null,
        private ?GeneralSettings $settings = null,
    ) {}

    public function toScript(): string
    {
        $schema = Schema::webPage()
            ->name($this->title)
            ->description($this->description)
            ->url($this->url ?? url()->current())
            ->inLanguage('de-DE')
            ->publisher(
                Schema::organization()->setProperty('@id', url('/') . '#organization'),
            );

        if ($this->settings instanceof GeneralSettings) {
            $schema->isPartOf(
                Schema::webSite()
                    ->setProperty('@id', url('/') . '#website')
                    ->name($this->settings->site_name)
                    ->url(url('/')),
            );
        }

        return $schema->toScript();
    }
}
