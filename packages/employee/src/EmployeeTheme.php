<?php

namespace Quochao56\Employee;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Assets\Theme;
use Filament\Support\Color;
use Filament\Support\Facades\FilamentAsset;

class EmployeeTheme implements Plugin
{
    public function getId(): string
    {
        return 'employee';
    }

    public function register(Panel $panel): void
    {
        FilamentAsset::register([
            Theme::make('employee', __DIR__ . '/../resources/dist/employee.css'),
        ]);

        $panel
            ->font('DM Sans')
            ->primaryColor(Color::Amber)
            ->secondaryColor(Color::Gray)
            ->warningColor(Color::Amber)
            ->dangerColor(Color::Rose)
            ->successColor(Color::Green)
            ->grayColor(Color::Gray)
            ->theme('employee');
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
