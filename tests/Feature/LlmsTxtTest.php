<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LlmsTxtTest extends TestCase
{
    use RefreshDatabase;

    public function test_llms_txt_returns_successful_response(): void
    {
        $response = $this->get('/llms.txt');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; charset=utf-8');
    }

    public function test_llms_txt_contains_site_name_as_h1(): void
    {
        $response = $this->get('/llms.txt');

        $response->assertStatus(200);
        $this->assertStringContainsString('# Männerkreis Niederbayern', $response->getContent());
    }

    public function test_llms_txt_contains_blockquote_description(): void
    {
        $response = $this->get('/llms.txt');

        $response->assertStatus(200);
        $this->assertStringContainsString('> ', $response->getContent());
    }

    public function test_llms_txt_lists_upcoming_published_events(): void
    {
        $event = Event::factory()->published()->tomorrow()->create([
            'title' => 'Test Männerkreis Event',
            'location' => 'Straubing',
        ]);

        $response = $this->get('/llms.txt');

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('## Veranstaltungen', $content);
        $this->assertStringContainsString('Test Männerkreis Event', $content);
        $this->assertStringContainsString('Straubing', $content);
    }

    public function test_llms_txt_does_not_list_unpublished_events(): void
    {
        Event::factory()->unpublished()->tomorrow()->create([
            'title' => 'Unpublished Secret Event',
        ]);

        $response = $this->get('/llms.txt');

        $response->assertStatus(200);
        $this->assertStringNotContainsString('Unpublished Secret Event', $response->getContent());
    }

    public function test_llms_txt_lists_past_events(): void
    {
        Event::factory()->published()->create([
            'title' => 'Vergangenes Event',
            'event_date' => now()->subWeek(),
        ]);

        $response = $this->get('/llms.txt');

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('## Vergangene Veranstaltungen', $content);
        $this->assertStringContainsString('Vergangenes Event', $content);
    }

    public function test_llms_txt_lists_published_pages(): void
    {
        Page::create([
            'title' => 'Über Uns',
            'slug' => 'ueber-uns',
            'is_published' => true,
            'published_at' => now(),
            'content_blocks' => [],
        ]);

        $response = $this->get('/llms.txt');

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('## Seiten', $content);
        $this->assertStringContainsString('Über Uns', $content);
    }

    public function test_llms_txt_does_not_list_unpublished_pages(): void
    {
        Page::create([
            'title' => 'Draft Page',
            'slug' => 'draft-page',
            'is_published' => false,
            'content_blocks' => [],
        ]);

        $response = $this->get('/llms.txt');

        $response->assertStatus(200);
        $this->assertStringNotContainsString('Draft Page', $response->getContent());
    }

    public function test_llms_txt_contains_legal_section(): void
    {
        $response = $this->get('/llms.txt');

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('## Rechtliches', $content);
        $this->assertStringContainsString('Impressum', $content);
        $this->assertStringContainsString('Datenschutz', $content);
    }

    public function test_llms_txt_contains_actions_section(): void
    {
        $response = $this->get('/llms.txt');

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('## Aktionen', $content);
        $this->assertStringContainsString('Newsletter', $content);
    }

    public function test_llms_txt_is_properly_formatted_markdown(): void
    {
        Event::factory()->published()->tomorrow()->create([
            'title' => 'Markdown Test Event',
        ]);

        $response = $this->get('/llms.txt');

        $response->assertStatus(200);
        $content = $response->getContent();

        // Check for proper markdown link format: [text](url)
        $this->assertMatchesRegularExpression('/\[.+\]\(https?:\/\/.+\)/', $content);
    }

    public function test_llms_txt_route_is_named(): void
    {
        $this->assertEquals('/llms.txt', route('llms.txt', [], false));
    }
}
