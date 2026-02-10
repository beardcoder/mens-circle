<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\EventNotificationService;
use App\Traits\ClearsResponseCache;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Override;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @use HasFactory<EventFactory>
 *
 * @property Carbon $event_date
 * @property Carbon $start_time
 * @property Carbon $end_time
 * @property int $max_participants
 * @property bool $is_published
 * @property string $title
 * @property ?string $description
 * @property ?string $location
 * @property int $activeRegistrationsCount
 * @property int $availableSpots
 * @property bool $isFull
 * @property bool $isPast
 * @property ?string $fullAddress
 */
class Event extends Model implements HasMedia
{
    use ClearsResponseCache;

    /** @use HasFactory<EventFactory> */
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

    #[Override]
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(fn ($model) => $model->event_date->format('Y-m-d'))
            ->saveSlugsTo('slug');
    }

    #[Override]
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('event_image')
            ->singleFile()
            ->useDisk('public');
    }

    /**
     * @return HasMany<Registration, $this>
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    /**
     * @return HasMany<Registration, $this>
     */
    public function activeRegistrations(): HasMany
    {
        return $this->registrations()
            ->active();
    }

    /**
     * @return Attribute<int, never>
     */
    protected function activeRegistrationsCount(): Attribute
    {
        return Attribute::make(
            get: fn (): int => (int) ($this->active_registrations_count ?? $this->activeRegistrations()->count()),
        );
    }

    /**
     * @return Attribute<int, never>
     */
    protected function availableSpots(): Attribute
    {
        return Attribute::make(
            get: fn (): int => max(0, $this->max_participants - $this->activeRegistrationsCount),
        );
    }

    /**
     * @return Attribute<bool, never>
     */
    protected function isFull(): Attribute
    {
        return Attribute::make(get: fn (): bool => $this->availableSpots <= 0);
    }

    /**
     * @return Attribute<bool, never>
     */
    protected function isPast(): Attribute
    {
        return Attribute::make(get: fn (): bool => $this->event_date->endOfDay()->isPast());
    }

    /**
     * @return Attribute<?string, never>
     */
    protected function fullAddress(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                if (!$this->street || !$this->city) {
                    return null;
                }

                $parts = [$this->street, $this->postal_code ? "{$this->postal_code} {$this->city}" : $this->city, ];

                return implode(', ', $parts);
            },
        );
    }

    public function generateICalContent(): string
    {
        $startDateTime = $this->event_date->copy()
            ->setTimeFrom($this->start_time)
            ->format('Ymd\THis');

        $endDateTime = $this->event_date->copy()
            ->setTimeFrom($this->end_time)
            ->format('Ymd\THis');

        $now = now()
            ->format('Ymd\THis\Z');
        $location = $this->fullAddress ?? $this->location;
        $description = str_replace(["\r\n", "\n", "\r"], '\n', strip_tags($this->description ?? ''));

        $uid = $this->id . '@mens-circle.de';

        return <<<ICAL
            BEGIN:VCALENDAR\r
            VERSION:2.0\r
            PRODID:-//Männerkreis Niederbayern/ Straubing//Event//DE\r
            BEGIN:VEVENT\r
            UID:{$uid}\r
            DTSTAMP:{$now}\r
            DTSTART:{$startDateTime}\r
            DTEND:{$endDateTime}\r
            SUMMARY:{$this->title}\r
            DESCRIPTION:{$description}\r
            LOCATION:{$location}\r
            END:VEVENT\r
            END:VCALENDAR\r

            ICAL;
    }

    public function sendRegistrationConfirmation(Registration $registration): void
    {
        app(EventNotificationService::class)->sendRegistrationConfirmation($this, $registration);
    }

    public function sendEventReminder(Registration $registration): void
    {
        app(EventNotificationService::class)->sendEventReminder($this, $registration);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'event_date' => 'datetime',
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'is_published' => 'boolean',
        ];
    }

    public static function upcomingCount(): int
    {
        return static::query()->published()->upcoming()->count();
    }

    public static function nextEvent(): ?self
    {
        return static::query()
            ->published()
            ->upcoming()
            ->withCount('activeRegistrations')
            ->orderBy('event_date')
            ->first();
    }

    /**
     * @param Builder<Event> $query
     *
     * @return Builder<Event>
     */
    #[Scope]
    protected function published(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * @param Builder<Event> $query
     *
     * @return Builder<Event>
     */
    #[Scope]
    protected function upcoming(Builder $query): Builder
    {
        return $query->where('event_date', '>=', now());
    }
}
