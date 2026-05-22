<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\NavigationType;
use App\Models\Navigation;
use App\Models\NavigationItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NavigationSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $headerNav = Navigation::withTrashed()->updateOrCreate(
                ['type' => NavigationType::Header],
                [
                    'name' => 'Hauptnavigation',
                    'is_active' => true,
                    'deleted_at' => null,
                ],
            );

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

            $this->replaceNavigationItems($headerNav, $headerItems);

            $footerNav = Navigation::withTrashed()->updateOrCreate(
                ['type' => NavigationType::Footer],
                [
                    'name' => 'Footer Navigation',
                    'is_active' => true,
                    'deleted_at' => null,
                ],
            );

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

            $this->replaceNavigationItems($footerNav, $footerItems);

            $legalNav = Navigation::withTrashed()->updateOrCreate(
                ['type' => NavigationType::Legal],
                [
                    'name' => 'Rechtliches',
                    'is_active' => true,
                    'deleted_at' => null,
                ],
            );

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

            $this->replaceNavigationItems($legalNav, $legalItems);
        });
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    private function replaceNavigationItems(Navigation $navigation, array $items): void
    {
        $navigation->items()->withTrashed()->forceDelete();

        foreach ($items as $item) {
            NavigationItem::create([
                'navigation_id' => $navigation->id,
                ...$item,
                'is_active' => true,
            ]);
        }
    }
}
