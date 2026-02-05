<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Service;

use BeardCoder\MensCircle\Domain\Model\Registration;
use TYPO3\CMS\Core\Http\RequestFactory;

final class SmsService
{
    private const SEVEN_IO_API_URL = 'https://api.seven.io/api/sms';

    public function __construct(
        private readonly RequestFactory $requestFactory,
    ) {}

    public function sendRegistrationConfirmation(Registration $registration): void
    {
        $phone = $registration->getPhone();
        if (empty($phone)) {
            return;
        }

        $event = $registration->getEvent();
        if ($event === null) {
            return;
        }

        $apiKey = $GLOBALS['TYPO3_CONF_VARS']['EXTENSION']['mens_circle']['sms']['apiKey'] ?? '';
        $senderName = $GLOBALS['TYPO3_CONF_VARS']['EXTENSION']['mens_circle']['sms']['senderName'] ?? 'Maennerkreis';

        if (empty($apiKey)) {
            return;
        }

        $normalizedPhone = $this->normalizePhoneNumber($phone);
        if ($normalizedPhone === '') {
            return;
        }

        $message = \sprintf(
            'Anmeldung bestätigt: %s am %s in %s. - Männerkreis',
            $event->getTitle(),
            $event->getEventDate()?->format('d.m.') ?? '',
            $event->getLocation(),
        );

        $this->sendSms($apiKey, $senderName, $normalizedPhone, $message);
    }

    private function sendSms(string $apiKey, string $sender, string $to, string $message): void
    {
        $additionalOptions = [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode(':' . $apiKey),
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => http_build_query([
                'to' => $to,
                'from' => $sender,
                'message' => $message,
            ]),
        ];

        $this->requestFactory->request(self::SEVEN_IO_API_URL, 'POST', $additionalOptions);
    }

    private function normalizePhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        if (str_starts_with($phone, '00')) {
            $phone = '+' . substr($phone, 2);
        } elseif (str_starts_with($phone, '0')) {
            $phone = '+49' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '+')) {
            $phone = '+49' . $phone;
        }

        return \strlen($phone) >= 10 ? $phone : '';
    }
}
