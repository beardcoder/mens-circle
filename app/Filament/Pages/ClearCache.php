<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use BackedEnum;
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
            Action::make('clearApplicationCache')
                ->label('Anwendungs-Cache löschen')
                ->icon('heroicon-s-bolt')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Anwendungs-Cache löschen?')
                ->modalDescription('Dies löscht den Anwendungs-Cache.')
                ->modalSubmitActionLabel('Jetzt löschen')
                ->action(function (): void {
                    try {
                        Artisan::call('cache:clear');

                        Notification::make()
                            ->title('Anwendungs-Cache gelöscht')
                            ->body('Der Anwendungs-Cache wurde erfolgreich gelöscht.')
                            ->success()
                            ->send();
                    } catch (Exception $exception) {
                        Notification::make()
                            ->title('Fehler beim Löschen des Caches')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('clearResponseCache')
                ->label('Response-Cache löschen')
                ->icon('heroicon-s-globe-alt')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Response-Cache löschen?')
                ->modalDescription('Dies löscht den Response-Cache (gecachte HTTP-Antworten). Die Seiten werden beim nächsten Aufruf neu generiert.')
                ->modalSubmitActionLabel('Jetzt löschen')
                ->action(function (): void {
                    try {
                        ResponseCache::clear();

                        Notification::make()
                            ->title('Response-Cache gelöscht')
                            ->body('Der Response-Cache wurde erfolgreich gelöscht.')
                            ->success()
                            ->send();
                    } catch (Exception $exception) {
                        Notification::make()
                            ->title('Fehler beim Löschen des Response-Caches')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('clearConfigCache')
                ->label('Konfigurations-Cache löschen')
                ->icon('heroicon-s-cog-6-tooth')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Konfigurations-Cache löschen?')
                ->modalDescription('Dies löscht den Konfigurations-Cache.')
                ->modalSubmitActionLabel('Jetzt löschen')
                ->action(function (): void {
                    try {
                        Artisan::call('config:clear');

                        Notification::make()
                            ->title('Konfigurations-Cache gelöscht')
                            ->body('Der Konfigurations-Cache wurde erfolgreich gelöscht.')
                            ->success()
                            ->send();
                    } catch (Exception $exception) {
                        Notification::make()
                            ->title('Fehler beim Löschen des Caches')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('clearRouteCache')
                ->label('Routen-Cache löschen')
                ->icon('heroicon-s-map')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Routen-Cache löschen?')
                ->modalDescription('Dies löscht den Routen-Cache.')
                ->modalSubmitActionLabel('Jetzt löschen')
                ->action(function (): void {
                    try {
                        Artisan::call('route:clear');
                        Artisan::call('route:cache');

                        Notification::make()
                            ->title('Routen-Cache neu aufgebaut')
                            ->body('Der Routen-Cache wurde gelöscht und neu aufgebaut.')
                            ->success()
                            ->send();
                    } catch (Exception $exception) {
                        Notification::make()
                            ->title('Fehler beim Löschen des Caches')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('clearViewCache')
                ->label('View-Cache löschen')
                ->icon('heroicon-s-eye')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('View-Cache löschen?')
                ->modalDescription('Dies löscht den View-Cache.')
                ->modalSubmitActionLabel('Jetzt löschen')
                ->action(function (): void {
                    try {
                        Artisan::call('view:clear');
                        Artisan::call('view:cache');

                        Notification::make()
                            ->title('View-Cache neu aufgebaut')
                            ->body('Der View-Cache wurde gelöscht und neu aufgebaut.')
                            ->success()
                            ->send();
                    } catch (Exception $exception) {
                        Notification::make()
                            ->title('Fehler beim Löschen des Caches')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('clearAll')
                ->label('Alle Caches löschen')
                ->icon('heroicon-s-arrow-path')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Alle Caches löschen?')
                ->modalDescription('Dies löscht alle Caches (Anwendung, Response, Konfiguration, Routen, Views). Diese Aktion kann nicht rückgängig gemacht werden.')
                ->modalSubmitActionLabel('Alle löschen')
                ->action(function (): void {
                    try {
                        Artisan::call('cache:clear');
                        ResponseCache::clear();
                        Artisan::call('config:clear');
                        Artisan::call('route:clear');
                        Artisan::call('view:clear');
                        Artisan::call('route:cache');
                        Artisan::call('view:cache');

                        Notification::make()
                            ->title('Alle Caches gelöscht')
                            ->body('Alle Caches wurden gelöscht. Routen- und View-Cache wurden neu aufgebaut.')
                            ->success()
                            ->send();
                    } catch (Exception $exception) {
                        Notification::make()
                            ->title('Fehler beim Löschen der Caches')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('optimizeApplication')
                ->label('Laravel optimieren')
                ->icon('heroicon-s-rocket-launch')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Laravel optimieren?')
                ->modalDescription('Dies führt den Laravel Optimize-Befehl aus und cached Konfiguration, Routen, Views und Events für maximale Performance.')
                ->modalSubmitActionLabel('Jetzt optimieren')
                ->action(function (): void {
                    try {
                        Artisan::call('optimize');

                        Notification::make()
                            ->title('Laravel optimiert')
                            ->body('Die Anwendung wurde erfolgreich optimiert. Konfiguration, Routen, Views und Events wurden gecached.')
                            ->success()
                            ->send();
                    } catch (Exception $exception) {
                        Notification::make()
                            ->title('Fehler bei der Optimierung')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('clearOptimization')
                ->label('Optimierung zurücksetzen')
                ->icon('heroicon-s-x-circle')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Optimierung zurücksetzen?')
                ->modalDescription('Dies löscht alle durch Laravel Optimize erstellten Caches. Nützlich während der Entwicklung.')
                ->modalSubmitActionLabel('Zurücksetzen')
                ->action(function (): void {
                    try {
                        Artisan::call('optimize:clear');

                        Notification::make()
                            ->title('Optimierung zurückgesetzt')
                            ->body('Alle Optimierungs-Caches wurden gelöscht.')
                            ->success()
                            ->send();
                    } catch (Exception $exception) {
                        Notification::make()
                            ->title('Fehler beim Zurücksetzen')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
