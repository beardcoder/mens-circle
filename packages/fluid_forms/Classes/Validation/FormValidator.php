<?php

declare(strict_types=1);

namespace BeardCoder\FluidForms\Validation;

/**
 * Declarative form validation similar to Laravel's FormRequest.
 *
 * Usage:
 *   $validator = new FormValidator($requestData, [
 *       'email'     => ['required', 'email'],
 *       'firstName' => ['required', 'minLength:2'],
 *       'phone'     => ['phone'],
 *       'notes'     => ['maxLength:500'],
 *       'privacy'   => ['required', 'accepted'],
 *   ]);
 *
 *   if ($validator->fails()) {
 *       return $validator->getErrors(); // ['email' => ['Dieses Feld ist erforderlich.']]
 *   }
 *
 *   $clean = $validator->validated(); // sanitized data
 */
final class FormValidator
{
    /** @var array<string, list<string>> */
    private array $errors = [];

    /** @var array<string, mixed> */
    private array $validatedData = [];

    /** @var array<string, string> Custom field labels */
    private array $labels = [];

    /**
     * @param array<string, mixed> $data Raw request data
     * @param array<string, list<string>> $rules Validation rules per field
     * @param array<string, string> $messages Custom error messages (field.rule => message)
     * @param array<string, string> $labels Custom field labels
     */
    public function __construct(
        private readonly array $data,
        private readonly array $rules,
        private readonly array $messages = [],
        array $labels = [],
    ) {
        $this->labels = $labels;
        $this->validate();
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function passes(): bool
    {
        return !$this->fails();
    }

    /**
     * @return array<string, list<string>>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return array<string, mixed> Sanitized, validated data
     */
    public function validated(): array
    {
        return $this->validatedData;
    }

    /**
     * Get a single validated value.
     */
    public function get(string $field, mixed $default = null): mixed
    {
        return $this->validatedData[$field] ?? $default;
    }

    private function validate(): void
    {
        foreach ($this->rules as $field => $fieldRules) {
            $value = $this->data[$field] ?? null;
            $isRequired = \in_array('required', $fieldRules, true);

            // If field is not required and empty, skip other validations
            if (!$isRequired && $this->isEmpty($value)) {
                $this->validatedData[$field] = $this->sanitize($value);
                continue;
            }

            foreach ($fieldRules as $rule) {
                $this->applyRule($field, $value, $rule);
            }

            // Only store validated data if no errors for this field
            if (!isset($this->errors[$field])) {
                $this->validatedData[$field] = $this->sanitize($value);
            }
        }
    }

    private function applyRule(string $field, mixed $value, string $rule): void
    {
        $parts = explode(':', $rule, 2);
        $ruleName = $parts[0];
        $parameter = $parts[1] ?? null;

        $passed = match ($ruleName) {
            'required' => !$this->isEmpty($value),
            'email' => $this->validateEmail($value),
            'minLength' => $this->validateMinLength($value, (int) $parameter),
            'maxLength' => $this->validateMaxLength($value, (int) $parameter),
            'numeric' => is_numeric($value),
            'integer' => filter_var($value, FILTER_VALIDATE_INT) !== false,
            'phone' => $this->validatePhone($value),
            'url' => filter_var((string) $value, FILTER_VALIDATE_URL) !== false,
            'accepted' => $this->validateAccepted($value),
            'in' => $this->validateIn($value, $parameter ?? ''),
            'regex' => $this->validateRegex($value, $parameter ?? ''),
            'min' => is_numeric($value) && (float) $value >= (float) $parameter,
            'max' => is_numeric($value) && (float) $value <= (float) $parameter,
            default => true,
        };

        if (!$passed) {
            $this->addError($field, $ruleName, $parameter);
        }
    }

    private function addError(string $field, string $ruleName, ?string $parameter): void
    {
        // Check for custom message
        $key = $field . '.' . $ruleName;
        if (isset($this->messages[$key])) {
            $this->errors[$field][] = $this->messages[$key];
            return;
        }

        $label = $this->labels[$field] ?? $this->humanize($field);

        $this->errors[$field][] = match ($ruleName) {
            'required' => \sprintf('%s ist erforderlich.', $label),
            'email' => 'Bitte gib eine gültige E-Mail-Adresse ein.',
            'minLength' => \sprintf('%s muss mindestens %s Zeichen lang sein.', $label, $parameter),
            'maxLength' => \sprintf('%s darf maximal %s Zeichen lang sein.', $label, $parameter),
            'numeric' => \sprintf('%s muss eine Zahl sein.', $label),
            'integer' => \sprintf('%s muss eine ganze Zahl sein.', $label),
            'phone' => 'Bitte gib eine gültige Telefonnummer ein.',
            'url' => 'Bitte gib eine gültige URL ein.',
            'accepted' => \sprintf('%s muss akzeptiert werden.', $label),
            'in' => \sprintf('%s enthält einen ungültigen Wert.', $label),
            'regex' => \sprintf('%s hat ein ungültiges Format.', $label),
            'min' => \sprintf('%s muss mindestens %s sein.', $label, $parameter),
            'max' => \sprintf('%s darf maximal %s sein.', $label, $parameter),
            default => \sprintf('%s ist ungültig.', $label),
        };
    }

    private function isEmpty(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        if (\is_string($value)) {
            return trim($value) === '';
        }

        if (\is_array($value)) {
            return $value === [];
        }

        return false;
    }

    private function validateEmail(mixed $value): bool
    {
        if ($this->isEmpty($value)) {
            return false;
        }

        $email = filter_var((string) $value, FILTER_SANITIZE_EMAIL);

        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function validateMinLength(mixed $value, int $min): bool
    {
        return mb_strlen(trim((string) $value)) >= $min;
    }

    private function validateMaxLength(mixed $value, int $max): bool
    {
        return mb_strlen(trim((string) $value)) <= $max;
    }

    private function validatePhone(mixed $value): bool
    {
        if ($this->isEmpty($value)) {
            return true; // Phone is optional by default
        }

        // Allow +, digits, spaces, dashes, parentheses
        return (bool) preg_match('/^\+?[\d\s\-()]{6,20}$/', trim((string) $value));
    }

    private function validateAccepted(mixed $value): bool
    {
        return \in_array($value, [true, 'true', 1, '1', 'on', 'yes', 'ja'], false);
    }

    private function validateIn(mixed $value, string $parameter): bool
    {
        $allowed = explode(',', $parameter);

        return \in_array((string) $value, $allowed, true);
    }

    private function validateRegex(mixed $value, string $pattern): bool
    {
        return (bool) preg_match($pattern, (string) $value);
    }

    private function sanitize(mixed $value): mixed
    {
        if (\is_string($value)) {
            return htmlspecialchars(trim($value), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        if (\is_array($value)) {
            return array_map($this->sanitize(...), $value);
        }

        return $value;
    }

    private function humanize(string $field): string
    {
        // camelCase to words
        $words = preg_replace('/([a-z])([A-Z])/', '$1 $2', $field);

        // snake_case to words
        $words = str_replace('_', ' ', (string) $words);

        return ucfirst(mb_strtolower($words));
    }
}
