<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Sentry;

use Psr\Log\LogLevel;
use Sentry\Severity;
use function Sentry\captureException;
use function Sentry\captureMessage;

final class SentryService
{
    private static ?bool $initialized = null;

    public static function initialize(): bool
    {
        if (self::$initialized !== null) {
            return self::$initialized;
        }

        $dsn = self::getEnv('SENTRY_DSN');
        if ($dsn === null || $dsn === '') {
            self::$initialized = false;

            return false;
        }

        \Sentry\init([
            'dsn' => $dsn,
            'environment' => self::getEnv('SENTRY_ENVIRONMENT') ?? 'production',
            'release' => self::getEnv('SENTRY_RELEASE') ?? '',
            'traces_sample_rate' => (float) (self::getEnv('SENTRY_TRACES_SAMPLE_RATE') ?? 0.0),
            'send_default_pii' => false,
            'attach_stacktrace' => true,
            'error_types' => E_ALL & ~(E_NOTICE | E_DEPRECATED | E_USER_DEPRECATED),
        ]);

        self::$initialized = true;

        return true;
    }

    public static function isEnabled(): bool
    {
        return self::$initialized === true;
    }

    public static function captureException(\Throwable $exception): void
    {
        if (!self::isEnabled()) {
            return;
        }

        captureException($exception);
    }

    public static function captureMessage(string $message, string $logLevel = LogLevel::INFO): void
    {
        if (!self::isEnabled()) {
            return;
        }

        captureMessage($message, self::mapSeverity($logLevel));
    }

    private static function mapSeverity(string $logLevel): Severity
    {
        return match ($logLevel) {
            LogLevel::EMERGENCY, LogLevel::ALERT, LogLevel::CRITICAL => Severity::fatal(),
            LogLevel::ERROR => Severity::error(),
            LogLevel::WARNING => Severity::warning(),
            LogLevel::DEBUG => Severity::debug(),
            default => Severity::info(),
        };
    }

    private static function getEnv(string $key): ?string
    {
        $value = getenv($key);

        if ($value === false || $value === '') {
            return null;
        }

        return $value;
    }
}
