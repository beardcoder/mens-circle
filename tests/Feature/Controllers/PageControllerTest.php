<?php

declare(strict_types=1);

use App\Models\ContentBlock;
use App\Models\Page;

test('home page renders micro animation hooks for prominent sections', function (): void {
    $page = Page::factory()->published()->create([
        'title' => 'Startseite',
        'slug' => 'home',
    ]);

    ContentBlock::factory()->forPage($page)->create([
        'type' => 'hero',
        'order' => 0,
        'data' => [
            'label' => 'Willkommen',
            'title' => 'Männerkreis Niederbayern',
            'description' => 'Authentischer Austausch und persönliche Entwicklung.',
            'button_text' => 'Mehr erfahren',
            'button_link' => '/kontakt',
        ],
    ]);

    ContentBlock::factory()->forPage($page)->create([
        'type' => 'intro',
        'order' => 1,
        'data' => [
            'eyebrow' => 'Über uns',
            'title' => 'Ein Raum für echte Verbindung',
            'text' => 'Wir schaffen einen sicheren Rahmen für ehrliche Begegnung.',
            'quote' => 'Mut. Präsenz. Gemeinschaft.',
            'values' => [
                [
                    'number' => '01',
                    'title' => 'Vertrauen',
                    'description' => 'Verbindung entsteht durch echte Offenheit.',
                ],
            ],
        ],
    ]);

    ContentBlock::factory()->forPage($page)->create([
        'type' => 'value_items',
        'order' => 2,
        'data' => [
            'eyebrow' => 'Werte',
            'title' => 'Was uns trägt',
            'items' => [
                [
                    'number' => '01',
                    'title' => 'Mut',
                    'description' => 'Wir bleiben präsent, auch wenn es herausfordernd wird.',
                ],
                [
                    'number' => '02',
                    'title' => 'Klarheit',
                    'description' => 'Wir sprechen offen und hören aufmerksam zu.',
                ],
            ],
        ],
    ]);

    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('class="hero__label animate-on-scroll"', false)
        ->assertSee('class="hero__title animate-on-scroll"', false)
        ->assertSee('class="btn btn--primary btn--large hover-lift animate-on-scroll"', false)
        ->assertSee('class="intro__text animate-on-scroll"', false)
        ->assertSee('class="section-header animate-on-scroll"', false)
        ->assertSee('class="value-item animate-on-scroll"', false)
        ->assertSee('data-delay="120"', false);
});
