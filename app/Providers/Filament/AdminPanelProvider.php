<?php

namespace App\Providers\Filament;

use AchyutN\FilamentLogViewer\FilamentLogViewer;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Quochao56\Acl\AclPlugin;
use Quochao56\Employee\EmployeePlugin;
use Quochao56\Equipment\EquipmentPlugin;
use Quochao56\PlanningEvaluation\PlanningEvaluationPlugin;
use Quochao56\SessionLog\SessionLogPlugin;
use Quochao56\Student\StudentPlugin;
use Tapp\FilamentAuditing\FilamentAuditingPlugin;
use TomatoPHP\FilamentUsers\FilamentUsersPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->emailVerification()
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
                SessionLogPlugin::make(),
                EquipmentPlugin::make(),
                AclPlugin::make(),
                FilamentUsersPlugin::make(),
                FilamentLogViewer::make()
                    ->navigationGroup(trans('navigation.system'))
                    ->authorize(fn(): bool => auth()->check() && auth()->user()->can('logs.index')),
                FilamentAuditingPlugin::make(),
            ])
            ->navigationGroups([
                $this->collapsedNavigationGroup(trans('packages.student::student.navigation_group')),
                $this->collapsedNavigationGroup(trans('packages.session_log::daily_log.navigation_group')),
                $this->collapsedNavigationGroup(trans('packages.planning_evaluation::planning.navigation_group')),
                $this->collapsedNavigationGroup(trans('packages.equipment::equipment.common.navigation_group')),
                $this->collapsedNavigationGroup(trans('navigation.system')),
            ])
            // ->spa()
            ->maxContentWidth(Width::Full)
            ->collapsibleNavigationGroups()
            ->sidebarCollapsibleOnDesktop(true);
    }

    public function boot(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::STYLES_AFTER,
            fn(): string => new HtmlString('<link rel="stylesheet" href="' . asset('css/admin.css') . '">'),
        );
        FilamentView::registerRenderHook(
            PanelsRenderHook::SCRIPTS_AFTER,
            fn(): string => new HtmlString('<script src="' . asset('js/admin.js') . '" defer></script>'),
        );
    }

    private function collapsedNavigationGroup(string $label): NavigationGroup
    {
        return NavigationGroup::make()
            ->label($label)
            ->collapsible()
            ->collapsed();
    }
}
