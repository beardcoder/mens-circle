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
use Spatie\ResponseCache\Facades\ResponseCache;

class ClearCache extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Trash;

    protected string $view = 'filament.pages.clear-cache';

    protected static ?string $navigationLabel = 'Cache löschen';

    protected static ?string $title = 'Cache löschen';

    protected static ?int $navigationSort = 100;

    protected function getHeaderActions(): array
    {
        return [
            $this->makeCacheAction(
                name: 'clearApplicationCache',
                label: 'Anwendungs-Cache löschen',
                icon: 'heroicon-s-bolt',
                color: 'warning',
                modalHeading: 'Anwendungs-Cache löschen?',
                modalDescription: 'Dies löscht den Anwendungs-Cache.',
                successTitle: 'Anwendungs-Cache gelöscht',
                successBody: 'Der Anwendungs-Cache wurde erfolgreich gelöscht.',
                action: fn () => Artisan::call('cache:clear'),
            ),

            $this->makeCacheAction(
                name: 'clearResponseCache',
                label: 'Response-Cache löschen',
                icon: 'heroicon-s-globe-alt',
                color: 'warning',
                modalHeading: 'Response-Cache löschen?',
                modalDescription: 'Dies löscht den Response-Cache (gecachte HTTP-Antworten). Die Seiten werden beim nächsten Aufruf neu generiert.',
                successTitle: 'Response-Cache gelöscht',
                successBody: 'Der Response-Cache wurde erfolgreich gelöscht.',
                action: fn () => ResponseCache::clear(),
            ),

            $this->makeCacheAction(
                name: 'clearConfigCache',
                label: 'Konfigurations-Cache löschen',
                icon: 'heroicon-s-cog-6-tooth',
                color: 'info',
                modalHeading: 'Konfigurations-Cache löschen?',
                modalDescription: 'Dies löscht den Konfigurations-Cache.',
                successTitle: 'Konfigurations-Cache gelöscht',
                successBody: 'Der Konfigurations-Cache wurde erfolgreich gelöscht.',
                action: fn () => Artisan::call('config:clear'),
            ),

            $this->makeCacheAction(
                name: 'clearRouteCache',
                label: 'Routen-Cache löschen',
                icon: 'heroicon-s-map',
                color: 'info',
                modalHeading: 'Routen-Cache löschen?',
                modalDescription: 'Dies löscht den Routen-Cache.',
                successTitle: 'Routen-Cache neu aufgebaut',
                successBody: 'Der Routen-Cache wurde gelöscht und neu aufgebaut.',
                action: function (): void {
                    Artisan::call('route:clear');
                    Artisan::call('route:cache');
                },
            ),

            $this->makeCacheAction(
                name: 'clearViewCache',
                label: 'View-Cache löschen',
                icon: 'heroicon-s-eye',
                color: 'info',
                modalHeading: 'View-Cache löschen?',
                modalDescription: 'Dies löscht den View-Cache.',
                successTitle: 'View-Cache neu aufgebaut',
                successBody: 'Der View-Cache wurde gelöscht und neu aufgebaut.',
                action: function (): void {
                    Artisan::call('view:clear');
                    Artisan::call('view:cache');
                },
            ),

            $this->makeCacheAction(
                name: 'clearAll',
                label: 'Alle Caches löschen',
                icon: 'heroicon-s-arrow-path',
                color: 'danger',
                modalHeading: 'Alle Caches löschen?',
                modalDescription: 'Dies löscht alle Caches (Anwendung, Response, Konfiguration, Routen, Views). Diese Aktion kann nicht rückgängig gemacht werden.',
                successTitle: 'Alle Caches gelöscht',
                successBody: 'Alle Caches wurden gelöscht. Routen- und View-Cache wurden neu aufgebaut.',
                action: function (): void {
                    Artisan::call('cache:clear');
                    ResponseCache::clear();
                    Artisan::call('config:clear');
                    Artisan::call('route:clear');
                    Artisan::call('view:clear');
                    Artisan::call('route:cache');
                    Artisan::call('view:cache');
                },
                submitLabel: 'Alle löschen',
            ),

            $this->makeCacheAction(
                name: 'optimizeApplication',
                label: 'Laravel optimieren',
                icon: 'heroicon-s-rocket-launch',
                color: 'success',
                modalHeading: 'Laravel optimieren?',
                modalDescription: 'Dies führt den Laravel Optimize-Befehl aus und cached Konfiguration, Routen, Views und Events für maximale Performance.',
                successTitle: 'Laravel optimiert',
                successBody: 'Die Anwendung wurde erfolgreich optimiert. Konfiguration, Routen, Views und Events wurden gecached.',
                action: fn () => Artisan::call('optimize'),
                submitLabel: 'Jetzt optimieren',
            ),

            $this->makeCacheAction(
                name: 'clearOptimization',
                label: 'Optimierung zurücksetzen',
                icon: 'heroicon-s-x-circle',
                color: 'gray',
                modalHeading: 'Optimierung zurücksetzen?',
                modalDescription: 'Dies löscht alle durch Laravel Optimize erstellten Caches. Nützlich während der Entwicklung.',
                successTitle: 'Optimierung zurückgesetzt',
                successBody: 'Alle Optimierungs-Caches wurden gelöscht.',
                action: fn () => Artisan::call('optimize:clear'),
                submitLabel: 'Zurücksetzen',
            ),
        ];
    }

    private function makeCacheAction(
        string $name,
        string $label,
        string $icon,
        string $color,
        string $modalHeading,
        string $modalDescription,
        string $successTitle,
        string $successBody,
        Closure $action,
        string $submitLabel = 'Jetzt löschen',
    ): Action {
        return Action::make($name)
            ->label($label)
            ->icon($icon)
            ->color($color)
            ->requiresConfirmation()
            ->modalHeading($modalHeading)
            ->modalDescription($modalDescription)
            ->modalSubmitActionLabel($submitLabel)
            ->action(function () use ($action, $successTitle, $successBody): void {
                try {
                    $action();

                    Notification::make()
                        ->title($successTitle)
                        ->body($successBody)
                        ->success()
                        ->send();
                } catch (Exception $exception) {
                    Notification::make()
                        ->title('Fehler')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
