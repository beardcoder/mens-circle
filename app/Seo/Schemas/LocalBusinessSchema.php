<?php

declare(strict_types=1);

namespace App\Seo\Schemas;

use App\Settings\GeneralSettings;
use Spatie\SchemaOrg\Schema;

final readonly class LocalBusinessSchema
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

        $schema = Schema::localBusiness()
            ->setProperty('@id', url('/') . '#organization')
            ->name($this->settings->site_name)
            ->description($this->settings->site_description)
            ->url(url('/'))
            ->logo(
                Schema::imageObject()
                    ->url(asset('images/logo-color.png'))
                    ->setProperty('width', 512)
                    ->setProperty('height', 512),
            )
            ->image(asset('images/logo-color.png'))
            ->email($this->settings->contact_email)
            ->address(
                Schema::postalAddress()
                    ->addressLocality('Straubing')
                    ->addressRegion('Bayern')
                    ->postalCode('94315')
                    ->addressCountry('DE'),
            )
            ->geo(
                Schema::geoCoordinates()
                    ->latitude(48.8777)
                    ->longitude(12.5731),
            )
            ->areaServed(
                Schema::geoCircle()
                    ->geoMidpoint(
                        Schema::geoCoordinates()
                            ->latitude(48.8777)
                            ->longitude(12.5731),
                    )
                    ->geoRadius('50000'),
            )
            ->priceRange('€')
            ->openingHoursSpecification(
                Schema::openingHoursSpecification()
                    ->setProperty('dayOfWeek', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'])
                    ->setProperty('opens', '09:00')
                    ->setProperty('closes', '18:00'),
            );

        if ($this->settings->contact_phone) {
            $schema->telephone($this->settings->contact_phone);
        }

        if ($sameAs !== []) {
            $schema->sameAs($sameAs);
        }

        return $schema->toScript();
    }
}
