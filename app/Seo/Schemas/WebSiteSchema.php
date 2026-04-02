<?php

declare(strict_types=1);

namespace App\Seo\Schemas;

use App\Settings\GeneralSettings;
use Spatie\SchemaOrg\Schema;

final readonly class WebSiteSchema
{
    public function __construct(private GeneralSettings $settings) {}

    public function toScript(): string
    {
        return Schema::webSite()
            ->setProperty('@id', url('/') . '#website')
            ->name($this->settings->site_name)
            ->url(url('/'))
            ->description($this->settings->site_description)
            ->inLanguage('de-DE')
            ->publisher(
                Schema::organization()->setProperty('@id', url('/') . '#organization'),
            )
            ->potentialAction(
                Schema::searchAction()
                    ->target(url('/') . '?s={search_term_string}')
                    ->setProperty('query-input', 'required name=search_term_string'),
            )
            ->toScript();
    }
}
