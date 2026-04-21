<?php

declare(strict_types=1);

namespace App\Services\Ai;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

final class AiAuditLogger
{
    /**
     * @param array<string, mixed> $context
     */
    public function log(string $action, array $context = []): void
    {
        /** @var Authenticatable|null $user */
        $user = Auth::user();

        Log::info('AI management action executed', [
            'action' => $action,
            'actor_id' => $user?->getAuthIdentifier(),
            'actor_email' => $user instanceof \App\Models\User ? $user->email : null,
            'via' => request()?->expectsJson() ? 'http' : 'mcp',
            'ip' => request()?->ip(),
            ...$context,
        ]);
    }
}
