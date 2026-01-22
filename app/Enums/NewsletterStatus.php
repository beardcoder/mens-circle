<?php

declare(strict_types=1);

namespace App\Enums;

enum NewsletterStatus: string
{
    case Draft = 'draft';
    case Sending = 'sending';
    case Sent = 'sent';
}
