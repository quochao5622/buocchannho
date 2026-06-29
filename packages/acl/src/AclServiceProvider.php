<?php

namespace Quochao56\Acl;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Lab404\Impersonate\Events\LeaveImpersonation;
use Lab404\Impersonate\Events\TakeImpersonation;
use Quochao56\Acl\Filament\Resources\Users\Schemas\UserForm;
use Quochao56\Acl\Listeners\LogSuccessfulLogin;
use Quochao56\Acl\Listeners\LogSuccessfulLogout;
use Quochao56\Acl\Policies\RolePolicy;
use Quochao56\Acl\Policies\UserPolicy;
use Quochao56\Core\Models\User;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\Permission\Models\Role;
use TomatoPHP\FilamentUsers\Filament\Resources\Users\Tables\Columns\Roles as UserRolesColumn;
use TomatoPHP\FilamentUsers\Filament\Resources\Users\Tables\Filters\Roles as UserRolesFilter;
use TomatoPHP\FilamentUsers\Filament\Resources\Users\Tables\UserFilters;
use TomatoPHP\FilamentUsers\Filament\Resources\Users\Tables\UsersTable;

class AclServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('acl');
    }

    public function packageRegistered(): void
    {
        // 1. Merge configuration
        if (file_exists(__DIR__.'/../config/permissions.php')) {
            $this->mergeConfigFrom(__DIR__.'/../config/permissions.php', 'permissions');
        }

        // 2. Load package translations
        if (is_dir(__DIR__.'/../lang')) {
            $this->loadTranslationsFrom(__DIR__.'/../lang', 'acl');
        }
    }

    public function packageBooted(): void
    {
        // 1. Override filament-users translations first to ensure subsequent label resolves are translated
        if (is_dir(__DIR__.'/../lang/filament-users')) {
            $this->loadTranslationsFrom(__DIR__.'/../lang/filament-users', 'filament-users');

            // Force reload translations by clearing translator's internal loaded cache
            try {
                $translator = app('translator');
                $reflector = new \ReflectionClass($translator);
                $property = $reflector->getProperty('loaded');
                $property->setAccessible(true);
                $property->setValue($translator, []);
            } catch (\Throwable $e) {
                // Fail silently if reflection fails in future Laravel versions
            }
        }

        // 2. Register Policies
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(User::class, UserPolicy::class);

        // Fix Impersonate Leave/Take logout issue in Laravel 11/12
        Event::listen(LeaveImpersonation::class, function (LeaveImpersonation $event) {
            app('request')->setUserResolver(function () use ($event) {
                return $event->impersonator;
            });
        });

        Event::listen(TakeImpersonation::class, function (TakeImpersonation $event) {
            app('request')->setUserResolver(function () use ($event) {
                return $event->impersonated;
            });
        });

        Event::listen(Login::class, LogSuccessfulLogin::class);
        Event::listen(Logout::class, LogSuccessfulLogout::class);

        // 3. Register Spatie Role field and filters into tomatophp/filament-users
        if (class_exists(UserForm::class)) {
            UsersTable::register([
                UserRolesColumn::make(),
                IconColumn::make('is_super_admin')
                    ->label(trans('acl::user.fields.super_admin'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(trans('acl::user.fields.active'))
                    ->boolean()
                    ->sortable(),
            ]);
            UserFilters::register([
                UserRolesFilter::make(),
                TernaryFilter::make('is_active')
                    ->label(trans('acl::user.fields.is_active')),
            ]);
        }
    }
}
