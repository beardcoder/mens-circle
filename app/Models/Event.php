<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RegistrationStatus;
use App\Mail\EventRegistrationConfirmation;
use App\Traits\ClearsResponseCache;
use Exception;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Seven\Api\Client;
use Seven\Api\Resource\Sms\SmsParams;
use Seven\Api\Resource\Sms\SmsResource;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property \Illuminate\Support\Carbon $event_date
 * @property \Illuminate\Support\Carbon $start_time
 * @property \Illuminate\Support\Carbon $end_time
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
        return $this->hasMany(Registration::class);
    }

    public function activeRegistrations(): HasMany
    {
        return $this->registrations()->whereIn('status', [
            RegistrationStatus::Registered->value,
            RegistrationStatus::Attended->value,
        ]);
    }

    protected function activeRegistrationsCount(): Attribute
    {
        return Attribute::make(
            get: fn (): int => (int) ($this->active_registrations_count ?? $this->activeRegistrations()->count())
        );
    }

    protected function availableSpots(): Attribute
    {
        return Attribute::make(
            get: fn (): int => max(0, $this->max_participants - $this->activeRegistrationsCount)
        );
    }

    protected function isFull(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->availableSpots <= 0
        );
    }

    protected function isPast(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->event_date->endOfDay()->isPast()
        );
    }

    protected function fullAddress(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                if (! $this->street || ! $this->city) {
                    return null;
                }

                $parts = [
                    $this->street,
                    $this->postal_code ? sprintf('%s %s', $this->postal_code, $this->city) : $this->city,
                ];

                return implode(', ', $parts);
            }
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

        $now = now()->format('Ymd\THis\Z');
        $location = $this->fullAddress ?? $this->location;
        $description = str_replace(
            ["\r\n", "\n", "\r"],
            '\n',
            strip_tags($this->description ?? '')
        );

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
        $participant = $registration->participant;

        // Send email
        try {
            Mail::queue(new EventRegistrationConfirmation($registration, $this));
            Log::info('Event registration confirmation sent', [
                'registration_id' => $registration->id,
                'email' => $participant->email,
                'event_id' => $this->id,
            ]);
        } catch (Exception $exception) {
            Log::error('Failed to send event registration confirmation', [
                'registration_id' => $registration->id,
                'error' => $exception->getMessage(),
            ]);
        }

        // Send SMS if phone number provided
        if ($participant->phone) {
            $this->sendRegistrationSms($registration);
        }
    }

    public function sendEventReminder(Registration $registration): void
    {
        $participant = $registration->participant;

        if (! $participant->phone) {
            return;
        }

        $message = 'Erinnerung: Männerkreis findet morgen statt. Details per E-Mail. Bis bald!';
        $this->sendSms($participant->phone, $message, [
            'registration_id' => $registration->id,
            'type' => 'event_reminder',
        ]);
    }

    private function sendRegistrationSms(Registration $registration): void
    {
        $participant = $registration->participant;
        $message = sprintf(
            'Hallo %s! Deine Anmeldung ist bestätigt. Details per E-Mail. Männerkreis',
            $participant->first_name
        );

        $this->sendSms($participant->phone, $message, [
            'registration_id' => $registration->id,
            'type' => 'registration_confirmation',
        ]);
    }

    private function sendSms(string $phoneNumber, string $message, array $context = []): void
    {
        $apiKey = config('sevenio.api_key');

        if (! $apiKey) {
            Log::warning('Cannot send SMS - Seven.io API key not configured', $context);

            return;
        }

        try {
            $client = new Client($apiKey);
            $smsResource = new SmsResource($client);
            $params = new SmsParams(
                text: $message,
                to: $phoneNumber,
                from: config('sevenio.from')
            );

            $response = $smsResource->dispatch($params);

            Log::info('SMS sent successfully', array_merge($context, [
                'phone_number' => $phoneNumber,
                'event_id' => $this->id,
            ]));
        } catch (Exception $exception) {
            Log::error('Failed to send SMS', array_merge($context, [
                'phone_number' => $phoneNumber,
                'event_id' => $this->id,
                'error' => $exception->getMessage(),
            ]));
        }
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

    #[Scope]
    protected function published(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    #[Scope]
    protected function upcoming(Builder $query): Builder
    {
        return $query->where('event_date', '>=', now());
    }
}
