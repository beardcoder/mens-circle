<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\NewsletterStatus;
use Database\Factories\NewsletterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Override;

/**
 * @property string $subject
 * @property string $content
 * @property ?Carbon $sent_at
 * @property ?int $recipient_count
 * @property NewsletterStatus $status
 */
class Newsletter extends Model
{
    /** @use HasFactory<NewsletterFactory> */
    use HasFactory;

    protected $fillable = ['subject', 'content', 'sent_at', 'recipient_count', 'status', ];

    #[Override]
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
