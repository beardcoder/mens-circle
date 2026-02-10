<?php

declare(strict_types=1);

namespace App\Checks;

use Illuminate\Support\Facades\Config;
use Override;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Throwable;

class MailHealthCheck extends Check
{
    #[Override]
    public function run(): Result
    {
        $result = Result::make();

        /** @var string $mailer */
        $mailer = Config::get('mail.default', 'smtp');

        if ($mailer === 'log' || $mailer === 'array') {
            return $result
                ->warning("Mail-Treiber ist '{$mailer}' (nicht für Produktion)")
                ->shortSummary($mailer);
        }

        if ($mailer !== 'smtp') {
            return $result
                ->ok()
                ->shortSummary($mailer);
        }

        try {
            /** @var string|null $host */
            $host = Config::get('mail.mailers.smtp.host');
            /** @var int|string|null $portRaw */
            $portRaw = Config::get('mail.mailers.smtp.port', 587);
            $port = (int) $portRaw;
            /** @var string $encryption */
            $encryption = Config::get('mail.mailers.smtp.encryption', 'tls');
            /** @var string|null $username */
            $username = Config::get('mail.mailers.smtp.username');
            /** @var string|null $password */
            $password = Config::get('mail.mailers.smtp.password');

            if (empty($host)) {
                return $result
                    ->failed('SMTP-Host nicht konfiguriert')
                    ->shortSummary('Nicht konfiguriert');
            }

            $scheme = match ($encryption) {
                'tls' => 'smtp',
                'ssl' => 'smtps',
                default => 'smtp',
            };

            $dsn = new Dsn($scheme, $host, $username, $password, $port,);

            $factory = new EsmtpTransportFactory();
            $transport = $factory->create($dsn);

            if ($transport instanceof EsmtpTransport) {
                $transport->start();
                $transport->stop();
            }

            return $result
                ->ok()
                ->shortSummary("{$host}:{$port}")
                ->meta([
                    'host' => $host,
                    'port' => $port,
                    'encryption' => $encryption,
                ]);
        } catch (Throwable $throwable) {
            return $result
                ->failed('SMTP-Verbindungsfehler: ' . $throwable->getMessage())
                ->shortSummary('Verbindungsfehler')
                ->meta([
'error' => $throwable->getMessage()
]);
        }
    }
}
