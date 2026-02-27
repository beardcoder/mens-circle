<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Override;

/**
 * @property int $registration_id
 * @property int $event_id
 * @property string $channel
 * @property Carbon $notified_at
 */
class EventNotificationLog extends Model
{
    protected $fillable = ['registration_id', 'event_id', 'channel', 'notified_at'];

    /**
     * @return BelongsTo<Registration, $this>
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'notified_at' => 'datetime',
        ];
    }
}
