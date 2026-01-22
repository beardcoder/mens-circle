<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\NewsletterStatus;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $subject
 * @property string $content
 * @property ?\Illuminate\Support\Carbon $sent_at
 * @property ?int $recipient_count
 * @property NewsletterStatus $status
 */
class Newsletter extends Model
{
    protected $fillable = [
        'subject',
        'content',
        'sent_at',
        'recipient_count',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'status' => NewsletterStatus::class,
        ];
    }

    public function isSent(): bool
    {
        return $this->status === NewsletterStatus::Sent;
    }

    public function isDraft(): bool
    {
        return $this->status === NewsletterStatus::Draft;
    }
}
