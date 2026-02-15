<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Sentry;

use TYPO3\CMS\Core\Error\ProductionExceptionHandler;

class SentryProductionExceptionHandler extends ProductionExceptionHandler
{
    public function __construct()
    {
        SentryService::initialize();
        parent::__construct();
    }

    public function handleException(\Throwable $exception): void
    {
        $ignoredCodes = array_merge(self::IGNORED_EXCEPTION_CODES, self::IGNORED_HMAC_EXCEPTION_CODES);

        if (!in_array($exception->getCode(), $ignoredCodes, true)) {
            SentryService::captureException($exception);
        }

        parent::handleException($exception);
    }
}
