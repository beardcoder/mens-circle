<?php

declare(strict_types=1);

namespace App\Http\Requests\Ai;

use App\Http\Requests\JsonFormRequest;

final class UpdateAiPageRequest extends JsonFormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255'],
            'meta' => ['sometimes', 'array'],
            'published_at' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
