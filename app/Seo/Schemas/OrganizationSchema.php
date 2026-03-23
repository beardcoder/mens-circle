<?php

declare(strict_types=1);

namespace App\Seo\Schemas;

use App\Settings\GeneralSettings;
use Spatie\SchemaOrg\Schema;

final readonly class OrganizationSchema
{
    public function __construct(private GeneralSettings $settings) {}

    public function toScript(): string
    {
        $sameAs = [];
        foreach ($this->settings->social_links ?? [] as $link) {
            if (is_array($link) && isset($link['value']) && is_string($link['value'])) {
                $sameAs[] = $link['value'];
            }
        }

        $schema = Schema::organization()
            ->setProperty('@id', url('/') . '#organization')
            ->name($this->settings->site_name)
            ->url(url('/'))
            ->logo(
                Schema::imageObject()
                    ->url(asset('images/logo-color.png'))
                    ->setProperty('width', 512)
                    ->setProperty('height', 512)
            )
            ->description($this->settings->site_description)
            ->email($this->settings->contact_email)
            ->address(
                Schema::postalAddress()
                    ->addressLocality('Straubing')
                    ->addressRegion('Bayern')
                    ->addressCountry('DE')
            )
            ->areaServed(
                Schema::place()->name('Niederbayern')
            );

        if (count($sameAs) > 0) {
            $schema->sameAs($sameAs);
        }

        return $schema->toScript();
    }
}
