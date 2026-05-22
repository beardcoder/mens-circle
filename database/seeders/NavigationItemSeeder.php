<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\NavigationCondition;
use App\Enums\NavigationLocation;
use App\Models\NavigationItem;
use Illuminate\Database\Seeder;

class NavigationItemSeeder extends Seeder
{
    public function run(): void
    {
        /** @var array<int, array<string, mixed>> $items */
        $items = [
            // Header
            ['location' => NavigationLocation::Header, 'label' => 'Über', 'url' => '#ueber', 'umami_event_target' => 'ueber', 'sort' => 10],
            ['location' => NavigationLocation::Header, 'label' => 'Die Reise', 'url' => '#reise', 'umami_event_target' => 'reise', 'sort' => 20],
            ['location' => NavigationLocation::Header, 'label' => 'Fragen', 'url' => '#faq', 'umami_event_target' => 'faq', 'sort' => 30],
            ['location' => NavigationLocation::Header, 'label' => 'Atemübung', 'url' => '/atemuebung', 'umami_event_target' => 'atemuebung', 'sort' => 40],
            [
                'location' => NavigationLocation::Header,
                'label' => 'Nächster Termin',
                'url' => '',
                'condition' => NavigationCondition::NextEvent,
                'is_cta' => true,
                'umami_event_target' => 'go-to-event',
                'sort' => 50,
            ],

            // Footer primary
            ['location' => NavigationLocation::FooterPrimary, 'label' => 'Über uns', 'url' => '#ueber', 'umami_event_target' => 'ueber', 'sort' => 10],
            ['location' => NavigationLocation::FooterPrimary, 'label' => 'Die Reise', 'url' => '#reise', 'umami_event_target' => 'reise', 'sort' => 20],
            ['location' => NavigationLocation::FooterPrimary, 'label' => 'FAQ', 'url' => '#faq', 'umami_event_target' => 'faq', 'sort' => 30],
            ['location' => NavigationLocation::FooterPrimary, 'label' => 'Atemübung', 'url' => '/atemuebung', 'umami_event_target' => 'atemuebung', 'sort' => 40],
            [
                'location' => NavigationLocation::FooterPrimary,
                'label' => 'Nächster Termin',
                'url' => '',
                'condition' => NavigationCondition::NextEvent,
                'umami_event_target' => 'event',
                'sort' => 50,
            ],

            // Footer contact
            ['location' => NavigationLocation::FooterContact, 'label' => 'Newsletter', 'url' => '#newsletter', 'umami_event_target' => 'newsletter', 'sort' => 10],

            // Footer legal
            ['location' => NavigationLocation::FooterLegal, 'label' => 'Impressum', 'url' => '/impressum', 'umami_event_target' => 'impressum', 'sort' => 10],
            ['location' => NavigationLocation::FooterLegal, 'label' => 'Datenschutz', 'url' => '/datenschutz', 'umami_event_target' => 'datenschutz', 'sort' => 20],
        ];

        foreach ($items as $item) {
            NavigationItem::query()->updateOrCreate(
                ['location' => $item['location'], 'label' => $item['label']],
                $item,
            );
        }
    }
}
