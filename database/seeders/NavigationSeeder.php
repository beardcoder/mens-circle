<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\NavigationType;
use App\Models\Navigation;
use App\Models\NavigationItem;
use Illuminate\Database\Seeder;

class NavigationSeeder extends Seeder
{
    public function run(): void
    {
        // Header Navigation
        $headerNav = Navigation::create([
            'name' => 'Hauptnavigation',
            'type' => NavigationType::Header,
            'is_active' => true,
        ]);

        $headerItems = [
            [
                'label' => 'Über',
                'route_name' => 'home',
                'anchor' => 'ueber',
                'order' => 1,
                'data_attributes' => [
                    'umami-event' => 'nav-click',
                    'umami-event-target' => 'ueber',
                ],
            ],
            [
                'label' => 'Die Reise',
                'route_name' => 'home',
                'anchor' => 'reise',
                'order' => 2,
                'data_attributes' => [
                    'umami-event' => 'nav-click',
                    'umami-event-target' => 'reise',
                ],
            ],
            [
                'label' => 'Fragen',
                'route_name' => 'home',
                'anchor' => 'faq',
                'order' => 3,
                'data_attributes' => [
                    'umami-event' => 'nav-click',
                    'umami-event-target' => 'faq',
                ],
            ],
            [
                'label' => 'Atemübung',
                'route_name' => 'breathing.show',
                'order' => 4,
                'data_attributes' => [
                    'umami-event' => 'nav-click',
                    'umami-event-target' => 'atemuebung',
                ],
            ],
        ];

        foreach ($headerItems as $item) {
            NavigationItem::create([
                'navigation_id' => $headerNav->id,
                ...$item,
                'is_active' => true,
            ]);
        }

        // Footer Navigation
        $footerNav = Navigation::create([
            'name' => 'Footer Navigation',
            'type' => NavigationType::Footer,
            'is_active' => true,
        ]);

        $footerItems = [
            [
                'label' => 'Über uns',
                'route_name' => 'home',
                'anchor' => 'ueber',
                'order' => 1,
                'data_attributes' => [
                    'umami-event' => 'footer-link',
                    'umami-event-target' => 'ueber',
                ],
            ],
            [
                'label' => 'Die Reise',
                'route_name' => 'home',
                'anchor' => 'reise',
                'order' => 2,
                'data_attributes' => [
                    'umami-event' => 'footer-link',
                    'umami-event-target' => 'reise',
                ],
            ],
            [
                'label' => 'FAQ',
                'route_name' => 'home',
                'anchor' => 'faq',
                'order' => 3,
                'data_attributes' => [
                    'umami-event' => 'footer-link',
                    'umami-event-target' => 'faq',
                ],
            ],
            [
                'label' => 'Atemübung',
                'route_name' => 'breathing.show',
                'order' => 4,
                'data_attributes' => [
                    'umami-event' => 'footer-link',
                    'umami-event-target' => 'atemuebung',
                ],
            ],
            [
                'label' => 'Newsletter',
                'route_name' => 'home',
                'anchor' => 'newsletter',
                'order' => 5,
                'data_attributes' => [
                    'umami-event' => 'footer-link',
                    'umami-event-target' => 'newsletter',
                ],
            ],
        ];

        foreach ($footerItems as $item) {
            NavigationItem::create([
                'navigation_id' => $footerNav->id,
                ...$item,
                'is_active' => true,
            ]);
        }

        // Legal Navigation
        $legalNav = Navigation::create([
            'name' => 'Rechtliches',
            'type' => NavigationType::Legal,
            'is_active' => true,
        ]);

        $legalItems = [
            [
                'label' => 'Impressum',
                'route_name' => 'page.show',
                'route_params' => ['slug' => 'impressum'],
                'order' => 1,
                'data_attributes' => [
                    'umami-event' => 'footer-link',
                    'umami-event-target' => 'impressum',
                ],
            ],
            [
                'label' => 'Datenschutz',
                'route_name' => 'page.show',
                'route_params' => ['slug' => 'datenschutz'],
                'order' => 2,
                'data_attributes' => [
                    'umami-event' => 'footer-link',
                    'umami-event-target' => 'datenschutz',
                ],
            ],
        ];

        foreach ($legalItems as $item) {
            NavigationItem::create([
                'navigation_id' => $legalNav->id,
                ...$item,
                'is_active' => true,
            ]);
        }
    }
}
