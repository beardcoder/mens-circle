<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Actions\Ai\UpdateAiGeneralSettings;
use App\Services\Ai\AiDataFormatter;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use RuntimeException;

final class UpdateGeneralSettingsTool extends Tool
{
    protected string $name = 'update_general_settings';

    protected string $description = 'Aktualisiert allgemeine Website-Einstellungen. Erwartet confirm=true und die zu ändernden Felder.';

    public function __construct(
        private readonly UpdateAiGeneralSettings $action,
        private readonly AiDataFormatter $formatter,
    ) {}

    public function handle(Request $request): Response
    {
        if (! $request->boolean('confirm')) {
            throw new RuntimeException('Zum Aktualisieren ist confirm=true erforderlich.');
        }

        $payload = $request->all();
        unset($payload['confirm']);
        $settings = $this->action->execute($payload);

        return Response::structured([
            'data' => $this->formatter->settings($settings),
        ]);
    }
}
