<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_is_past_returns_true_for_past_events(): void
    {
        $event = Event::factory()->past()->create();

        $this->assertTrue($event->isPast());
    }

    public function test_event_is_past_returns_false_for_future_events(): void
    {
        $event = Event::factory()->tomorrow()->create();

        $this->assertFalse($event->isPast());
    }

    public function test_event_is_past_returns_false_for_today_events(): void
    {
        $today = now();
        $event = Event::factory()->onDate($today)->create([
            'start_time' => $today->copy()->setTime(23, 0),
            'end_time' => $today->copy()->setTime(23, 59),
        ]);

        $this->assertFalse($event->isPast());
    }

    public function test_user_can_register_for_future_event(): void
    {
        $event = Event::factory()->published()->tomorrow()->create();

        $response = $this->postJson(route('event.register'), [
            'event_id' => $event->id,
            'first_name' => 'Max',
            'last_name' => 'Mustermann',
            'email' => 'max@example.com',
            'privacy' => true,
        ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('event_registrations', [
            'event_id' => $event->id,
            'email' => 'max@example.com',
        ]);
    }

    public function test_user_cannot_register_for_past_event(): void
    {
        $event = Event::factory()->published()->past()->create();

        $response = $this->postJson(route('event.register'), [
            'event_id' => $event->id,
            'first_name' => 'Max',
            'last_name' => 'Mustermann',
            'email' => 'max@example.com',
            'privacy' => true,
        ]);

        $response->assertStatus(410)
            ->assertJson([
                'success' => false,
                'message' => 'Diese Veranstaltung hat bereits stattgefunden. Eine Anmeldung ist nicht mehr möglich.',
            ]);

        $this->assertDatabaseMissing('event_registrations', [
            'event_id' => $event->id,
            'email' => 'max@example.com',
        ]);
    }

    public function test_past_event_page_shows_past_event_info(): void
    {
        $event = Event::factory()->published()->past()->create();

        $response = $this->get(route('event.show.slug', $event->slug));

        $response->assertOk()
            ->assertSee('Vergangenes Treffen')
            ->assertSee('Dieses Treffen hat stattgefunden')
            ->assertSee('Dieses Treffen liegt in der Vergangenheit')
            ->assertDontSee('Verbindlich anmelden');
    }

    public function test_future_event_page_shows_registration_form(): void
    {
        $event = Event::factory()->published()->tomorrow()->create();

        $response = $this->get(route('event.show.slug', $event->slug));

        $response->assertOk()
            ->assertSee('Nächstes Treffen')
            ->assertSee('Sichere dir')
            ->assertSee('Verbindlich anmelden')
            ->assertDontSee('Dieses Treffen hat stattgefunden');
    }

    public function test_past_event_shows_newsletter_cta_instead_of_registration(): void
    {
        $event = Event::factory()->published()->past()->create();

        $response = $this->get(route('event.show.slug', $event->slug));

        $response->assertOk()
            ->assertSee('Zum Newsletter anmelden')
            ->assertSee('Bleib')
            ->assertSee('informiert');
    }
}
