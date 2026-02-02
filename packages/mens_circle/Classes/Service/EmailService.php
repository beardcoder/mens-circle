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
