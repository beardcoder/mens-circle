<?php

declare(strict_types=1);

namespace App\Http\Requests\Ai;

use App\Http\Requests\JsonFormRequest;

final class UpdatePageBlocksRequest extends JsonFormRequest
{
    public function rules(): array
    {
        return [
            'content_blocks' => ['required', 'array', 'min:1'],
            'content_blocks.*.type' => ['required', 'string', 'max:255'],
            'content_blocks.*.data' => ['required', 'array'],
        ];
    }
}
