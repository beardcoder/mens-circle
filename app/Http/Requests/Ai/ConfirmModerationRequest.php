<?php

declare(strict_types=1);

namespace App\Http\Requests\Ai;

use App\Http\Requests\JsonFormRequest;

final class ConfirmModerationRequest extends JsonFormRequest
{
    public function rules(): array
    {
        return [
            'confirm' => ['required', 'accepted'],
        ];
    }
}
