<?php

declare(strict_types=1);

namespace App\Filament\Support;

use App\Services\GeocodingService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;

/**
 * Filament suffix action that reads street/postal_code/city from the form,
 * asks the Nominatim API for coordinates and writes latitude/longitude back.
 * Used as an inline action on Event's longitude field.
 */
final class GeocodeAction
{
    public static function make(): Action
    {
        return Action::make('geocode')
            ->label('Adresse zu Koordinaten umwandeln')
            ->icon(Heroicon::OutlinedMapPin)
            ->color('primary')
            ->action(static function (Get $get, Set $set, GeocodingService $geocoder): void {
                $address = self::buildAddress($get);

                if ($address === null) {
                    Notification::make()
                        ->title('Keine Adresse vorhanden')
                        ->body('Bitte trage zuerst eine Straße, PLZ oder Stadt ein.')
                        ->warning()
                        ->send();

                    return;
                }

                $coords = $geocoder->geocode($address);

                if ($coords === null) {
                    Notification::make()
                        ->title('Adresse nicht gefunden')
                        ->body('Die Adresse konnte nicht in Koordinaten umgewandelt werden. Bitte manuell eintragen.')
                        ->danger()
                        ->send();

                    return;
                }

                $set('latitude', $coords['latitude']);
                $set('longitude', $coords['longitude']);

                Notification::make()->title('Koordinaten gefunden')->body('Die Adresse wurde erfolgreich umgewandelt.')->success()->send();
            });
    }

    private static function buildAddress(Get $get): ?string
    {
        $toString = static fn(mixed $value): string => is_string($value) ? trim($value) : '';

        $parts = array_filter(
            [
                $toString($get('street')),
                trim($toString($get('postal_code')) . ' ' . $toString($get('city'))),
                'Deutschland',
            ],
            static fn(string $part): bool => $part !== '',
        );

        $address = trim(implode(', ', $parts));

        return $address === '' || $address === 'Deutschland' ? null : $address;
    }
}
