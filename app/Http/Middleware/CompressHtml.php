<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use voku\helper\HtmlMin;

readonly class CompressHtml
{
    public function __construct(
        private HtmlMin $htmlMin,
    ) {}

    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        $response = $next($request);

        if (!$this->shouldCompress($response)) {
            return $response;
        }

        $content = $response->getContent();

        if ($content === false || $content === '') {
            return $response;
        }

        $response->setContent($this->htmlMin->minify($content));

        return $response;
    }

    private function shouldCompress(SymfonyResponse $response): bool
    {
        if (!$response instanceof Response) {
            return false;
        }

        if ($response->isRedirection() || !$response->isSuccessful()) {
            return false;
        }

        $contentType = $response->headers->get('Content-Type') ?? '';

        if (!str_contains($contentType, 'text/html')) {
            return false;
        }

        $content = $response->getContent();

        // Skip compression for pages containing Livewire components — HtmlMin
        // can corrupt wire:snapshot JSON (e.g. stripping empty "children": {}),
        // causing Livewire hydration to fail with "Undefined array key children".
        if ($content !== false && str_contains($content, 'wire:snapshot')) {
            return false;
        }

        return true;
    }
}
