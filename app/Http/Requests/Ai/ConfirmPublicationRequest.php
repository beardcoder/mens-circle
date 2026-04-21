<?php

declare(strict_types=1);

namespace App\Http\Requests\Ai;

use App\Http\Requests\JsonFormRequest;

final class ConfirmPublicationRequest extends JsonFormRequest
{
    public function rules(): array
    {
        return [
            'confirm' => ['required', 'accepted'],
            'is_published' => ['required', 'boolean'],
        ];
    }
}
