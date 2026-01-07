<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Event extends Model implements HasMedia
{
    use HasFactory;
    use HasSlug;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'image',
        'event_date',
        'start_time',
        'end_time',
        'location',
        'street',
        'postal_code',
        'city',
        'location_details',
        'max_participants',
        'cost_basis',
        'is_published',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(fn ($model) => $model->event_date->format('Y-m-d'))
            ->saveSlugsTo('slug');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('event_image')
            ->singleFile()
            ->useDisk('public');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function confirmedRegistrations(): HasMany
    {
        return $this->registrations()->where('status', 'confirmed');
    }

    public function confirmedRegistrationsCount(): int
    {
        return (int) ($this->confirmed_registrations_count ?? $this->confirmedRegistrations()->count());
    }

    public function availableSpots(): int
    {
        return $this->max_participants - $this->confirmedRegistrationsCount();
    }

    public function isFull(): bool
    {
        return $this->availableSpots() <= 0;
    }

    public function isPast(): bool
    {
        return $this->event_date->endOfDay()->isPast();
    }

    public function getFullAddress(): ?string
    {
        if (! $this->street || ! $this->city) {
            return null;
        }

        $parts = array_filter([
            $this->street,
            $this->postal_code ? $this->postal_code.' '.$this->city : $this->city,
        ]);

        return implode(', ', $parts);
    }

    public function generateICalContent(): string
    {
        $startDateTime = $this->event_date->copy()
            ->setTimeFrom($this->start_time)
            ->format('Ymd\THis');

        $endDateTime = $this->event_date->copy()
            ->setTimeFrom($this->end_time)
            ->format('Ymd\THis');

        $now = now()->format('Ymd\THis\Z');

        $location = $this->getFullAddress() ?? $this->location;
        $description = strip_tags($this->description ?? '');
        $description = str_replace(["\r\n", "\n", "\r"], '\n', $description);

        $uid = $this->id.'@mens-circle.de';

        $ical = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//Männerkreis Niederbayern/ Straubing//Event//DE\r\n";
        $ical .= "BEGIN:VEVENT\r\n";
        $ical .= "UID:{$uid}\r\n";
        $ical .= "DTSTAMP:{$now}\r\n";
        $ical .= "DTSTART:{$startDateTime}\r\n";
        $ical .= "DTEND:{$endDateTime}\r\n";
        $ical .= "SUMMARY:{$this->title}\r\n";
        $ical .= "DESCRIPTION:{$description}\r\n";
        $ical .= "LOCATION:{$location}\r\n";
        $ical .= "END:VEVENT\r\n";

        return $ical."END:VCALENDAR\r\n";
    }

    protected function casts(): array
    {
        return [
            'event_date' => 'datetime',
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'is_published' => 'boolean',
        ];
    }
}
