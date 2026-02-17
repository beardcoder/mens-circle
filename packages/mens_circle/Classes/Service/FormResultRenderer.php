<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Service;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use TYPO3\CMS\Core\Http\PropagateResponseException;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;

final readonly class FormResultRenderer
{
    public function __construct(
        private ViewFactoryInterface $viewFactory,
        private ResponseFactoryInterface $responseFactory,
        private StreamFactoryInterface $streamFactory,
    ) {}

    public function isEnhancedRequest(ServerRequestInterface $request): bool
    {
        return $request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * Render a Fluid template with the form result and short-circuit the
     * TYPO3 rendering pipeline so only the fragment is returned to the client.
     *
     * @throws PropagateResponseException always
     */
    public function sendFormResult(
        ServerRequestInterface $request,
        string $message,
        ContextualFeedbackSeverity $severity,
    ): never {
        $severityName = match ($severity) {
            ContextualFeedbackSeverity::OK => 'success',
            ContextualFeedbackSeverity::WARNING => 'warning',
            ContextualFeedbackSeverity::INFO => 'info',
            default => 'error',
        };

        $view = $this->viewFactory->create(new ViewFactoryData(
            templateRootPaths: ['EXT:mens_circle/Resources/Private/Templates/'],
            partialRootPaths: ['EXT:mens_circle/Resources/Private/Templates/Partials/'],
            request: $request,
        ));

        $view->assignMultiple([
            'message' => $message,
            'severity' => $severityName,
        ]);

        $html = $view->render('FormResult');

        $response = $this->responseFactory->createResponse()
            ->withHeader('Content-Type', 'text/html; charset=utf-8')
            ->withBody($this->streamFactory->createStream($html));

        throw new PropagateResponseException($response);
    }
}
