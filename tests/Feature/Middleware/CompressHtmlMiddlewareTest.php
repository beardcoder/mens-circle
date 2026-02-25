<?php

declare(strict_types=1);

use App\Http\Middleware\CompressHtml;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use voku\helper\HtmlMin;

beforeEach(function (): void {
    $this->middleware = new CompressHtml(new HtmlMin());
});

test('compresses html responses without livewire components', function (): void {
    $request = Request::create('/');
    $html = '<html>  <body>  <p>   Hello world   </p>  </body>  </html>';

    $response = $this->middleware->handle($request, function () use ($html) {
        return new Response($html, 200, ['Content-Type' => 'text/html']);
    });

    expect($response->getContent())->not->toBe($html)
        ->and(mb_strlen((string) $response->getContent()))->toBeLessThan(mb_strlen($html));
});

test('does not compress html responses containing livewire wire:snapshot', function (): void {
    $request = Request::create('/admin');
    $snapshot = json_encode(['memo' => ['id' => 'abc', 'name' => 'TestComponent', 'children' => []]]);
    $html = '<html>  <body>  <div wire:snapshot=\'' . $snapshot . '\'>  content  </div>  </body>  </html>';

    $response = $this->middleware->handle($request, function () use ($html) {
        return new Response($html, 200, ['Content-Type' => 'text/html']);
    });

    expect($response->getContent())->toBe($html);
});

test('does not compress non-html responses', function (): void {
    $request = Request::create('/api/data');
    $json = '{"key":   "value"}';

    $response = $this->middleware->handle($request, function () use ($json) {
        return new Response($json, 200, ['Content-Type' => 'application/json']);
    });

    expect($response->getContent())->toBe($json);
});

test('does not compress redirect responses', function (): void {
    $request = Request::create('/');

    $response = $this->middleware->handle($request, function () {
        return new Response('', 302, ['Content-Type' => 'text/html', 'Location' => '/other']);
    });

    expect($response->getContent())->toBe('');
});

test('preserves wire:snapshot children key when skipping compression', function (): void {
    $request = Request::create('/admin/resources');
    $snapshot = json_encode(['memo' => ['id' => 'xyz', 'name' => 'App\Filament\Resources\EventResource\Pages\ListEvents', 'children' => []]]);
    $html = '<html><body><div wire:snapshot=\'' . $snapshot . '\'></div></body></html>';

    $response = $this->middleware->handle($request, function () use ($html) {
        return new Response($html, 200, ['Content-Type' => 'text/html']);
    });

    $content = $response->getContent();
    $decodedSnapshot = json_decode(
        mb_substr((string) $content, mb_strpos((string) $content, "wire:snapshot='") + 15, mb_strpos((string) $content, "'></div>") - mb_strpos((string) $content, "wire:snapshot='") - 15),
        true,
    );

    expect($decodedSnapshot)->toHaveKey('memo')
        ->and($decodedSnapshot['memo'])->toHaveKey('children');
});
