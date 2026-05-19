<?php

declare(strict_types=1);

namespace App\Mcp\Servers;

use App\Mcp\Tools\CreateEvent;
use App\Mcp\Tools\GetPage;
use App\Mcp\Tools\ListEvents;
use App\Mcp\Tools\ListPages;
use App\Mcp\Tools\PublishPage;
use App\Mcp\Tools\UpdateEvent;
use App\Mcp\Tools\UpdatePageContent;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Männerkreis Content Server')]
#[Version('1.0.0')]
#[Instructions(<<<'TXT'
    Edit the public website of Männerkreis Niederbayern.

    Pages are managed via a content-block system. A page has an ordered list of
    blocks; each block has a type (e.g. hero, intro, faq, cta) and a free-form
    data object. Use get-page first to inspect the current shape of a page
    before calling update-page-content — update-page-content REPLACES the full
    block list, so blocks you omit will be removed.

    Events have a date, time window (start/end), location, capacity and an
    optional cost notice. Drafts are created with is_published=false. Once an
    event date passes, it appears in past-event listings automatically.

    German is the user-facing language: prefer German text for titles,
    descriptions and CTAs unless the user asks otherwise.
    TXT)]
class ContentServer extends Server
{
    /**
     * @var array<int, class-string<\Laravel\Mcp\Server\Tool>>
     */
    protected array $tools = [
        ListPages::class,
        GetPage::class,
        UpdatePageContent::class,
        PublishPage::class,
        ListEvents::class,
        CreateEvent::class,
        UpdateEvent::class,
    ];

    /**
     * @var array<int, class-string<\Laravel\Mcp\Server\Resource>>
     */
    protected array $resources = [];

    /**
     * @var array<int, class-string<\Laravel\Mcp\Server\Prompt>>
     */
    protected array $prompts = [];
}
