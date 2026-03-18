<?php

namespace Quochao56\Student;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Assets\Theme;
use Filament\Support\Color;
use Filament\Support\Facades\FilamentAsset;

class StudentTheme implements Plugin
{
    public function getId(): string
    {
        return 'student';
    }

    public function register(Panel $panel): void
    {
        FilamentAsset::register([
            Theme::make('student', __DIR__ . '/../resources/dist/student.css'),
        ]);

        $panel
            ->font('DM Sans')
            ->primaryColor(Color::Amber)
            ->secondaryColor(Color::Gray)
            ->warningColor(Color::Amber)
            ->dangerColor(Color::Rose)
            ->successColor(Color::Green)
            ->grayColor(Color::Gray)
            ->theme('student');
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
