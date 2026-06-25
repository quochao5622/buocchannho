<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Support\Enums\Width;
use Quochao56\Employee\EmployeePlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Quochao56\PlanningEvaluation\PlanningEvaluationPlugin;
use Quochao56\Student\StudentPlugin;
use Quochao56\Equipment\EquipmentPlugin;
use Quochao56\Acl\AclPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandLogo(asset('images/logo/logo150x150.jpg'))
            ->brandLogoHeight('2.5rem')
            ->favicon(asset('images/logo/logo32x32.jpg'))
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
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
            ->plugins([
                EmployeePlugin::make(),
                StudentPlugin::make(),
                PlanningEvaluationPlugin::make(),
                EquipmentPlugin::make(),
                AclPlugin::make(),
                \TomatoPHP\FilamentUsers\FilamentUsersPlugin::make(),
            ])
            ->navigationGroups([
                trans('packages.student::student.navigation_group'),
                trans('packages.planning_evaluation::planning.navigation_group'),
                trans('packages.equipment::equipment.common.navigation_group'),
                trans('filament-users::user.group'),
            ])
            ->spa()
            ->maxContentWidth(Width::Full)
            ->sidebarCollapsibleOnDesktop(true);
    }
}
