<?php

declare(strict_types=1);

namespace App\Http\Requests\Ai;

use App\Http\Requests\JsonFormRequest;

final class UpdateGeneralSettingsRequest extends JsonFormRequest
{
    public function rules(): array
    {
        return [
            'site_name' => ['sometimes', 'string', 'max:255'],
            'site_tagline' => ['sometimes', 'string', 'max:255'],
            'site_description' => ['sometimes', 'string', 'max:500'],
            'contact_email' => ['sometimes', 'email', 'max:255'],
            'contact_phone' => ['sometimes', 'nullable', 'string', 'max:255'],
            'location' => ['sometimes', 'string', 'max:255'],
            'whatsapp_community_link' => ['sometimes', 'nullable', 'url', 'max:500'],
            'social_links' => ['sometimes', 'nullable', 'array'],
            'footer_text' => ['sometimes', 'string'],
            'event_default_max_participants' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
