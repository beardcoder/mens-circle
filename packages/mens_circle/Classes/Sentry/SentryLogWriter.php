<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Sentry;

use Sentry\State\Scope;
use TYPO3\CMS\Core\Log\LogRecord;
use TYPO3\CMS\Core\Log\Writer\AbstractWriter;

use function Sentry\withScope;

final class SentryLogWriter extends AbstractWriter
{
    /**
     * Components whose errors are already captured by the exception handlers
     * and should not be duplicated via the log writer.
     */
    private const IGNORED_COMPONENTS = [
        'TYPO3.CMS.Core.Error.ErrorHandler',
        'TYPO3.CMS.Core.Error.ProductionExceptionHandler',
        'TYPO3.CMS.Core.Error.DebugExceptionHandler',
        'TYPO3.CMS.Frontend.ContentObject.Exception.ProductionExceptionHandler',
        'BeardCoder.MensCircle.Sentry.SentryProductionExceptionHandler',
        'BeardCoder.MensCircle.Sentry.SentryDebugExceptionHandler',
    ];

    public function __construct(array $options = [])
    {
        SentryService::initialize();
        parent::__construct($options);
    }

    public function writeLog(LogRecord $record): self
    {
        if (!SentryService::isEnabled()) {
            return $this;
        }

        if (!$this->shouldHandle($record)) {
            return $this;
        }

        withScope(function (Scope $scope) use ($record): void {
            $scope->setExtra('component', $record->getComponent());
            $scope->setTag('source', 'logwriter');
            $scope->setFingerprint([$record->getMessage(), $record->getComponent()]);

            $data = $record->getData();
            if ($data !== []) {
                if (isset($data['exception']) && $data['exception'] instanceof \Throwable) {
                    SentryService::captureException($data['exception']);

                    return;
                }

                $scope->setExtra('data', $data);
            }

            $message = $this->interpolate($record->getMessage(), $data);
            SentryService::captureMessage($message, $record->getLevel());
        });

        return $this;
    }

    private function shouldHandle(LogRecord $record): bool
    {
        $component = $record->getComponent();

        foreach (self::IGNORED_COMPONENTS as $ignored) {
            if (str_starts_with($component . '.', $ignored . '.') || $component === $ignored) {
                return false;
            }
        }

        return true;
    }
}
