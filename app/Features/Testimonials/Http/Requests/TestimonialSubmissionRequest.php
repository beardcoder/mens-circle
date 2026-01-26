<?php

declare(strict_types=1);

namespace App\Features\Testimonials\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use PhpStaticAnalysis\Attributes\Returns;

class TestimonialSubmissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    #[Returns('array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>')]
    public function rules(): array
    {
        return [
            'quote' => ['required', 'string', 'min:10', 'max:1000'],
            'author_name' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'privacy' => ['required', 'accepted'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    #[Returns('array<string, string>')]
    public function messages(): array
    {
        return [
            'quote.required' => 'Bitte teile deine Erfahrung mit uns.',
            'quote.min' => 'Deine Erfahrung sollte mindestens 10 Zeichen lang sein.',
            'quote.max' => 'Deine Erfahrung darf maximal 1000 Zeichen lang sein.',
            'email.required' => 'Bitte gib eine gültige E-Mail-Adresse ein.',
            'email.email' => 'Bitte gib eine gültige E-Mail-Adresse ein.',
            'privacy.required' => 'Bitte bestätige die Datenschutzerklärung.',
            'privacy.accepted' => 'Bitte bestätige die Datenschutzerklärung.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $validator->errors()->first(),
        ], 422));
    }
}
