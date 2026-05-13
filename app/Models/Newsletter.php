<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\NewsletterStatus;
use Database\Factories\NewsletterFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\CarbonImmutable;
use Override;

/**
 * @property string $subject
 * @property string $content
 * @property ?CarbonImmutable $sent_at
 * @property ?int $recipient_count
 * @property NewsletterStatus $status
 */
#[Fillable(['subject', 'content', 'sent_at', 'recipient_count', 'status'])]
#[UseFactory(NewsletterFactory::class)]
class Newsletter extends Model
{
    /** @use HasFactory<NewsletterFactory> */
    use HasFactory;

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
