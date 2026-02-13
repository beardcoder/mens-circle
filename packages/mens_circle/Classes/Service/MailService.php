<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Service;

use BeardCoder\MensCircle\Domain\Model\Event;
use BeardCoder\MensCircle\Domain\Model\NewsletterSubscription;
use BeardCoder\MensCircle\Domain\Model\Registration;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Mime\Address;
use Throwable;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\View\TemplateView;

final class MailService
{
    private const MAIL_TEMPLATE_ROOT_PATH = 'EXT:mens_circle/Resources/Private/Templates/Mail/';
    private const MAIL_LAYOUT_ROOT_PATH = 'EXT:mens_circle/Resources/Private/Templates/Layouts/Mail/';

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
                'eventSlug' => $event->getSlug(),
                'eventDate' => $event->getEventDate()?->format('Y-m-d H:i:s') ?? '',
                'eventStartTime' => $event->getStartTime()?->format('H:i:s') ?? '',
                'eventLocation' => $event->getLocation(),
            ],
            $settings,
        );
    }

    /**
     * @param array{
     *   participantEmail: string,
     *   participantFirstName: string,
     *   participantLastName: string,
     *   eventTitle: string,
     *   eventSlug?: string,
     *   eventDate: string,
     *   eventStartTime: string,
     *   eventLocation: string
     * } $notificationData
     * @param array<string, mixed> $settings
     */
    public function sendEventRegistrationConfirmationFromData(array $notificationData, array $settings): bool
    {
        $participantEmail = strtolower(trim((string)($notificationData['participantEmail'] ?? '')));
        if ($participantEmail === '' || !filter_var($participantEmail, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $firstName = trim((string)($notificationData['participantFirstName'] ?? ''));
        $lastName = trim((string)($notificationData['participantLastName'] ?? ''));
        $eventTitle = trim((string)($notificationData['eventTitle'] ?? ''));
        $eventSlug = trim((string)($notificationData['eventSlug'] ?? ''));
        $eventLocation = trim((string)($notificationData['eventLocation'] ?? ''));

        $siteName = (string)($settings['siteName'] ?? 'Männerkreis');
        $subject = \sprintf('Anmeldung bestaetigt: %s', $eventTitle !== '' ? $eventTitle : 'Männerkreis');

        $date = $this->formatDate((string)($notificationData['eventDate'] ?? ''));
        $startTime = $this->formatTime((string)($notificationData['eventStartTime'] ?? ''));
        $eventTitleLabel = $eventTitle !== '' ? $eventTitle : 'Männerkreis';
        $dateLabel = $date !== '' ? $date : 'tba';
        $timeLabel = $startTime !== '' ? $startTime . ' Uhr' : 'offen';
        $locationLabel = $eventLocation !== '' ? $eventLocation : '-';
        $recipientName = $firstName !== '' ? $firstName : 'du';
        $eventUrl = $this->buildEventUrl($settings, $eventSlug);

        $text = implode("\n", [
            'Servus ' . $recipientName,
            '',
            'deine Anmeldung war erfolgreich.',
            '',
            \sprintf('Termin: %s am %s', $eventTitleLabel, $dateLabel),
            'Uhrzeit: ' . $timeLabel,
            'Ort: ' . $locationLabel,
            $eventUrl !== '' ? 'Termin öffnen: ' . $eventUrl : '',
            '',
            $siteName,
        ]);

        $html = $this->renderMailTemplate(
            'EventRegistration',
            array_merge(
                $this->resolveMailLayoutVariables($settings),
                [
                    'preheader' => 'Anmeldung bestätigt: ' . ($eventTitle !== '' ? $eventTitle : $siteName),
                    'recipientName' => $recipientName,
                    'eventTitle' => $eventTitleLabel,
                    'eventDate' => $dateLabel,
                    'eventTime' => $timeLabel,
                    'eventLocation' => $locationLabel,
                    'eventUrl' => $eventUrl,
                ],
            ),
        );

        return $this->sendMail(
            $participantEmail,
            trim($firstName . ' ' . $lastName),
            $subject,
            $text,
            $html,
            $settings,
        );
    }

    /**
     * @param array{
     *   participantEmail: string,
     *   participantFirstName: string,
     *   participantLastName: string,
     *   eventTitle: string,
     *   eventSlug?: string,
     *   eventDate: string,
     *   eventStartTime: string,
     *   eventLocation: string
     * } $notificationData
     * @param array<string, mixed> $settings
     */
    public function sendEventReminderFromData(array $notificationData, array $settings): bool
    {
        $participantEmail = strtolower(trim((string)($notificationData['participantEmail'] ?? '')));
        if ($participantEmail === '' || !filter_var($participantEmail, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $firstName = trim((string)($notificationData['participantFirstName'] ?? ''));
        $lastName = trim((string)($notificationData['participantLastName'] ?? ''));
        $eventTitle = trim((string)($notificationData['eventTitle'] ?? ''));
        $eventSlug = trim((string)($notificationData['eventSlug'] ?? ''));
        $eventLocation = trim((string)($notificationData['eventLocation'] ?? ''));

        $siteName = (string)($settings['siteName'] ?? 'Männerkreis');
        $subject = \sprintf('Erinnerung: %s', $eventTitle !== '' ? $eventTitle : 'Männerkreis');

        $date = $this->formatDate((string)($notificationData['eventDate'] ?? ''));
        $startTime = $this->formatTime((string)($notificationData['eventStartTime'] ?? ''));
        $eventTitleLabel = $eventTitle !== '' ? $eventTitle : 'Männerkreis';
        $dateLabel = $date !== '' ? $date : 'tba';
        $timeLabel = $startTime !== '' ? $startTime . ' Uhr' : 'offen';
        $locationLabel = $eventLocation !== '' ? $eventLocation : '-';
        $recipientName = $firstName !== '' ? $firstName : 'du';
        $eventUrl = $this->buildEventUrl($settings, $eventSlug);

        $text = implode("\n", [
            'Servus ' . $recipientName,
            '',
            'kurze Erinnerung an den Männerkreis Termin.',
            '',
            \sprintf('Termin: %s am %s', $eventTitleLabel, $dateLabel),
            'Uhrzeit: ' . $timeLabel,
            'Ort: ' . $locationLabel,
            $eventUrl !== '' ? 'Termin öffnen: ' . $eventUrl : '',
            '',
            'Wir freuen uns auf dich.',
            '',
            $siteName,
        ]);

        $html = $this->renderMailTemplate(
            'EventReminder',
            array_merge(
                $this->resolveMailLayoutVariables($settings),
                [
                    'preheader' => 'Erinnerung: ' . ($eventTitle !== '' ? $eventTitle : $siteName),
                    'recipientName' => $recipientName,
                    'eventTitle' => $eventTitleLabel,
                    'eventDate' => $dateLabel,
                    'eventTime' => $timeLabel,
                    'eventLocation' => $locationLabel,
                    'eventUrl' => $eventUrl,
                ],
            ),
        );

        return $this->sendMail(
            $participantEmail,
            trim($firstName . ' ' . $lastName),
            $subject,
            $text,
            $html,
            $settings,
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

        $siteName = (string)($settings['siteName'] ?? 'Männerkreis');
        $subject = \sprintf('%s Newsletter Anmeldung', $siteName);
        $recipientName = trim($participant->getFirstName()) !== '' ? trim($participant->getFirstName()) : 'du';

        $text = implode("\n", [
            'Danke für deine Anmeldung zum Newsletter.',
            '',
            'Abmelden: ' . $unsubscribeUrl,
            '',
            $siteName,
        ]);

        $html = $this->renderMailTemplate(
            'NewsletterWelcome',
            array_merge(
                $this->resolveMailLayoutVariables($settings, $unsubscribeUrl),
                [
                    'preheader' => 'Newsletter Anmeldung bestätigt',
                    'recipientName' => $recipientName,
                ],
            ),
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
        array $settings,
    ): bool {
        if ($toEmail === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $siteName = (string)($settings['siteName'] ?? 'Männerkreis');
        $normalizedContent = trim($this->sanitizeNewsletterContent($content));

        if ($normalizedContent === '') {
            return false;
        }

        $text = trim(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $normalizedContent)));
        $recipientName = trim($toName) !== '' ? trim($toName) : 'du';

        if ($unsubscribeUrl !== '') {
            $text .= "\n\nAbmelden: " . $unsubscribeUrl;
        }

        $text .= "\n\n" . $siteName;
        $html = $this->renderMailTemplate(
            'NewsletterBroadcast',
            array_merge(
                $this->resolveMailLayoutVariables($settings, $unsubscribeUrl),
                [
                    'preheader' => $subject,
                    'subject' => $subject,
                    'recipientName' => $recipientName,
                    'contentHtml' => $normalizedContent,
                ],
            ),
        );

        return $this->sendMail($toEmail, $toName, $subject, $text, $html, $settings);
    }

    /**
     * @param array<string, mixed> $settings
     * @return array{siteName: string, contactEmail: string, unsubscribeUrl: string}
     */
    private function resolveMailLayoutVariables(array $settings, string $unsubscribeUrl = ''): array
    {
        return [
            'siteName' => (string)($settings['siteName'] ?? 'Männerkreis'),
            'contactEmail' => trim((string)($settings['contactEmail'] ?? '')),
            'unsubscribeUrl' => trim($unsubscribeUrl),
        ];
    }

    /**
     * @param array<string, mixed> $variables
     */
    private function renderMailTemplate(string $templateName, array $variables): string
    {
        $view = new TemplateView();
        $templatePaths = $view->getRenderingContext()->getTemplatePaths();
        $templatePaths->setTemplateRootPaths([
            GeneralUtility::getFileAbsFileName(self::MAIL_TEMPLATE_ROOT_PATH),
        ]);
        $templatePaths->setLayoutRootPaths([
            GeneralUtility::getFileAbsFileName(self::MAIL_LAYOUT_ROOT_PATH),
        ]);

        $view->assignMultiple($variables);

        return $view->render($templateName);
    }

    /**
     * @param array<string, mixed> $settings
     */
    private function buildEventUrl(array $settings, string $eventSlug): string
    {
        $normalizedSlug = trim($eventSlug);
        if ($normalizedSlug === '') {
            return '';
        }

        $baseUrl = rtrim((string)($settings['baseUrl'] ?? ''), '/');
        if ($baseUrl === '') {
            return '';
        }

        return $baseUrl . '/event/' . ltrim($normalizedSlug, '/');
    }

    private function sanitizeNewsletterContent(string $content): string
    {
        $sanitized = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $content);
        if (!\is_string($sanitized)) {
            return '';
        }

        return trim($sanitized);
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
        array $settings,
    ): bool {
        $fromAddress = (string)($settings['contactEmail'] ?? $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] ?? '');
        $fromName = (string)($settings['siteName'] ?? $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] ?? 'Männerkreis');

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
        } catch (Throwable $throwable) {
            $this->logger->log(LogLevel::ERROR, 'Mail delivery failed', [
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
            return (new DateTimeImmutable($dateValue))->format('d.m.Y');
        } catch (Throwable) {
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
            return (new DateTimeImmutable($timeValue))->format('H:i');
        } catch (Throwable) {
            return '';
        }
    }
}
