<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Sentry;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Core\RequestId;
use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Frontend\ContentObject\AbstractContentObject;
use TYPO3\CMS\Frontend\ContentObject\Exception\ProductionExceptionHandler;

#[Autoconfigure(public: true, shared: false)]
class SentryContentExceptionHandler extends ProductionExceptionHandler
{
    public function __construct(
        Context $context,
        Random $random,
        LoggerInterface $logger,
        RequestId $requestId,
    ) {
        SentryService::initialize();
        parent::__construct($context, $random, $logger, $requestId);
    }

    public function handle(
        \Exception $exception,
        ?AbstractContentObject $contentObject = null,
        $contentObjectConfiguration = [],
    ): string {
        SentryService::captureException($exception);

        return parent::handle($exception, $contentObject, $contentObjectConfiguration);
    }
}
