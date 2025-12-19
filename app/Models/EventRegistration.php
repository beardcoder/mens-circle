<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRegistration extends Model
{
    protected $fillable = [
        'event_id',
        'first_name',
        'last_name',
        'email',
        'privacy_accepted',
        'status',
        'confirmed_at',
    ];

    protected $casts = [
        'privacy_accepted' => 'boolean',
        'confirmed_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
