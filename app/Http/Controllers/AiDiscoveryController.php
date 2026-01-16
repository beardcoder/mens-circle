<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Page;
use App\Settings\GeneralSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class AiDiscoveryController extends Controller
{
    /**
     * Site structure and metadata endpoint.
     * Provides overview of site, content types, and available discovery endpoints.
     */
    public function site(GeneralSettings $settings): JsonResponse
    {
        return Cache::remember('ai_discovery:site', now()->addHours(12), function () use ($settings): array {
            return [
                'site' => [
                    'name' => $settings->site_name,
                    'tagline' => $settings->site_tagline,
                    'description' => $settings->site_description,
                    'location' => $settings->location,
                    'language' => 'de',
                ],
                'contact' => [
                    'email' => $settings->contact_email,
                    'phone' => $settings->contact_phone,
                ],
                'community' => [
                    'whatsapp' => $settings->whatsapp_community_link,
                    'social_links' => $settings->social_links ?? [],
                ],
                'content_types' => [
                    'pages' => [
                        'description' => 'Dynamische Seiten mit strukturierten Content-Blöcken',
                        'endpoint' => route('ai.pages'),
                    ],
                    'events' => [
                        'description' => 'Veranstaltungen mit Datum, Ort und Anmeldungsstatus',
                        'endpoint' => route('ai.events'),
                    ],
                ],
                'capabilities' => [
                    'event_registration' => [
                        'available' => true,
                        'requires' => ['first_name', 'last_name', 'email', 'phone_number'],
                    ],
                    'newsletter_subscription' => [
                        'available' => true,
                        'requires' => ['email'],
                    ],
                ],
            ];
        });
    }

    /**
     * Pages discovery endpoint.
     * Returns all published pages with their content blocks, structured for AI interpretation.
     */
    public function pages(): JsonResponse
    {
        return Cache::remember('ai_discovery:pages', now()->addHours(6), function (): array {
            $pages = Page::published()
                ->with(['contentBlocks'])
                ->orderBy('title')
                ->get();

            return [
                'pages' => $pages->map(function (Page $page): array {
                    return [
                        'title' => $page->title,
                        'slug' => $page->slug,
                        'url' => url($page->slug === 'home' ? '/' : "/{$page->slug}"),
                        'published_at' => $page->published_at?->toIso8601String(),
                        'meta' => $page->meta,
                        'content_blocks' => $page->contentBlocks->map(function ($block): array {
                            return [
                                'type' => $block->type,
                                'order' => $block->order,
                                'content' => $this->extractBlockContent($block->type, $block->data),
                            ];
                        })->toArray(),
                    ];
                })->toArray(),
            ];
        });
    }

    /**
     * Events discovery endpoint.
     * Returns all published events (upcoming and past) with full details and availability status.
     */
    public function events(): JsonResponse
    {
        return Cache::remember('ai_discovery:events', now()->addMinutes(15), function (): array {
            $upcomingEvents = Event::published()
                ->upcoming()
                ->withCount('confirmedRegistrations as confirmed_registrations_count')
                ->orderBy('event_date', 'asc')
                ->get();

            $pastEvents = Event::published()
                ->where('event_date', '<', now())
                ->withCount('confirmedRegistrations as confirmed_registrations_count')
                ->orderBy('event_date', 'desc')
                ->limit(10)
                ->get();

            return [
                'upcoming_events' => $upcomingEvents->map(fn (Event $event): array => $this->formatEvent($event))->toArray(),
                'past_events' => $pastEvents->map(fn (Event $event): array => $this->formatEvent($event, includeFull: false))->toArray(),
            ];
        });
    }

    /**
     * Format event data for AI consumption.
     */
    private function formatEvent(Event $event, bool $includeFull = true): array
    {
        $data = [
            'title' => $event->title,
            'slug' => $event->slug,
            'url' => url("/event/{$event->slug}"),
            'description' => $event->description,
            'date' => $event->event_date->toDateString(),
            'time' => [
                'start' => $event->start_time->format('H:i'),
                'end' => $event->end_time->format('H:i'),
            ],
            'location' => [
                'name' => $event->location,
                'street' => $event->street,
                'postal_code' => $event->postal_code,
                'city' => $event->city,
                'full_address' => $event->fullAddress,
                'details' => $event->location_details,
            ],
            'cost_basis' => $event->cost_basis,
        ];

        if ($includeFull) {
            $data['registration'] = [
                'max_participants' => $event->max_participants,
                'confirmed_registrations' => $event->confirmedRegistrationsCount,
                'available_spots' => $event->availableSpots,
                'is_full' => $event->isFull,
                'is_open' => ! $event->isFull && ! $event->isPast,
            ];
        }

        return $data;
    }

    /**
     * Extract meaningful content from content blocks based on type.
     * Returns structured data optimized for AI understanding.
     */
    private function extractBlockContent(string $type, array $data): array
    {
        return match ($type) {
            'hero' => [
                'title' => $data['title'] ?? null,
                'description' => $data['description'] ?? null,
                'button_text' => $data['button_text'] ?? null,
                'button_url' => $data['button_url'] ?? null,
            ],
            'intro' => [
                'eyebrow' => $data['eyebrow'] ?? null,
                'title' => $data['title'] ?? null,
                'text' => $data['text'] ?? null,
                'quote' => $data['quote'] ?? null,
                'quote_author' => $data['quote_author'] ?? null,
                'values' => $data['values'] ?? [],
            ],
            'text_section' => [
                'content' => $data['content'] ?? null,
            ],
            'value_items' => [
                'items' => collect($data['items'] ?? [])->map(fn (array $item): array => [
                    'number' => $item['number'] ?? null,
                    'title' => $item['title'] ?? null,
                    'description' => $item['description'] ?? null,
                ])->toArray(),
            ],
            'moderator' => [
                'name' => $data['name'] ?? null,
                'bio' => $data['bio'] ?? null,
                'quote' => $data['quote'] ?? null,
            ],
            'journey_steps' => [
                'title' => $data['title'] ?? null,
                'steps' => collect($data['steps'] ?? [])->map(fn (array $step): array => [
                    'title' => $step['title'] ?? null,
                    'description' => $step['description'] ?? null,
                ])->toArray(),
            ],
            'testimonials' => [
                'type' => 'auto_display',
                'note' => 'Displays published testimonials automatically',
            ],
            'faq' => [
                'items' => collect($data['items'] ?? [])->map(fn (array $item): array => [
                    'question' => $item['question'] ?? null,
                    'answer' => $item['answer'] ?? null,
                ])->toArray(),
            ],
            'newsletter' => [
                'title' => $data['title'] ?? null,
                'description' => $data['description'] ?? null,
                'note' => 'Newsletter signup form',
            ],
            'cta' => [
                'title' => $data['title'] ?? null,
                'description' => $data['description'] ?? null,
                'button_text' => $data['button_text'] ?? null,
                'button_url' => $data['button_url'] ?? null,
            ],
            'whatsapp_community' => [
                'title' => $data['title'] ?? null,
                'description' => $data['description'] ?? null,
                'button_text' => $data['button_text'] ?? null,
                'note' => 'WhatsApp community join link',
            ],
            default => ['raw_data' => $data],
        };
    }
}
