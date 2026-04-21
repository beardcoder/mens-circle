<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Models\ContentBlock;
use App\Models\Event;
use App\Models\Newsletter;
use App\Models\Page;
use App\Models\Registration;
use App\Models\Testimonial;
use App\Settings\GeneralSettings;
use Illuminate\Support\Collection;

final class AiDataFormatter
{
    /**
     * @return array<string, mixed>
     */
    public function event(Event $event, bool $includeRegistrations = false): array
    {
        return [
            'id' => $event->id,
            'title' => $event->title,
            'slug' => $event->slug,
            'description' => $event->description,
            'event_date' => $event->event_date->toDateString(),
            'start_time' => $event->start_time->format('H:i'),
            'end_time' => $event->end_time->format('H:i'),
            'location' => $event->location,
            'street' => $event->street,
            'postal_code' => $event->postal_code,
            'city' => $event->city,
            'full_address' => $event->fullAddress,
            'location_details' => $event->location_details,
            'max_participants' => $event->max_participants,
            'cost_basis' => $event->cost_basis,
            'active_registrations_count' => $event->activeRegistrationsCount,
            'available_spots' => $event->availableSpots,
            'is_full' => $event->isFull,
            'is_past' => $event->isPast,
            'is_published' => $event->is_published,
            'image_url' => $event->getFirstMediaUrl('event_image', 'webp') ?: null,
            'registrations' => $includeRegistrations
                ? ($event->relationLoaded('registrations')
                    ? $event->registrations
                    : $event->registrations()
                        ->with('participant')
                        ->orderByDesc('registered_at')
                        ->get())
                    ->map(static fn(Registration $registration): array => [
                        'id' => $registration->id,
                        'status' => $registration->status->value,
                        'registered_at' => $registration->registered_at->toIso8601String(),
                        'participant' => [
                            'id' => $registration->participant->id,
                            'first_name' => $registration->participant->first_name,
                            'last_name' => $registration->participant->last_name,
                            'email' => $registration->participant->email,
                            'phone_number' => $registration->participant->phone,
                        ],
                    ])->values()->all()
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function page(Page $page): array
    {
        return [
            'id' => $page->id,
            'title' => $page->title,
            'slug' => $page->slug,
            'meta' => $page->meta,
            'is_published' => $page->is_published,
            'published_at' => $page->published_at?->toIso8601String(),
            'content_blocks' => $page->contentBlocks
                ->map(static fn(ContentBlock $block): array => [
                    'type' => $block->type,
                    'order' => $block->order,
                    'block_id' => $block->block_id,
                    'data' => $block->data,
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @param Collection<int, Page> $pages
     *
     * @return array<int, array<string, mixed>>
     */
    public function pages(Collection $pages): array
    {
        return $pages->map(fn(Page $page): array => $this->page($page))->values()->all();
    }

    /**
     * @param Collection<int, Event> $events
     *
     * @return array<int, array<string, mixed>>
     */
    public function events(Collection $events): array
    {
        return $events->map(fn(Event $event): array => $this->event($event))->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function newsletter(Newsletter $newsletter): array
    {
        return [
            'id' => $newsletter->id,
            'subject' => $newsletter->subject,
            'content' => $newsletter->content,
            'status' => $newsletter->status->value,
            'status_label' => $newsletter->status->getLabel(),
            'sent_at' => $newsletter->sent_at?->toIso8601String(),
            'recipient_count' => $newsletter->recipient_count,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function testimonial(Testimonial $testimonial): array
    {
        return [
            'id' => $testimonial->id,
            'quote' => $testimonial->quote,
            'author_name' => $testimonial->author_name,
            'email' => $testimonial->email,
            'role' => $testimonial->role,
            'is_published' => $testimonial->is_published,
            'published_at' => $testimonial->published_at?->toIso8601String(),
        ];
    }

    /**
     * @param Collection<int, Testimonial> $testimonials
     *
     * @return array<int, array<string, mixed>>
     */
    public function testimonials(Collection $testimonials): array
    {
        return $testimonials->map(fn(Testimonial $testimonial): array => $this->testimonial($testimonial))->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function settings(GeneralSettings $settings): array
    {
        return [
            'site_name' => $settings->site_name,
            'site_tagline' => $settings->site_tagline,
            'site_description' => $settings->site_description,
            'contact_email' => $settings->contact_email,
            'contact_phone' => $settings->contact_phone,
            'location' => $settings->location,
            'whatsapp_community_link' => $settings->whatsapp_community_link,
            'social_links' => $settings->social_links,
            'footer_text' => $settings->footer_text,
            'event_default_max_participants' => $settings->event_default_max_participants,
        ];
    }
}
