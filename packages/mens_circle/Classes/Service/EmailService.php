<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Service;

use BeardCoder\MensCircle\Domain\Model\NewsletterSubscription;
use BeardCoder\MensCircle\Domain\Model\Registration;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Mail\Mailer;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class EmailService
{
    private string $fromEmail;
    private string $fromName;

    public function __construct(
        private readonly Mailer $mailer,
        private readonly SiteFinder $siteFinder,
    ) {
        $this->fromEmail = $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] ?? 'noreply@example.com';
        $this->fromName = $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] ?? 'Männerkreis Niederbayern';
    }

    public function sendRegistrationConfirmation(Registration $registration): void
    {
        $event = $registration->getEvent();
        if ($event === null) {
            return;
        }

        $subject = sprintf(
            'Anmeldungsbestätigung: %s',
            $event->getTitle(),
        );

        $body = sprintf(
            "Hallo %s,\n\n"
            . "Deine Anmeldung für die Veranstaltung \"%s\" wurde erfolgreich registriert.\n\n"
            . "Veranstaltung: %s\n"
            . "Datum: %s\n"
            . "Ort: %s\n\n"
            . "Danke für deine Teilnahme!\n\n"
            . "Mit freundlichen Grüßen,\n"
            . "Männerkreis Niederbayern",
            $registration->getFirstName(),
            $event->getTitle(),
            $event->getTitle(),
            $event->getEventDate()?->format('d.m.Y, H:i') ?? '',
            $event->getLocation(),
        );

        $this->send(
            $registration->getEmail(),
            $subject,
            $body,
        );
    }

    public function sendNewsletterConfirmation(NewsletterSubscription $subscription): void
    {
        $subject = 'Bitte bestätige deine Newsletter-Anmeldung';

        $confirmationUrl = $this->getBaseUrl() . sprintf('/newsletter/confirm/%s', $subscription->getConfirmationToken());

        $body = sprintf(
            "Hallo %s,\n\n"
            . "Bitte bestätige deine Newsletter-Anmeldung, indem du auf den folgenden Link klickst:\n\n"
            . "%s\n\n"
            . "Dieser Link ist 24 Stunden gültig.\n\n"
            . "Falls du dich nicht angemeldet hast, kannst du diese Mail ignorieren.\n\n"
            . "Mit freundlichen Grüßen,\n"
            . "Männerkreis Niederbayern",
            $subscription->getFirstName() ?: 'Nutzer',
            $confirmationUrl,
        );

        $this->send(
            $subscription->getEmail(),
            $subject,
            $body,
        );
    }

    public function sendNewsletter(NewsletterSubscription $subscription, string $subject, string $message): void
    {
        $unsubscribeUrl = $this->getBaseUrl() . sprintf('/newsletter/unsubscribe/%s', $subscription->getUnsubscribeToken());
        $firstName = $subscription->getFirstName() ?: 'Nutzer';

        // HTML-Version
        $htmlBody = sprintf(
            '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>%s</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #2c3e50; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #fff; padding: 30px; border: 1px solid #ddd; }
        .footer { background: #f4f4f4; padding: 20px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 5px 5px; }
        .button { display: inline-block; padding: 12px 24px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
        .unsubscribe { margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666; }
        h1, h2, h3 { color: #2c3e50; }
        img { max-width: 100%%; height: auto; }
        table { width: 100%%; border-collapse: collapse; }
        table td, table th { padding: 10px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Männerkreis Niederbayern</h1>
    </div>
    <div class="content">
        <p>Hallo %s,</p>
        %s
    </div>
    <div class="footer">
        <div class="unsubscribe">
            <p>Du erhältst diese E-Mail, weil du den Newsletter des Männerkreis Niederbayern abonniert hast.</p>
            <p><a href="%s" style="color: #666;">Newsletter abmelden</a></p>
        </div>
        <p>Mit freundlichen Grüßen,<br>Männerkreis Niederbayern</p>
    </div>
</body>
</html>',
            htmlspecialchars($subject),
            $firstName,
            $message, // Message already contains HTML from RTE
            $unsubscribeUrl,
        );

        // Plaintext-Version (HTML entfernen)
        $plainBody = sprintf(
            "Hallo %s,\n\n"
            . "%s\n\n"
            . "---\n\n"
            . "Du erhältst diese E-Mail, weil du den Newsletter des Männerkreis Niederbayern abonniert hast.\n"
            . "Wenn du dich abmelden möchtest, besuche folgenden Link:\n"
            . "%s\n\n"
            . "Mit freundlichen Grüßen,\n"
            . "Männerkreis Niederbayern",
            $firstName,
            strip_tags($message),
            $unsubscribeUrl,
        );

        $this->sendHtml(
            $subscription->getEmail(),
            $subject,
            $htmlBody,
            $plainBody,
        );
    }

    private function send(string $to, string $subject, string $body): void
    {
        $mail = GeneralUtility::makeInstance(MailMessage::class);
        $mail
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to($to)
            ->subject($subject)
            ->text($body);

        $this->mailer->send($mail);
    }

    private function sendHtml(string $to, string $subject, string $htmlBody, string $plainBody): void
    {
        $mail = GeneralUtility::makeInstance(MailMessage::class);
        $mail
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to($to)
            ->subject($subject)
            ->text($plainBody)
            ->html($htmlBody);

        $this->mailer->send($mail);
    }

    private function getBaseUrl(): string
    {
        try {
            $sites = $this->siteFinder->getAllSites();
            $site = reset($sites);

            return $site !== false ? rtrim((string) $site->getBase(), '/') : '';
        } catch (\Exception) {
            return '';
        }
    }
}
