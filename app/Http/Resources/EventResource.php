<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Event
 */
class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'eventDate' => $this->event_date->toIso8601String(),
            'startTime' => $this->start_time->format('H:i'),
            'endTime' => $this->end_time->format('H:i'),
            'location' => [
                'name' => $this->location,
                'street' => $this->street,
                'postalCode' => $this->postal_code,
                'city' => $this->city,
                'details' => $this->location_details,
                'fullAddress' => $this->fullAddress,
            ],
            'capacity' => [
                'max' => $this->max_participants,
                'registered' => $this->activeRegistrationsCount,
                'available' => $this->availableSpots,
                'isFull' => $this->isFull,
            ],
            'costBasis' => $this->cost_basis,
            'isPublished' => $this->is_published,
            'isPast' => $this->isPast,
            'image' => $this->getFirstMediaUrl('event_image'),
        ];
    }
}
