<?php

declare(strict_types=1);

namespace App\Seo\Schemas;

use App\Models\Event;
use App\Settings\GeneralSettings;
use Illuminate\Support\Uri;
use Spatie\SchemaOrg\Schema;

final readonly class EventSchema
{
    public function __construct(
        private Event $event,
        private GeneralSettings $settings,
    ) {}

    public function toScript(): string
    {
        $startDate = $this->event->event_date->format('Y-m-d')
            . 'T' . $this->event->start_time->format('H:i')
            . ':00' . $this->event->event_date->format('P');

        $endDate = $this->event->event_date->format('Y-m-d')
            . 'T' . $this->event->end_time->format('H:i')
            . ':00' . $this->event->event_date->format('P');

        $availability = ($this->event->isPast || $this->event->isFull)
            ? 'https://schema.org/SoldOut'
            : 'https://schema.org/InStock';

        $address = Schema::postalAddress()
            ->addressLocality($this->event->city ?? 'Straubing')
            ->addressRegion('Bayern')
            ->addressCountry('DE');

        if ($this->event->street !== null && $this->event->street !== '') {
            $address = $address->streetAddress($this->event->street);
        }

        if ($this->event->postal_code !== null && $this->event->postal_code !== '') {
            $address = $address->postalCode($this->event->postal_code);
        }

        $place = Schema::place()
            ->name($this->event->location ?? 'Straubing')
            ->address($address);

        if ($this->event->hasCoordinates) {
            $place = $place->geo(
                Schema::geoCoordinates()
                    ->latitude((float) $this->event->latitude)
                    ->longitude((float) $this->event->longitude),
            );
        }

        $organizer = Schema::organization()
            ->setProperty('@id', (string) Uri::of(url('/'))->withFragment('organization'))
            ->name($this->settings->site_name)
            ->url(url('/'));

        return Schema::event()
            ->name($this->event->title)
            ->description(strip_tags($this->event->description ?? ''))
            ->image(
                Schema::imageObject()
                    ->url(asset('images/logo-color.png'))
                    ->setProperty('width', 512)
                    ->setProperty('height', 512),
            )
            ->setProperty('startDate', $startDate)
            ->setProperty('endDate', $endDate)
            ->setProperty('eventStatus', 'https://schema.org/EventScheduled')
            ->setProperty('eventAttendanceMode', 'https://schema.org/OfflineEventAttendanceMode')
            ->location($place)
            ->organizer($organizer)
            ->performer(
                Schema::organization()->setProperty('@id', (string) Uri::of(url('/'))->withFragment('organization')),
            )
            ->offers(
                Schema::offer()
                    ->url(route('event.show.slug', $this->event->slug))
                    ->price('0')
                    ->priceCurrency('EUR')
                    ->setProperty('availability', $availability)
                    ->setProperty('validFrom', now()->format('Y-m-d')),
            )
            ->maximumAttendeeCapacity($this->event->max_participants)
            ->remainingAttendeeCapacity(max(0, $this->event->availableSpots))
            ->inLanguage('de')
            ->toScript();
    }
}
