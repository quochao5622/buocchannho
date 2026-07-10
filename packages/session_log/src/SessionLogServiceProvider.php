<?php

namespace Quochao56\SessionLog;

use Illuminate\Support\Facades\Gate;
use Quochao56\SessionLog\Models\BehaviorIncident;
use Quochao56\SessionLog\Models\DailyLog;
use Quochao56\SessionLog\Policies\BehaviorIncidentPolicy;
use Quochao56\SessionLog\Policies\DailyLogPolicy;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SessionLogServiceProvider extends PackageServiceProvider
{
    public static string $name = 'session-log';

    public static string $viewNamespace = 'session-log';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishMigrations()
                    ->askToRunMigrations();
            });

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if (is_dir(__DIR__.'/../lang')) {
            $this->loadTranslationsFrom(__DIR__.'/../lang', 'packages.session_log');
        }

        if (file_exists(__DIR__.'/../config/permissions.php')) {
            $this->mergeConfigFrom(__DIR__.'/../config/permissions.php', 'permissions');
        }
    }

    public function packageBooted(): void
    {
        Gate::policy(
            DailyLog::class,
            DailyLogPolicy::class
        );
        Gate::policy(
            BehaviorIncident::class,
            BehaviorIncidentPolicy::class
        );
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            '2026_07_01_000001_create_daily_logs_table',
            '2026_07_01_000002_create_behavior_incidents_table',
        ];
    }
}
