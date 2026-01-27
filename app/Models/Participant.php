<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ParticipantFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property ?string $phone
 * @property string $fullName
 */
class Participant extends Model
{
    /** @use HasFactory<ParticipantFactory> */
    use HasFactory;

    protected $fillable = ['first_name', 'last_name', 'email', 'phone', ];

    /**
     * @return HasMany<Registration, $this>
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    /**
     * @return HasOne<NewsletterSubscription, $this>
     */
    public function newsletterSubscription(): HasOne
    {
        return $this->hasOne(NewsletterSubscription::class);
    }

    /**
     * @return Attribute<string, never>
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(get: fn (): string => trim(sprintf('%s %s', $this->first_name, $this->last_name)));
    }

    public function isSubscribedToNewsletter(): bool
    {
        $subscription = $this->newsletterSubscription;

        return $subscription !== null && $subscription->unsubscribed_at === null;
    }

    public static function findByEmail(string $email): ?self
    {
        return static::where('email', $email)->first();
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public static function findOrCreateByEmail(string $email, array $attributes = []): self
    {
        return static::firstOrCreate([
'email' => $email
], $attributes);
    }
}
