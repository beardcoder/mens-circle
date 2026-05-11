<?php

declare(strict_types=1);

use App\Models\ContentBlock;
use App\Models\Page;
use Symfony\Component\DomCrawler\Crawler;

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

    $response->assertOk();

    $content = $response->getContent();

    expect($content)->not->toBeFalse();

    $crawler = new Crawler($content);
    $introValueStyles = $crawler->filter('.intro-section .intro__values[data-animate="scroll"]')->extract(['style']);
    $heroTitleStyles = $crawler->filter('.hero__title[data-animate="scroll"]')->extract(['style']);

    expect($crawler->filter('.hero__label[data-animate="scroll"]')->count())->toBe(1);
    expect($crawler->filter('.hero__title[data-animate="scroll"]')->count())->toBe(1);
    expect($crawler->filter('.hero__cta[data-animate="scroll"]')->count())->toBe(1);
    expect($crawler->filter('.hero__cta[data-animate="scroll"] .btn[data-hover="lift"]')->count())->toBe(1);
    expect($crawler->filter('.intro__text[data-animate="scroll"]')->count())->toBe(1);
    expect($crawler->filter('.intro-section .intro__values[data-animate="scroll"]')->count())->toBe(1);
    expect($introValueStyles)->toBe(['--animate-delay: 220ms']);
    expect($crawler->filter('.section-header[data-animate="scroll"]')->count())->toBeGreaterThan(0);
    expect($heroTitleStyles)->toBe(['--animate-delay: 120ms']);
});
