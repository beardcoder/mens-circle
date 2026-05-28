<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use BackedEnum;
use Closure;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Spatie\ResponseCache\Facades\ResponseCache;

final class ClearCache extends Page implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Bolt;

    protected string $view = 'filament.pages.clear-cache';

    protected static ?string $navigationLabel = 'Cache-Verwaltung';

    protected static ?string $title = 'Cache-Verwaltung';

    protected static ?int $navigationSort = 100;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        $enabled = Config::boolean('responsecache.enabled', true);
        $lifetimeSeconds = Config::integer('responsecache.cache.lifetime_in_seconds', 604_800);

        return $schema->components([
            Section::make('Cache-Status')
                ->columns(4)
                ->schema([
                    TextEntry::make('response_cache')
                        ->label('Response-Cache')
                        ->hint('Gecachte HTTP-Antworten')
                        ->state($enabled ? 'Aktiv' : 'Deaktiviert')
                        ->badge()
                        ->color($enabled ? 'success' : 'gray'),

                    TextEntry::make('lifetime')
                        ->label('Cache-Lebensdauer')
                        ->hint('Max. TTL für Response-Cache')
                        ->state($this->formatDuration($lifetimeSeconds))
                        ->badge()
                        ->color('info'),

                    TextEntry::make('cache_driver')
                        ->label('Cache-Treiber')
                        ->hint('Response-Cache Speicher')
                        ->state(Config::string('responsecache.cache.store', 'file'))
                        ->badge()
                        ->color('gray'),

                    TextEntry::make('app_cache')
                        ->label('App-Cache')
                        ->hint('Allgemeiner Cache-Treiber')
                        ->state(Config::string('cache.default', 'file'))
                        ->badge()
                        ->color('gray'),
                ]),
        ]);
    }

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            $this->action('clearResponseCache'),

            ActionGroup::make([
                $this->action('clearApplicationCache'),
                $this->action('clearConfigCache'),
                $this->action('clearRouteCache'),
                $this->action('clearViewCache'),
            ])
                ->label('System-Caches')
                ->icon(Heroicon::Cog6Tooth)
                ->button()
                ->color('gray'),

            ActionGroup::make([
                $this->action('optimize'),
                $this->action('clearOptimization'),
            ])
                ->label('Optimierung')
                ->icon(Heroicon::RocketLaunch)
                ->button()
                ->color('success'),

            $this->action('clearAll'),
        ];
    }

    /**
     * @return array<string, array{label: string, icon: Heroicon, color: string, heading: string, description: string, success: string, run: Closure, submit?: string}>
     */
    private function definitions(): array
    {
        return [
            'clearResponseCache' => [
                'label' => 'Response-Cache leeren',
                'icon' => Heroicon::GlobeAlt,
                'color' => 'primary',
                'heading' => 'Response-Cache leeren?',
                'description' => 'Alle gecachten HTTP-Antworten werden verworfen. Seiten werden beim nächsten Aufruf neu generiert.',
                'success' => 'Alle gecachten HTTP-Antworten wurden entfernt.',
                'run' => ResponseCache::clear(...),
            ],
            'clearApplicationCache' => [
                'label' => 'Anwendungs-Cache leeren',
                'icon' => Heroicon::CircleStack,
                'color' => 'gray',
                'heading' => 'Anwendungs-Cache leeren?',
                'description' => 'Löscht den allgemeinen Anwendungs-Cache (cache:clear).',
                'success' => 'Der Anwendungs-Cache wurde erfolgreich geleert.',
                'run' => static fn() => Artisan::call('cache:clear'),
            ],
            'clearConfigCache' => [
                'label' => 'Config-Cache leeren',
                'icon' => Heroicon::Cog6Tooth,
                'color' => 'gray',
                'heading' => 'Config-Cache leeren?',
                'description' => 'Entfernt den gecachten Konfigurations-Container (config:clear).',
                'success' => 'Der Konfigurations-Cache wurde geleert.',
                'run' => static fn() => Artisan::call('config:clear'),
            ],
            'clearRouteCache' => [
                'label' => 'Routen-Cache leeren',
                'icon' => Heroicon::Map,
                'color' => 'gray',
                'heading' => 'Routen-Cache leeren?',
                'description' => 'Löscht den Routen-Cache und baut ihn neu auf (route:clear → route:cache).',
                'success' => 'Die Routen wurden frisch gecached.',
                'run' => static function (): void {
                    Artisan::call('route:clear');
                    Artisan::call('route:cache');
                },
            ],
            'clearViewCache' => [
                'label' => 'View-Cache leeren',
                'icon' => Heroicon::Eye,
                'color' => 'gray',
                'heading' => 'View-Cache leeren?',
                'description' => 'Verwirft kompilierte Blade-Templates und baut sie neu auf (view:clear → view:cache).',
                'success' => 'Die Blade-Views wurden frisch kompiliert.',
                'run' => static function (): void {
                    Artisan::call('view:clear');
                    Artisan::call('view:cache');
                },
            ],
            'optimize' => [
                'label' => 'Optimieren',
                'icon' => Heroicon::RocketLaunch,
                'color' => 'success',
                'heading' => 'Anwendung optimieren?',
                'description' => 'Cached Konfiguration, Routen, Views und Events für maximale Performance (artisan optimize).',
                'success' => 'Die Optimierungs-Caches wurden aufgebaut.',
                'submit' => 'Jetzt optimieren',
                'run' => static fn() => Artisan::call('optimize'),
            ],
            'clearOptimization' => [
                'label' => 'Optimierung zurücksetzen',
                'icon' => Heroicon::ArrowUturnLeft,
                'color' => 'gray',
                'heading' => 'Optimierung zurücksetzen?',
                'description' => 'Entfernt alle durch optimize erstellten Caches (artisan optimize:clear).',
                'success' => 'Alle Optimierungs-Caches wurden entfernt.',
                'submit' => 'Zurücksetzen',
                'run' => static fn() => Artisan::call('optimize:clear'),
            ],
            'clearAll' => [
                'label' => 'Alles leeren',
                'icon' => Heroicon::Trash,
                'color' => 'danger',
                'heading' => 'Alle Caches leeren?',
                'description' => 'Leert Anwendungs-, Response-, Config-, Routen- und View-Cache und baut Routen sowie Views neu auf. Diese Aktion kann nicht rückgängig gemacht werden.',
                'success' => 'Sämtliche Caches wurden entfernt und Routen/Views neu aufgebaut.',
                'submit' => 'Alles leeren',
                'run' => static function (): void {
                    Artisan::call('cache:clear');
                    ResponseCache::clear();
                    Artisan::call('config:clear');
                    Artisan::call('route:clear');
                    Artisan::call('view:clear');
                    Artisan::call('route:cache');
                    Artisan::call('view:cache');
                },
            ],
        ];
    }

    private function action(string $name): Action
    {
        $config = $this->definitions()[$name];

        return Action::make($name)
            ->label($config['label'])
            ->icon($config['icon'])
            ->color($config['color'])
            ->requiresConfirmation()
            ->modalIcon($config['icon'])
            ->modalHeading($config['heading'])
            ->modalDescription($config['description'])
            ->modalSubmitActionLabel($config['submit'] ?? 'Jetzt leeren')
            ->action(static function () use ($config): void {
                try {
                    $config['run']();

                    Notification::make()->title($config['label'])->body($config['success'])->success()->send();
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
