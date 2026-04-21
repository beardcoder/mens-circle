<?php

declare(strict_types=1);

namespace App\Http\Requests\Ai;

use App\Http\Requests\JsonFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

final class SendNewsletterRequest extends JsonFormRequest
{
    /**
     * @return array<string, list<ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'confirm_send' => ['required', 'accepted'],
        ];
    }
}
