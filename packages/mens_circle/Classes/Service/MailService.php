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

        $siteName = (string) ($settings['siteName'] ?? 'Männerkreis');
        $subject = sprintf('Anmeldung bestätigt: %s', $event->getTitle());

        $date = $event->getEventDate()?->format('d.m.Y') ?? 'tba';
        $startTime = $event->getStartTime()?->format('H:i') ?? '--:--';

        $text = implode("\n", [
            'Servus ' . ($participant->getFirstName() !== '' ? $participant->getFirstName() : 'du'),
            '',
            'deine Anmeldung war erfolgreich.',
            '',
            sprintf('Termin: %s am %s um %s Uhr', $event->getTitle(), $date, $startTime),
            'Ort: ' . $event->getLocation(),
            '',
            $siteName,
        ]);

        $html = sprintf(
            '<p>Servus %s,</p><p>deine Anmeldung war erfolgreich.</p><p><strong>Termin:</strong> %s am %s um %s Uhr<br><strong>Ort:</strong> %s</p><p>%s</p>',
            htmlspecialchars($participant->getFirstName() !== '' ? $participant->getFirstName() : 'du', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            htmlspecialchars($event->getTitle(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            htmlspecialchars($date, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            htmlspecialchars($startTime, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            htmlspecialchars($event->getLocation(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            htmlspecialchars($siteName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        );

        $this->sendMail($participant->getEmail(), $participant->getFullName(), $subject, $text, $html, $settings);
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
}
