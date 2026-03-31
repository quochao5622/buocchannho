<?php

namespace Quochao56\PlanningEvaluation;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Assets\Theme;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentAsset;

class PlanningEvaluationTheme implements Plugin
{
    public function getId(): string
    {
        return 'planning-evaluation';
    }

    public function register(Panel $panel): void
    {
        FilamentAsset::register([
            Theme::make('planning-evaluation', __DIR__ . '/../resources/dist/planning-evaluation.css'),
        ]);

        $panel
            ->font('DM Sans')
            ->primaryColor(Color::Amber)
            ->secondaryColor(Color::Gray)
            ->warningColor(Color::Amber)
            ->dangerColor(Color::Rose)
            ->successColor(Color::Green)
            ->grayColor(Color::Gray)
            ->theme('planning-evaluation');
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
