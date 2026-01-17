<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventRegistration extends Model
{
    use HasFactory;
    use ClearsResponseCache;
    use SoftDeletes;

    protected $fillable = [
        'event_id',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'privacy_accepted',
        'status',
        'confirmed_at',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public static function confirmedCount(): int
    {
        return static::where('status', 'confirmed')->count();
    }

    protected function casts(): array
    {
        return [
            'privacy_accepted' => 'boolean',
            'confirmed_at' => 'datetime',
        ];
    }
}
