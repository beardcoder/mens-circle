<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
    ];

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function newsletterSubscription(): HasOne
    {
        return $this->hasOne(NewsletterSubscription::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
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

    public static function findOrCreateByEmail(string $email, array $attributes = []): self
    {
        return static::firstOrCreate(
            ['email' => $email],
            $attributes
        );
    }
}
