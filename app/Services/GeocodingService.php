<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    private const string NOMINATIM_ENDPOINT = 'https://nominatim.openstreetmap.org/search';

    /**
     * Resolve an address string to latitude/longitude using OpenStreetMap Nominatim.
     *
     * @return array{latitude: float, longitude: float}|null
     */
    public function geocode(string $address): ?array
    {
        $address = trim($address);

        if ($address === '') {
            return null;
        }

        $appName = (string) config('app.name', 'mens-circle');
        $appUrl = (string) config('app.url', 'https://example.com');

        try {
            $response = Http::withHeaders([
                'User-Agent' => $appName . ' (' . $appUrl . ')',
                'Accept-Language' => 'de',
            ])
                ->timeout(10)
                ->retry(2, 250)
                ->get(self::NOMINATIM_ENDPOINT, [
                    'q' => $address,
                    'format' => 'json',
                    'limit' => 1,
                    'addressdetails' => 0,
                ]);
        } catch (ConnectionException $e) {
            Log::warning('Geocoding request failed', ['address' => $address, 'error' => $e->getMessage()]);

            return null;
        }

        if (!$response->successful()) {
            return null;
        }

        $results = $response->json();

        if (!is_array($results) || $results === []) {
            return null;
        }

        $first = $results[0] ?? null;

        if (!is_array($first) || !isset($first['lat'], $first['lon'])) {
            return null;
        }

        $lat = $first['lat'];
        $lon = $first['lon'];

        if (!is_numeric($lat) || !is_numeric($lon)) {
            return null;
        }

        return [
            'latitude' => (float) $lat,
            'longitude' => (float) $lon,
        ];
    }
}
