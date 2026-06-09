<?php

namespace Quochao56\Equipment;

use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class EquipmentServiceProvider extends PackageServiceProvider
{
    public static string $name = 'equipment';

    public static string $viewNamespace = 'equipment';

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
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if (is_dir(__DIR__ . '/../lang')) {
            $this->loadTranslationsFrom(__DIR__ . '/../lang', 'packages.equipment');
        }
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_equipment_categories_table',
            'create_equipments_table',
            'create_equipment_inventories_table',
            'create_equipment_inventory_details_table',
        ];
    }
}

