<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;

class ClearCache extends Page
{
    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedTrash;

    protected string $view = 'filament.pages.clear-cache';

    protected static ?string $navigationLabel = 'Cache löschen';

    protected static ?string $title = 'Cache löschen';

    protected static ?int $navigationSort = 100;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clearApplicationCache')
                ->label('Anwendungs-Cache löschen')
                ->icon(Heroicon::SolidBolt)
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
                ->icon(Heroicon::SolidCog6Tooth)
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
                ->icon(Heroicon::SolidMap)
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Routen-Cache löschen?')
                ->modalDescription('Dies löscht den Routen-Cache.')
                ->modalSubmitActionLabel('Jetzt löschen')
                ->action(function (): void {
                    try {
                        Artisan::call('route:clear');

                        Notification::make()
                            ->title('Routen-Cache gelöscht')
                            ->body('Der Routen-Cache wurde erfolgreich gelöscht.')
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
                ->icon(Heroicon::SolidEye)
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('View-Cache löschen?')
                ->modalDescription('Dies löscht den View-Cache.')
                ->modalSubmitActionLabel('Jetzt löschen')
                ->action(function (): void {
                    try {
                        Artisan::call('view:clear');

                        Notification::make()
                            ->title('View-Cache gelöscht')
                            ->body('Der View-Cache wurde erfolgreich gelöscht.')
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
                ->icon(Heroicon::SolidArrowPath)
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

                        Notification::make()
                            ->title('Alle Caches gelöscht')
                            ->body('Alle Caches wurden erfolgreich gelöscht.')
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
