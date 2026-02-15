<?php

declare(strict_types=1);

namespace BeardCoder\MensCircle\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sentry\State\Scope;

final class SentryMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        \Sentry\configureScope(static function (Scope $scope) use ($request): void {
            $scope->setTag('typo3.type', 'frontend');
            $scope->setContext('request', [
                'url' => (string) $request->getUri(),
                'method' => $request->getMethod(),
            ]);
        });

        try {
            return $handler->handle($request);
        } catch (\Throwable $exception) {
            \Sentry\captureException($exception);

            throw $exception;
        }
    }
}
