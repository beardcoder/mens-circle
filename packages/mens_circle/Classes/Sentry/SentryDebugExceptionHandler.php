<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Sentry;

use TYPO3\CMS\Core\Error\DebugExceptionHandler;

class SentryDebugExceptionHandler extends DebugExceptionHandler
{
    public function __construct()
    {
        SentryService::initialize();
        parent::__construct();
    }

    public function handleException(\Throwable $exception): void
    {
        SentryService::captureException($exception);
        parent::handleException($exception);
    }
}
