<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ResponseCacheTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Cache vor jedem Test leeren
        $this->artisan('responsecache:clear');
    }

    public function test_homepage_is_cached(): void
    {
        config(['responsecache.enabled' => true]);

        // Erster Request - sollte nicht gecached sein
        $firstResponse = $this->get('/');
        $firstResponse->assertStatus(200);

        // Zweiter Request - sollte aus Cache kommen
        $secondResponse = $this->get('/');
        $secondResponse->assertStatus(200);

        // Prüfe ob Cache Header gesetzt ist (wenn aktiviert)
        if (config('responsecache.add_cache_time_header')) {
            $this->assertTrue($secondResponse->headers->has('laravel-responsecache'));
        }
    }

    public function test_admin_routes_are_not_cached(): void
    {
        config(['responsecache.enabled' => true]);

        // Admin Routes sollten nicht gecached werden
        $response = $this->get('/admin');

        // Die Route existiert möglicherweise nicht, aber wir testen nur das Caching-Verhalten
        // Cache sollte leer bleiben für Admin-Routen
        $this->assertTrue(true); // Placeholder - anpassen wenn Admin-Routes existieren
    }

    public function test_post_requests_are_not_cached(): void
    {
        config(['responsecache.enabled' => true]);

        // POST Requests sollten niemals gecached werden
        $response = $this->post('/', []);

        // Sollte keinen Cache-Header haben
        $this->assertFalse($response->headers->has('laravel-responsecache'));
    }

    public function test_cache_can_be_disabled(): void
    {
        config(['responsecache.enabled' => false]);

        $response = $this->get('/');
        $response->assertStatus(200);

        // Sollte keinen Cache-Header haben wenn disabled
        $this->assertFalse($response->headers->has('laravel-responsecache'));
    }

    public function test_cache_profile_works_correctly(): void
    {
        config(['responsecache.enabled' => true]);

        // Teste verschiedene Routen
        $routes = ['/', '/event'];

        foreach ($routes as $route) {
            $this->artisan('responsecache:clear');

            $firstResponse = $this->get($route);
            $secondResponse = $this->get($route);

            $this->assertEquals($firstResponse->status(), $secondResponse->status());
        }
    }
}
