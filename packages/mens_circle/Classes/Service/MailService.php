<?php

declare(strict_types=1);

namespace MarkusSommer\MensCircle\Service;

use MarkusSommer\MensCircle\Domain\Model\Event;
use MarkusSommer\MensCircle\Domain\Model\NewsletterSubscription;
use MarkusSommer\MensCircle\Domain\Model\Registration;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class MailService
{
    private readonly LoggerInterface $logger;

    public function __construct()
    {
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(self::class);
    }

    /**
     * @param array<string, mixed> $settings
     */
    public function sendEventRegistrationConfirmation(Registration $registration, Event $event, array $settings): void
    {
        $participant = $registration->getParticipant();
        if ($participant === null || $participant->getEmail() === '') {
            return;
        }

        $this->sendEventRegistrationConfirmationFromData(
            [
                'participantEmail' => $participant->getEmail(),
                'participantFirstName' => $participant->getFirstName(),
                'participantLastName' => $participant->getLastName(),
                'eventTitle' => $event->getTitle(),
                'eventDate' => $event->getEventDate()?->format('Y-m-d H:i:s') ?? '',
                'eventStartTime' => $event->getStartTime()?->format('H:i:s') ?? '',
                'eventLocation' => $event->getLocation(),
            ],
            $settings
        );
    }

    /**
     * @param array{
     *   participantEmail: string,
     *   participantFirstName: string,
     *   participantLastName: string,
     *   eventTitle: string,
     *   eventDate: string,
     *   eventStartTime: string,
     *   eventLocation: string
     * } $notificationData
     * @param array<string, mixed> $settings
     */
    public function sendEventRegistrationConfirmationFromData(array $notificationData, array $settings): bool
    {
        $participantEmail = strtolower(trim((string) ($notificationData['participantEmail'] ?? '')));
        if ($participantEmail === '' || !filter_var($participantEmail, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $firstName = trim((string) ($notificationData['participantFirstName'] ?? ''));
        $lastName = trim((string) ($notificationData['participantLastName'] ?? ''));
        $eventTitle = trim((string) ($notificationData['eventTitle'] ?? ''));
        $eventLocation = trim((string) ($notificationData['eventLocation'] ?? ''));

        $siteName = (string) ($settings['siteName'] ?? 'Männerkreis');
        $subject = sprintf('Anmeldung bestaetigt: %s', $eventTitle !== '' ? $eventTitle : 'Männerkreis');

        $date = $this->formatDate((string) ($notificationData['eventDate'] ?? ''));
        $startTime = $this->formatTime((string) ($notificationData['eventStartTime'] ?? ''));

        $text = implode("\n", [
            'Servus ' . ($firstName !== '' ? $firstName : 'du'),
            '',
            'deine Anmeldung war erfolgreich.',
            '',
            sprintf('Termin: %s am %s%s', $eventTitle, $date !== '' ? $date : 'tba', $startTime !== '' ? ' um ' . $startTime . ' Uhr' : ''),
            'Ort: ' . ($eventLocation !== '' ? $eventLocation : '-'),
            '',
            $siteName,
        ]);

        $html = sprintf(
            '<p>Servus %s,</p><p>deine Anmeldung war erfolgreich.</p><p><strong>Termin:</strong> %s am %s%s<br><strong>Ort:</strong> %s</p><p>%s</p>',
            htmlspecialchars($firstName !== '' ? $firstName : 'du', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            htmlspecialchars($eventTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            htmlspecialchars($date !== '' ? $date : 'tba', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            $startTime !== '' ? ' um ' . htmlspecialchars($startTime, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ' Uhr' : '',
            htmlspecialchars($eventLocation !== '' ? $eventLocation : '-', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            htmlspecialchars($siteName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        );

        return $this->sendMail(
            $participantEmail,
            trim($firstName . ' ' . $lastName),
            $subject,
            $text,
            $html,
            $settings
        );
    }

    /**
     * @param array{
     *   participantEmail: string,
     *   participantFirstName: string,
     *   participantLastName: string,
     *   eventTitle: string,
     *   eventDate: string,
     *   eventStartTime: string,
     *   eventLocation: string
     * } $notificationData
     * @param array<string, mixed> $settings
     */
    public function sendEventReminderFromData(array $notificationData, array $settings): bool
    {
        $participantEmail = strtolower(trim((string) ($notificationData['participantEmail'] ?? '')));
        if ($participantEmail === '' || !filter_var($participantEmail, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $firstName = trim((string) ($notificationData['participantFirstName'] ?? ''));
        $lastName = trim((string) ($notificationData['participantLastName'] ?? ''));
        $eventTitle = trim((string) ($notificationData['eventTitle'] ?? ''));
        $eventLocation = trim((string) ($notificationData['eventLocation'] ?? ''));

        $siteName = (string) ($settings['siteName'] ?? 'Männerkreis');
        $subject = sprintf('Erinnerung: %s', $eventTitle !== '' ? $eventTitle : 'Männerkreis');

        $date = $this->formatDate((string) ($notificationData['eventDate'] ?? ''));
        $startTime = $this->formatTime((string) ($notificationData['eventStartTime'] ?? ''));

        $text = implode("\n", [
            'Servus ' . ($firstName !== '' ? $firstName : 'du'),
            '',
            'kurze Erinnerung an den Männerkreis Termin.',
            '',
            sprintf('Termin: %s am %s%s', $eventTitle, $date !== '' ? $date : 'tba', $startTime !== '' ? ' um ' . $startTime . ' Uhr' : ''),
            'Ort: ' . ($eventLocation !== '' ? $eventLocation : '-'),
            '',
            'Wir freuen uns auf dich.',
            '',
            $siteName,
        ]);

        $html = sprintf(
            '<p>Servus %s,</p><p>kurze Erinnerung an den Männerkreis Termin.</p><p><strong>Termin:</strong> %s am %s%s<br><strong>Ort:</strong> %s</p><p>Wir freuen uns auf dich.</p><p>%s</p>',
            htmlspecialchars($firstName !== '' ? $firstName : 'du', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            htmlspecialchars($eventTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            htmlspecialchars($date !== '' ? $date : 'tba', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            $startTime !== '' ? ' um ' . htmlspecialchars($startTime, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ' Uhr' : '',
            htmlspecialchars($eventLocation !== '' ? $eventLocation : '-', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            htmlspecialchars($siteName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        );

        return $this->sendMail(
            $participantEmail,
            trim($firstName . ' ' . $lastName),
            $subject,
            $text,
            $html,
            $settings
        );
    }

    /**
     * @param array<string, mixed> $settings
     */
    public function sendNewsletterWelcome(NewsletterSubscription $subscription, string $unsubscribeUrl, array $settings): void
    {
        $participant = $subscription->getParticipant();
        if ($participant === null || $participant->getEmail() === '') {
            return;
        }

        $siteName = (string) ($settings['siteName'] ?? 'Männerkreis');
        $subject = sprintf('%s Newsletter Anmeldung', $siteName);

        $text = implode("\n", [
            'Danke für deine Anmeldung zum Newsletter.',
            '',
            'Abmelden: ' . $unsubscribeUrl,
            '',
            $siteName,
        ]);

        $html = sprintf(
            '<p>Danke für deine Anmeldung zum Newsletter.</p><p><a href="%s">Hier abmelden</a>, falls du keine E-Mails mehr möchtest.</p><p>%s</p>',
            htmlspecialchars($unsubscribeUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            htmlspecialchars($siteName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        );

        $this->sendMail($participant->getEmail(), $participant->getFullName(), $subject, $text, $html, $settings);
    }

    /**
     * @param array<string, mixed> $settings
     */
    public function sendNewsletterBroadcast(
        string $toEmail,
        string $toName,
        string $subject,
        string $content,
        string $unsubscribeUrl,
        array $settings
    ): bool {
        if ($toEmail === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $siteName = (string) ($settings['siteName'] ?? 'Männerkreis');
        $normalizedContent = trim($content);

        if ($normalizedContent === '') {
            return false;
        }

        $text = trim(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $normalizedContent)));
        $html = $normalizedContent;

        if ($unsubscribeUrl !== '') {
            $text .= "\n\nAbmelden: " . $unsubscribeUrl;
            $html .= sprintf(
                '<p><a href="%s">Newsletter abbestellen</a></p>',
                htmlspecialchars($unsubscribeUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            );
        }

        $text .= "\n\n" . $siteName;
        $html .= sprintf(
            '<p>%s</p>',
            htmlspecialchars($siteName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        );

        return $this->sendMail($toEmail, $toName, $subject, $text, $html, $settings);
    }

    /**
     * @param array<string, mixed> $settings
     */
    private function sendMail(
        string $toEmail,
        string $toName,
        string $subject,
        string $textBody,
        string $htmlBody,
        array $settings
    ): bool {
        $fromAddress = (string) ($settings['contactEmail'] ?? $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] ?? '');
        $fromName = (string) ($settings['siteName'] ?? $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] ?? 'Männerkreis');

        if ($fromAddress === '') {
            $this->logger->warning('Mail skipped: no sender address configured.');

            return false;
        }

        try {
            $mail = GeneralUtility::makeInstance(MailMessage::class);
            $mail
                ->to(new Address($toEmail, $toName !== '' ? $toName : $toEmail))
                ->from(new Address($fromAddress, $fromName))
                ->subject($subject)
                ->text($textBody)
                ->html($htmlBody)
                ->send();

            return true;
        } catch (\Throwable $throwable) {
            $this->logger->error('Mail delivery failed', [
                'to' => $toEmail,
                'subject' => $subject,
                'error' => $throwable->getMessage(),
            ]);

            return false;
        }
    }

    private function formatDate(string $dateValue): string
    {
        $dateValue = trim($dateValue);
        if ($dateValue === '' || $dateValue === '0000-00-00 00:00:00') {
            return '';
        }

        try {
            return (new \DateTimeImmutable($dateValue))->format('d.m.Y');
        } catch (\Throwable) {
            return '';
        }
    }

    private function formatTime(string $timeValue): string
    {
        $timeValue = trim($timeValue);
        if ($timeValue === '' || $timeValue === '00:00:00') {
            return '';
        }

        try {
            return (new \DateTimeImmutable($timeValue))->format('H:i');
        } catch (\Throwable) {
            return '';
        }
    }
}
