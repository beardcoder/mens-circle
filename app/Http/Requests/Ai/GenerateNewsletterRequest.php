<?php

declare(strict_types=1);

namespace App\Http\Requests\Ai;

use App\Http\Requests\JsonFormRequest;

final class GenerateNewsletterRequest extends JsonFormRequest
{
    public function rules(): array
    {
        return [
            'prompt' => ['nullable', 'string', 'max:5000'],
            'subject' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
        ];
    }
}
