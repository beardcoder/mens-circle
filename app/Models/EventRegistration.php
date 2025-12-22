<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class EventRegistration extends Model
{
    use Notifiable;

    protected $fillable = [
        'event_id',
        'first_name',
        'last_name',
        'email',
        'phone_number', // Für zukünftige SMS-Benachrichtigungen
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
