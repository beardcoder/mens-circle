<?php

declare(strict_types=1);

use App\Models\Page;
use Illuminate\Support\Str;

test('llms output includes archetypes block content', function (): void {
    $page = Page::factory()
        ->published()
        ->create([
            'title' => 'Archetypen Kompass',
            'slug' => 'archetypen-kompass',
        ]);

    $page->contentBlocks()->create([
        'type' => 'archetypes',
        'block_id' => (string) Str::uuid(),
        'order' => 0,
        'data' => [
            'eyebrow' => 'Archetypen',
            'title' => 'Die fuenf Kraefte',
            'intro' => 'Ein Kompass fuer maennliche Entwicklung.',
            'items' => [
                [
                    'number' => 1,
                    'title' => 'Der Krieger',
                    'description' => 'Grenzen setzen und Verantwortung uebernehmen.',
                ],
                [
                    'number' => 2,
                    'title' => 'Der Koenig',
                    'description' => 'Souveraen fuehren und fuer Stabilitaet sorgen.',
                ],
            ],
        ],
    ]);

    $response = $this->get(route('llms.txt'));

    $response
        ->assertSuccessful()
        ->assertSeeText('Die fuenf Kraefte')
        ->assertSeeText('Ein Kompass fuer maennliche Entwicklung.')
        ->assertSeeText('Der Krieger')
        ->assertSeeText('Der Koenig');
});
