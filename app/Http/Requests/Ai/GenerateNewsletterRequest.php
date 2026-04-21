<?php

declare(strict_types=1);

namespace App\Http\Requests\Ai;

use App\Http\Requests\JsonFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

final class GenerateNewsletterRequest extends JsonFormRequest
{
    /**
     * @return array<string, list<ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'prompt' => ['nullable', 'string', 'max:5000'],
            'subject' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
        ];
    }
}
