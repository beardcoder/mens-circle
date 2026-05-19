<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use BackedEnum;
use Closure;
use Exception;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Spatie\ResponseCache\Facades\ResponseCache;

class ClearCache extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Bolt;

    protected string $view = 'filament.pages.clear-cache';

    protected static ?string $navigationLabel = 'Cache-Verwaltung';

    protected static ?string $title = 'Cache-Verwaltung';

    protected static ?int $navigationSort = 100;

    /**
     * @return array<int, array{label: string, value: string, hint: string, color: string}>
     */
    public function getStatusItems(): array
    {
        $isResponseCacheEnabled = Config::boolean('responsecache.enabled', true);
        $lifetimeSeconds = Config::integer('responsecache.cache.lifetime_in_seconds', 604_800);

        return [
            [
                'label' => 'Response-Cache',
                'value' => $isResponseCacheEnabled ? 'Aktiv' : 'Deaktiviert',
                'hint' => 'Cached HTTP-Antworten',
                'color' => $isResponseCacheEnabled ? 'success' : 'gray',
            ],
            [
                'label' => 'Lebensdauer',
                'value' => $this->formatDuration($lifetimeSeconds),
                'hint' => 'Maximaler Response-Cache',
                'color' => 'info',
            ],
            [
                'label' => 'Cache-Treiber',
                'value' => Config::string('responsecache.cache.store', 'file'),
                'hint' => 'Speicher für Antworten',
                'color' => 'info',
            ],
            [
                'label' => 'App-Cache',
                'value' => Config::string('cache.default', 'file'),
                'hint' => 'Allgemeiner Anwendungs-Cache',
                'color' => 'info',
            ],
        ];
    }

    public function clearResponseCacheAction(): Action
    {
        return $this->makeCacheAction(
            name: 'clearResponseCache',
            label: 'Response-Cache leeren',
            icon: Heroicon::GlobeAlt,
            color: 'primary',
            modalHeading: 'Response-Cache leeren?',
            modalDescription: 'Alle gecachten HTTP-Antworten werden verworfen. Seiten werden beim nächsten Aufruf neu generiert.',
            successTitle: 'Response-Cache geleert',
            successBody: 'Alle gecachten HTTP-Antworten wurden entfernt.',
            action: ResponseCache::clear(...),
        );
    }

    public function clearApplicationCacheAction(): Action
    {
        return $this->makeCacheAction(
            name: 'clearApplicationCache',
            label: 'Anwendungs-Cache leeren',
            icon: Heroicon::CircleStack,
            color: 'primary',
            modalHeading: 'Anwendungs-Cache leeren?',
            modalDescription: 'Löscht den allgemeinen Anwendungs-Cache (cache:clear).',
            successTitle: 'Anwendungs-Cache geleert',
            successBody: 'Der Anwendungs-Cache wurde erfolgreich geleert.',
            action: static fn() => Artisan::call('cache:clear'),
        );
    }

    public function clearConfigCacheAction(): Action
    {
        return $this->makeCacheAction(
            name: 'clearConfigCache',
            label: 'Config-Cache leeren',
            icon: Heroicon::Cog6Tooth,
            color: 'gray',
            modalHeading: 'Config-Cache leeren?',
            modalDescription: 'Entfernt den gecachten Konfigurations-Container (config:clear).',
            successTitle: 'Config-Cache geleert',
            successBody: 'Der Konfigurations-Cache wurde geleert.',
            action: static fn() => Artisan::call('config:clear'),
        );
    }

    public function clearRouteCacheAction(): Action
    {
        return $this->makeCacheAction(
            name: 'clearRouteCache',
            label: 'Routen-Cache leeren',
            icon: Heroicon::Map,
            color: 'gray',
            modalHeading: 'Routen-Cache leeren?',
            modalDescription: 'Löscht den Routen-Cache und baut ihn neu auf (route:clear → route:cache).',
            successTitle: 'Routen-Cache neu aufgebaut',
            successBody: 'Die Routen wurden frisch gecached.',
            action: static function (): void {
                Artisan::call('route:clear');
                Artisan::call('route:cache');
            },
        );
    }

    public function clearViewCacheAction(): Action
    {
        return $this->makeCacheAction(
            name: 'clearViewCache',
            label: 'View-Cache leeren',
            icon: Heroicon::Eye,
            color: 'gray',
            modalHeading: 'View-Cache leeren?',
            modalDescription: 'Verwirft kompilierte Blade-Templates und baut sie neu auf (view:clear → view:cache).',
            successTitle: 'View-Cache neu aufgebaut',
            successBody: 'Die Blade-Views wurden frisch kompiliert.',
            action: static function (): void {
                Artisan::call('view:clear');
                Artisan::call('view:cache');
            },
        );
    }

    public function optimizeAction(): Action
    {
        return $this->makeCacheAction(
            name: 'optimize',
            label: 'Optimieren',
            icon: Heroicon::RocketLaunch,
            color: 'success',
            modalHeading: 'Anwendung optimieren?',
            modalDescription: 'Cached Konfiguration, Routen, Views und Events für maximale Performance (artisan optimize).',
            successTitle: 'Anwendung optimiert',
            successBody: 'Die Optimierungs-Caches wurden aufgebaut.',
            action: static fn() => Artisan::call('optimize'),
            submitLabel: 'Jetzt optimieren',
        );
    }

    public function clearOptimizationAction(): Action
    {
        return $this->makeCacheAction(
            name: 'clearOptimization',
            label: 'Optimierung zurücksetzen',
            icon: Heroicon::ArrowUturnLeft,
            color: 'gray',
            modalHeading: 'Optimierung zurücksetzen?',
            modalDescription: 'Entfernt alle durch optimize erstellten Caches (artisan optimize:clear).',
            successTitle: 'Optimierung zurückgesetzt',
            successBody: 'Alle Optimierungs-Caches wurden entfernt.',
            action: static fn() => Artisan::call('optimize:clear'),
            submitLabel: 'Zurücksetzen',
        );
    }

    public function clearAllAction(): Action
    {
        return $this->makeCacheAction(
            name: 'clearAll',
            label: 'Alles leeren',
            icon: Heroicon::Trash,
            color: 'danger',
            modalHeading: 'Alle Caches leeren?',
            modalDescription: 'Leert Anwendungs-, Response-, Config-, Routen- und View-Cache und baut Routen sowie Views neu auf. Diese Aktion kann nicht rückgängig gemacht werden.',
            successTitle: 'Alle Caches geleert',
            successBody: 'Sämtliche Caches wurden entfernt und Routen/Views neu aufgebaut.',
            action: static function (): void {
                Artisan::call('cache:clear');
                ResponseCache::clear();
                Artisan::call('config:clear');
                Artisan::call('route:clear');
                Artisan::call('view:clear');
                Artisan::call('route:cache');
                Artisan::call('view:cache');
            },
            submitLabel: 'Alles leeren',
        );
    }

    private function makeCacheAction(
        string $name,
        string $label,
        Heroicon $icon,
        string $color,
        string $modalHeading,
        string $modalDescription,
        string $successTitle,
        string $successBody,
        Closure $action,
        string $submitLabel = 'Jetzt leeren',
    ): Action {
        return Action::make($name)
            ->label($label)
            ->icon($icon)
            ->color($color)
            ->requiresConfirmation()
            ->modalIcon($icon)
            ->modalHeading($modalHeading)
            ->modalDescription($modalDescription)
            ->modalSubmitActionLabel($submitLabel)
            ->action(static function () use ($action, $successTitle, $successBody): void {
                try {
                    $action();

                    Notification::make()->title($successTitle)->body($successBody)->success()->send();
                } catch (Exception $exception) {
                    Notification::make()->title('Fehler beim Leeren des Caches')->body($exception->getMessage())->danger()->send();
                }
            });
    }

    private function formatDuration(int $seconds): string
    {
        return match (true) {
            $seconds >= 86_400 => sprintf('%d Tage', (int) round($seconds / 86_400)),
            $seconds >= 3600 => sprintf('%d Std.', (int) round($seconds / 3600)),
            $seconds >= 60 => sprintf('%d Min.', (int) round($seconds / 60)),
            default => sprintf('%d Sek.', $seconds),
        };
    }
}
