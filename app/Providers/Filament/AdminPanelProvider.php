<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use AchyutN\FilamentLogViewer\FilamentLogViewer;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->unsavedChangesAlerts()
            ->profile(isSimple: false)
            ->favicon(asset('favicon.svg'))
            ->brandName('Männerkreis Niederbayern')
            ->brandLogoHeight('40px')
            ->renderHook(
                'panels::auth.login.form.after',
                fn (): Factory|View => view('filament.components.auth.socialite.github')
            )
            ->colors([
                'primary' => [
                    50 => 'oklch(0.97 0.02 46)',
                    100 => 'oklch(0.94 0.04 46)',
                    200 => 'oklch(0.88 0.07 46)',
                    300 => 'oklch(0.79 0.1 46)',
                    400 => 'oklch(0.7 0.13 46)',
                    500 => 'oklch(0.58 0.13 46)',
                    600 => 'oklch(0.52 0.12 46)',
                    700 => 'oklch(0.44 0.1 46)',
                    800 => 'oklch(0.37 0.08 46)',
                    900 => 'oklch(0.31 0.06 46)',
                    950 => 'oklch(0.21 0.04 46)',
                ],
            ])
            ->renderHook(PanelsRenderHook::TOPBAR_END, fn (): Factory|View => view('filament.components.go-to-website'))
            ->plugins([
                FilamentLogViewer::make(),
            ])
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\Filament\Clusters')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->broadcasting(false)
            ->subNavigationPosition(SubNavigationPosition::Top)
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
