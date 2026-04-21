<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Services\Ai\AiDataFormatter;
use App\Settings\GeneralSettings;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

final class GetGeneralSettingsTool extends Tool
{
    protected string $name = 'get_general_settings';

    protected string $description = 'Gibt die allgemeinen Website-Einstellungen zurück.';

    public function __construct(
        private readonly AiDataFormatter $formatter,
        private readonly GeneralSettings $settings,
    ) {}

    public function handle(Request $request): ResponseFactory
    {
        return Response::structured([
            'data' => $this->formatter->settings($this->settings),
        ]);
    }
}
