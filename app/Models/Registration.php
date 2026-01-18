<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RegistrationStatus;
use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Registration extends Model
{
    use ClearsResponseCache;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'participant_id',
        'event_id',
        'status',
        'registered_at',
        'cancelled_at',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function cancel(): void
    {
        $this->update([
            'status' => RegistrationStatus::Cancelled->value,
            'cancelled_at' => now(),
        ]);
    }

    public function markAsAttended(): void
    {
        $this->update([
            'status' => RegistrationStatus::Attended->value,
        ]);
    }

    public static function registeredCount(): int
    {
        return static::query()
            ->where('status', RegistrationStatus::Registered->value)
            ->count();
    }

    protected function casts(): array
    {
        return [
            'status' => RegistrationStatus::class,
            'registered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }
}
