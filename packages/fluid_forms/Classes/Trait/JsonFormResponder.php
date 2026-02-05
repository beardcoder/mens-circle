<?php

declare(strict_types=1);

namespace BeardCoder\FluidForms\Trait;

use BeardCoder\FluidForms\Validation\FormValidator;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\JsonResponse;

/**
 * Trait for Extbase ActionControllers to handle AJAX form submissions.
 *
 * Automatically detects AJAX requests (Accept: application/json) and returns
 * structured JSON responses instead of HTML redirects.
 *
 * Usage in your controller:
 *
 *   use JsonFormResponder;
 *
 *   public function registerAction(Event $event): ResponseInterface
 *   {
 *       $validator = $this->validateForm($this->getFormData(), [
 *           'email'     => ['required', 'email'],
 *           'firstName' => ['required', 'minLength:2'],
 *       ]);
 *
 *       if ($validator->fails()) {
 *           return $this->validationErrorResponse($validator);
 *       }
 *
 *       // ... business logic ...
 *
 *       return $this->successResponse('Registration successful!');
 *   }
 */
trait JsonFormResponder
{
    /**
     * Check if the current request expects a JSON response (AJAX).
     */
    protected function isJsonRequest(): bool
    {
        $accept = $this->request->getHeaderLine('Accept');

        return str_contains($accept, 'application/json');
    }

    /**
     * Get parsed body data from the request.
     *
     * @return array<string, mixed>
     */
    protected function getFormData(): array
    {
        $contentType = $this->request->getHeaderLine('Content-Type');
        $body = $this->request->getParsedBody();

        // If content type is JSON, decode the body
        if (str_contains($contentType, 'application/json') && $body === null) {
            $rawBody = (string) $this->request->getBody();
            $decoded = json_decode($rawBody, true);

            return \is_array($decoded) ? $decoded : [];
        }

        return \is_array($body) ? $body : [];
    }

    /**
     * Validate form data with declarative rules.
     *
     * @param array<string, mixed> $data
     * @param array<string, list<string>> $rules
     * @param array<string, string> $messages Custom error messages
     * @param array<string, string> $labels Custom field labels
     */
    protected function validateForm(
        array $data,
        array $rules,
        array $messages = [],
        array $labels = [],
    ): FormValidator {
        return new FormValidator($data, $rules, $messages, $labels);
    }

    /**
     * Return a success JSON response.
     *
     * @param array<string, mixed> $data Additional data to include
     */
    protected function successResponse(
        string $message,
        array $data = [],
        ?string $redirect = null,
    ): ResponseInterface {
        $payload = [
            'success' => true,
            'message' => $message,
            ...$data,
        ];

        if ($redirect !== null) {
            $payload['redirect'] = $redirect;
        }

        return new JsonResponse($payload);
    }

    /**
     * Return a validation error JSON response.
     */
    protected function validationErrorResponse(FormValidator $validator): ResponseInterface
    {
        return new JsonResponse([
            'success' => false,
            'message' => 'Bitte überprüfe deine Eingaben.',
            'errors' => $validator->getErrors(),
        ], 422);
    }

    /**
     * Return a generic error JSON response.
     */
    protected function errorResponse(string $message, int $status = 400): ResponseInterface
    {
        return new JsonResponse([
            'success' => false,
            'message' => $message,
            'errors' => [],
        ], $status);
    }
}
