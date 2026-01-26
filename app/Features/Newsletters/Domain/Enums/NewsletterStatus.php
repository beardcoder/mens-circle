<?php

declare(strict_types=1);

namespace App\Features\Newsletters\Domain\Enums;

enum NewsletterStatus: string
{
    case Draft = 'draft';
    case Sending = 'sending';
    case Sent = 'sent';
}
