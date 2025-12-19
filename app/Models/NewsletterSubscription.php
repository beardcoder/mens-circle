<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewsletterSubscription extends Model
{
    protected $fillable = [
        'email',
        'status',
        'token',
        'subscribed_at',
        'unsubscribed_at',
    ];

    protected $casts = [
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($subscription) {
            $subscription->token = Str::random(64);
            $subscription->subscribed_at = now();
        });
    }
}
