<?php

declare(strict_types=1);

namespace App\Http\Requests\Ai;

use App\Http\Requests\JsonFormRequest;

final class SendNewsletterRequest extends JsonFormRequest
{
    public function rules(): array
    {
        return [
            'confirm_send' => ['required', 'accepted'],
        ];
    }
}
