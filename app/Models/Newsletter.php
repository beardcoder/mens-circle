<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        ];
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }
}
