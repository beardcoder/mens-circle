<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\DefinesCacheUrls;
use App\Enums\RegistrationStatus;
use App\Observers\RegistrationObserver;
use App\Traits\ClearsResponseCache;
use Database\Factories\RegistrationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\CarbonImmutable;
use Override;

/**
 * @property int $participant_id
 * @property int $event_id
 * @property RegistrationStatus $status
 * @property CarbonImmutable $registered_at
 * @property ?CarbonImmutable $cancelled_at
 * @property ?CarbonImmutable $reminder_sent_at
 * @property ?CarbonImmutable $sms_reminder_sent_at
 * @property Participant $participant
 * @property Event $event
 */
#[Fillable(['participant_id', 'event_id', 'status', 'registered_at', 'cancelled_at', 'reminder_sent_at', 'sms_reminder_sent_at'])]
#[ObservedBy(RegistrationObserver::class)]
#[UseFactory(RegistrationFactory::class)]
class Registration extends Model implements DefinesCacheUrls
{
    use ClearsResponseCache;

    /** @use HasFactory<RegistrationFactory> */
    use HasFactory;
    use SoftDeletes;

    /**
     * @return list<string>
     */
    public function getCacheUrls(): array
    {
        $eventSlug = $this->relationLoaded('event')
            ? $this->event->slug
            : Event::query()->where('id', $this->event_id)->value('slug');

        return array_values(array_filter([
            url('/event'),
            $eventSlug ? route('event.show.slug', $eventSlug) : null,
        ]));
    }

    /**
     * @return list<string>
     */
    public function getCacheKeys(): array
    {
        return ['next_event_data'];
    }

    /**
     * @return BelongsTo<Participant, $this>
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function cancel(): void
    {
        $this->update([
            'status' => RegistrationStatus::Cancelled,
            'cancelled_at' => now(),
        ]);
    }

    public function promote(): void
    {
        $this->update([
            'status' => RegistrationStatus::Registered,
        ]);
    }

    public function markAsAttended(): void
    {
        $this->update([
            'status' => RegistrationStatus::Attended,
        ]);
    }

    /**
     * @param Builder<Registration> $query
     *
     * @return Builder<Registration>
     */
    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->whereIn('status', [RegistrationStatus::Registered, RegistrationStatus::Attended]);
    }

    /**
     * @param Builder<Registration> $query
     *
     * @return Builder<Registration>
     */
    #[Scope]
    protected function registered(Builder $query): Builder
    {
        return $query->where('status', RegistrationStatus::Registered);
    }

    /**
     * @param Builder<Registration> $query
     *
     * @return Builder<Registration>
     */
    #[Scope]
    protected function cancelled(Builder $query): Builder
    {
        return $query->where('status', RegistrationStatus::Cancelled);
    }

    /**
     * @param Builder<Registration> $query
     *
     * @return Builder<Registration>
     */
    #[Scope]
    protected function waitlisted(Builder $query): Builder
    {
        return $query->where('status', RegistrationStatus::Waitlist);
    }

    public static function registeredCount(): int
    {
        return static::query()->registered()->count();
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'status' => RegistrationStatus::class,
            'registered_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
            'sms_reminder_sent_at' => 'datetime',
        ];
    }
}
