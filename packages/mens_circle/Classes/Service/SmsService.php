<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Service;

use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Throwable;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Http\RequestFactory;

final class SmsService
{
    public function __construct(
        private readonly RequestFactory $requestFactory,
        private readonly ExtensionConfiguration $extensionConfiguration,
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * @param array{
     *   participantPhone: string,
     *   participantFirstName: string,
     *   eventTitle: string,
     *   eventDate: string,
     *   eventStartTime: string
     * } $notificationData
     * @param array<string, mixed> $settings
     */
    public function sendRegistrationConfirmation(array $notificationData, array $settings): bool
    {
        $phone = trim($notificationData['participantPhone']);
        if ($phone === '') {
            return false;
        }

        $firstName = trim($notificationData['participantFirstName']);
        $eventTitle = trim($notificationData['eventTitle']);
        $eventDate = $this->formatDate($notificationData['eventDate']);
        $eventTime = $this->formatTime($notificationData['eventStartTime']);

        $messageText = trim(\sprintf(
            'Servus %s, deine Anmeldung fuer "%s" am %s%s ist bestaetigt.',
            $firstName !== '' ? $firstName : 'du',
            $eventTitle !== '' ? $eventTitle : 'den Maennerkreis',
            $eventDate !== '' ? $eventDate : 'dem naechsten Termin',
            $eventTime !== '' ? ' um ' . $eventTime . ' Uhr' : '',
        ));

        return $this->sendSms($phone, $messageText, $settings);
    }

    /**
     * @param array{
     *   participantPhone: string,
     *   participantFirstName: string,
     *   eventTitle: string,
     *   eventDate: string,
     *   eventStartTime: string
     * } $notificationData
     * @param array<string, mixed> $settings
     */
    public function sendReminder(array $notificationData, array $settings): bool
    {
        $phone = trim($notificationData['participantPhone']);
        if ($phone === '') {
            return false;
        }

        $firstName = trim($notificationData['participantFirstName']);
        $eventTitle = trim($notificationData['eventTitle']);
        $eventDate = $this->formatDate($notificationData['eventDate']);
        $eventTime = $this->formatTime($notificationData['eventStartTime']);

        $messageText = trim(\sprintf(
            'Erinnerung %s: "%s" ist am %s%s.',
            $firstName !== '' ? $firstName : '',
            $eventTitle !== '' ? $eventTitle : 'Maennerkreis',
            $eventDate !== '' ? $eventDate : 'Termin',
            $eventTime !== '' ? ' um ' . $eventTime . ' Uhr' : '',
        ));

        return $this->sendSms($phone, $messageText, $settings);
    }

    /**
     * @param array<string, mixed> $settings
     */
    private function sendSms(string $phone, string $messageText, array $settings): bool
    {
        $configuration = $this->resolveSmsConfiguration($settings);
        if (!$configuration['enabled']) {
            return false;
        }

        if ($configuration['apiKey'] === '') {
            $this->logger->warning('SMS skipped: no API key configured.');

            return false;
        }

        try {
            $response = $this->requestFactory->request(
                'https://gateway.seven.io/api/sms',
                'POST',
                [
                    'headers' => [
                        'X-Api-Key' => $configuration['apiKey'],
                        'Accept' => 'application/json',
                    ],
                    'form_params' => [
                        'to' => $phone,
                        'from' => $configuration['sender'],
                        'text' => $messageText,
                    ],
                    'timeout' => 10.0,
                ],
            );

            if ($response->getStatusCode() >= 400) {
                $this->logger->warning('SMS delivery failed', [
                    'phone' => $phone,
                    'statusCode' => $response->getStatusCode(),
                ]);

                return false;
            }

            return true;
        } catch (Throwable $throwable) {
            $this->logger->log(LogLevel::ERROR, 'SMS delivery failed', [
                'phone' => $phone,
                'error' => $throwable->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * @param array<string, mixed> $settings
     * @return array{enabled: bool, apiKey: string, sender: string}
     */
    private function resolveSmsConfiguration(array $settings): array
    {
        $extensionConfiguration = [];
        try {
            $extensionConfiguration = $this->extensionConfiguration->get('mens_circle');
        } catch (Throwable) {
            $extensionConfiguration = [];
        }

        $enabled = $this->toBool($settings['smsEnabled'] ?? $extensionConfiguration['smsEnabled'] ?? false);
        $apiKey = trim((string)($extensionConfiguration['smsApiKey'] ?? getenv('MENSCIRCLE_SMS_API_KEY') ?: ''));
        $sender = trim((string)($settings['smsSender'] ?? $extensionConfiguration['smsSender'] ?? 'MensCircle'));
        if ($sender === '') {
            $sender = 'MensCircle';
        }

        return [
            'enabled' => $enabled,
            'apiKey' => $apiKey,
            'sender' => $sender,
        ];
    }

    private function formatDate(mixed $dateValue): string
    {
        $dateString = trim((string)$dateValue);
        if ($dateString === '' || $dateString === '0000-00-00 00:00:00') {
            return '';
        }

        try {
            return (new DateTimeImmutable($dateString))->format('d.m.Y');
        } catch (Throwable) {
            return '';
        }
    }

    private function formatTime(mixed $timeValue): string
    {
        $timeString = trim((string)$timeValue);
        if ($timeString === '' || $timeString === '00:00:00') {
            return '';
        }

        try {
            return (new DateTimeImmutable($timeString))->format('H:i');
        } catch (Throwable) {
            return '';
        }
    }

    private function toBool(mixed $value): bool
    {
        if (\is_bool($value)) {
            return $value;
        }

        $normalized = strtolower(trim((string)$value));

        return \in_array($normalized, ['1', 'true', 'yes', 'on'], true);
    }
}
