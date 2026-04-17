<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

final class EventRegistrationRequest extends JsonFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'event_id' => ['required', Rule::exists('events', 'id')->whereNull('deleted_at')],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'privacy' => ['required', 'accepted'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'Bitte gib deinen Vornamen ein.',
            'last_name.required' => 'Bitte gib deinen Nachnamen ein.',
            'email.required' => 'Bitte gib eine gültige E-Mail-Adresse ein.',
            'email.email' => 'Bitte gib eine gültige E-Mail-Adresse ein.',
            'privacy.required' => 'Bitte bestätige die Datenschutzerklärung.',
            'privacy.accepted' => 'Bitte bestätige die Datenschutzerklärung.',
        ];
    }

    /**
     * @return array{event_id: int, first_name: string, last_name: string, email: string, phone_number: ?string, privacy: bool}
     */
    public function registrationData(): array
    {
        /** @var array{event_id: int, first_name: string, last_name: string, email: string, phone_number: ?string, privacy: bool} */
        return $this->validated();
    }
}
