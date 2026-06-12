<?php

namespace Quochao56\Core;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CoreServiceProvider extends PackageServiceProvider
{
    public static string $name = 'core';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name);

        if (file_exists($package->basePath('/../lang'))) {
            $package->hasTranslations();
        }
    }

    public function packageRegistered(): void
    {
        if (is_dir(__DIR__ . '/../lang')) {
            $this->loadTranslationsFrom(__DIR__ . '/../lang', 'packages.core');
        }
    }
}
