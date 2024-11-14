<?php

namespace App\Providers\Filament;

use App\CentralLogics\Helpers;
use App\Models\Restaurant;
use App\Services\SuperAdminApiService;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $superAdminApiService = new SuperAdminApiService();
        $restaurantDetailFromSuperAdminPanel = $superAdminApiService->requestData();

        $restaurantData = Restaurant::where(['domain' => $restaurantDetailFromSuperAdminPanel['domain']])?->first()?->toArray() ?? $restaurantDetailFromSuperAdminPanel;

        return $panel
            ->default()
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->id('admin')
            ->path('admin')

            ->brandName(fn() => $restaurantData['name'] ?? config('app.name'))
            ->favicon(fn() => Helpers::get_full_url(null,$restaurantData['logo_full_url'],'public', 'favicon'))
            ->brandLogo(fn() => Helpers::get_full_url(null,$restaurantData['logo_full_url'],'public', 'logo'))
            ->brandLogoHeight('2rem')

            ->login()
            ->registration()
            ->passwordReset()
            ->emailVerification()
            ->profile()

            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                // Pages\Dashboard::class, // commented because using custom dashboard page
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
            ])
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
            ])
            ->spa()
            ->spaUrlExceptions([
                // '*/admin/posts/*',
            ])
            ->unsavedChangesAlerts()
            ->sidebarCollapsibleOnDesktop();
    }
}
