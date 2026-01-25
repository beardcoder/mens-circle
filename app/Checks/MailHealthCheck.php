<?php

declare(strict_types=1);

namespace App\Checks;

use Illuminate\Support\Facades\Config;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Throwable;

class MailHealthCheck extends Check
{
    public function run(): Result
    {
        $result = Result::make();

        $mailer = Config::get('mail.default');

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
            $host = Config::get('mail.mailers.smtp.host');
            $port = Config::get('mail.mailers.smtp.port');
            $encryption = Config::get('mail.mailers.smtp.encryption');
            $username = Config::get('mail.mailers.smtp.username');
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

            $dsn = new Dsn(
                $scheme,
                $host,
                $username,
                $password,
                $port
            );

            $factory = new EsmtpTransportFactory();
            $transport = $factory->create($dsn);

            $transport->start();
            $transport->stop();

            return $result
                ->ok()
                ->shortSummary("{$host}:{$port}")
                ->meta([
                    'host' => $host,
                    'port' => $port,
                    'encryption' => $encryption,
                ]);
        } catch (Throwable $e) {
            return $result
                ->failed("SMTP-Verbindungsfehler: {$e->getMessage()}")
                ->shortSummary('Verbindungsfehler')
                ->meta(['error' => $e->getMessage()]);
        }
    }
}
