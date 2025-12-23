<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;

class ClearCache extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::Trash;

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
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Fehler beim Löschen des Caches')
                            ->body($e->getMessage())
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
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Fehler beim Löschen des Caches')
                            ->body($e->getMessage())
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
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Fehler beim Löschen des Caches')
                            ->body($e->getMessage())
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
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Fehler beim Löschen des Caches')
                            ->body($e->getMessage())
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
                ->modalDescription('Dies löscht alle Caches. Diese Aktion kann nicht rückgängig gemacht werden.')
                ->modalSubmitActionLabel('Alle löschen')
                ->action(function (): void {
                    try {
                        Artisan::call('cache:clear');
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
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Fehler beim Löschen der Caches')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
