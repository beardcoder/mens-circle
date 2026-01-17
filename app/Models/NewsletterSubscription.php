<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property string $email
 * @property string $status
 * @property string $token
 * @property \Illuminate\Support\Carbon $subscribed_at
 * @property ?\Illuminate\Support\Carbon $unsubscribed_at
 */
class NewsletterSubscription extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'email',
        'status',
        'token',
        'subscribed_at',
        'unsubscribed_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $subscription): void {
            $subscription->token = Str::random(64);
            $subscription->subscribed_at = now();
        });
    }

    public static function activeCount(): int
    {
        return static::where('status', 'active')->count();
    }

    protected function casts(): array
    {
        return [
            'subscribed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }
}
