<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property int $participant_id
 * @property string $token
 * @property \Illuminate\Support\Carbon $subscribed_at
 * @property ?\Illuminate\Support\Carbon $confirmed_at
 * @property ?\Illuminate\Support\Carbon $unsubscribed_at
 * @property Participant $participant
 */
class NewsletterSubscription extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'participant_id',
        'token',
        'subscribed_at',
        'confirmed_at',
        'unsubscribed_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $subscription): void {
            $subscription->token ??= Str::random(64);
            $subscription->subscribed_at ??= now();
        });
    }

    /**
     * @return BelongsTo<Participant, $this>
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function isActive(): bool
    {
        return $this->unsubscribed_at === null;
    }

    public function unsubscribe(): void
    {
        $this->update([
            'unsubscribed_at' => now(),
        ]);
    }

    public function resubscribe(): void
    {
        $this->update([
            'subscribed_at' => now(),
            'unsubscribed_at' => null,
        ]);
    }

    /**
     * @param Builder<NewsletterSubscription> $query
     * @return Builder<NewsletterSubscription>
     */
    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->whereNull('unsubscribed_at');
    }

    public static function activeCount(): int
    {
        return static::query()->active()->count();
    }

    protected function casts(): array
    {
        return [
            'subscribed_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }
}
