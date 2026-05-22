<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\NavigationCondition;
use App\Enums\NavigationLocation;
use App\Models\NavigationItem;
use Illuminate\Database\Seeder;

/**
 * Seeds the default navigation that previously lived in the layout template.
 *
 * Seed identity is the tuple (location, label). The seeder is idempotent:
 * running it again preserves any user edits to existing rows (matched by the
 * tuple) and only fills in missing rows. If multiple items with the same
 * label inside one location are ever required at runtime, create them via
 * the admin UI / MCP tools — the seeder is intentionally narrow.
 */
class NavigationItemSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->items() as $item) {
            /** @var array{location: NavigationLocation, label: string} $item */
            NavigationItem::query()->updateOrCreate(['location' => $item['location'], 'label' => $item['label']], $item);
        }
    }

    /**
     * @return list<array{location: NavigationLocation, label: string, url?: string, condition?: NavigationCondition, is_cta?: bool, umami_event_target?: string, sort?: int}>
     */
    private function items(): array
    {
        return [
            // Header
            ['location' => NavigationLocation::Header, 'label' => 'Über', 'url' => '#ueber', 'umami_event_target' => 'ueber', 'sort' => 10],
            [
                'location' => NavigationLocation::Header,
                'label' => 'Die Reise',
                'url' => '#reise',
                'umami_event_target' => 'reise',
                'sort' => 20,
            ],
            ['location' => NavigationLocation::Header, 'label' => 'Fragen', 'url' => '#faq', 'umami_event_target' => 'faq', 'sort' => 30],
            [
                'location' => NavigationLocation::Header,
                'label' => 'Atemübung',
                'url' => '/atemuebung',
                'umami_event_target' => 'atemuebung',
                'sort' => 40,
            ],
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
            [
                'location' => NavigationLocation::FooterPrimary,
                'label' => 'Über uns',
                'url' => '#ueber',
                'umami_event_target' => 'ueber',
                'sort' => 10,
            ],
            [
                'location' => NavigationLocation::FooterPrimary,
                'label' => 'Die Reise',
                'url' => '#reise',
                'umami_event_target' => 'reise',
                'sort' => 20,
            ],
            [
                'location' => NavigationLocation::FooterPrimary,
                'label' => 'FAQ',
                'url' => '#faq',
                'umami_event_target' => 'faq',
                'sort' => 30,
            ],
            [
                'location' => NavigationLocation::FooterPrimary,
                'label' => 'Atemübung',
                'url' => '/atemuebung',
                'umami_event_target' => 'atemuebung',
                'sort' => 40,
            ],
            [
                'location' => NavigationLocation::FooterPrimary,
                'label' => 'Nächster Termin',
                'url' => '',
                'condition' => NavigationCondition::NextEvent,
                'umami_event_target' => 'event',
                'sort' => 50,
            ],

            // Footer contact
            [
                'location' => NavigationLocation::FooterContact,
                'label' => 'Newsletter',
                'url' => '#newsletter',
                'umami_event_target' => 'newsletter',
                'sort' => 10,
            ],

            // Footer legal
            [
                'location' => NavigationLocation::FooterLegal,
                'label' => 'Impressum',
                'url' => '/impressum',
                'umami_event_target' => 'impressum',
                'sort' => 10,
            ],
            [
                'location' => NavigationLocation::FooterLegal,
                'label' => 'Datenschutz',
                'url' => '/datenschutz',
                'umami_event_target' => 'datenschutz',
                'sort' => 20,
            ],
        ];
    }
}
