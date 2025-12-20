<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\ComplaintsTrendChart;
use App\Filament\Widgets\KitchenRevenueChart;
use App\Filament\Widgets\KitchenSalesChart;
use App\Filament\Widgets\MealsOrderChart;
use App\Filament\Widgets\MealsTrendChart;
use App\Filament\Widgets\MostOrderedMeals;
use App\Filament\Widgets\OrderStats;
use App\Filament\Widgets\TopInstitutionsChart;
use App\Filament\Widgets\TotalSalesTrendChart;
use App\Http\Middleware\CheckUserRole;
use App\Http\Middleware\ProcessQueueMiddleware;
use App\Models\Complaint;
use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
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
            ->passwordReset()
            ->brandName('وطن فود')
            // ->brandLogo(asset('images/logo.jpg'))

            // تم الرفع بنجاح

            ->brandLogoHeight('2rem')
            ->favicon(asset('images/food_icon.png'))
            ->colors([
                'primary' => Color::hex('#dc2626'), // أحمر أنيق
            ])
            ->font('Inter')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                ComplaintsTrendChart::class ,

                KitchenRevenueChart::class,
                KitchenSalesChart::class,
                TotalSalesTrendChart::class

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
                CheckUserRole::class,
                // ProcessQueueMiddleware::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make()
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
