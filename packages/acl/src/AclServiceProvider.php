<?php

namespace Quochao56\Acl;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Quochao56\Acl\Policies\RolePolicy;
use Quochao56\Acl\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;

class AclServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('acl');
    }

    public function packageRegistered(): void
    {
        // 1. Merge configuration
        if (file_exists(__DIR__ . '/../config/permissions.php')) {
            $this->mergeConfigFrom(__DIR__ . '/../config/permissions.php', 'permissions');
        }

        // 2. Load package translations
        if (is_dir(__DIR__ . '/../lang')) {
            $this->loadTranslationsFrom(__DIR__ . '/../lang', 'acl');
        }
    }

    public function packageBooted(): void
    {
        // 1. Override filament-users translations first to ensure subsequent label resolves are translated
        if (is_dir(__DIR__ . '/../lang/filament-users')) {
            $this->loadTranslationsFrom(__DIR__ . '/../lang/filament-users', 'filament-users');

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

        // 3. Register Spatie Role field and filters into tomatophp/filament-users
        if (class_exists(\TomatoPHP\FilamentUsers\Filament\Resources\Users\Schemas\UserForm::class)) {
            \TomatoPHP\FilamentUsers\Filament\Resources\Users\Schemas\UserForm::register([
                \TomatoPHP\FilamentUsers\Filament\Resources\Users\Schemas\Components\Roles::make(),
            ]);
            \TomatoPHP\FilamentUsers\Filament\Resources\Users\Tables\UsersTable::register([
                \TomatoPHP\FilamentUsers\Filament\Resources\Users\Tables\Columns\Roles::make(),
            ]);
            \TomatoPHP\FilamentUsers\Filament\Resources\Users\Tables\UserFilters::register([
                \TomatoPHP\FilamentUsers\Filament\Resources\Users\Tables\Filters\Roles::make(),
            ]);
        }
    }
}
