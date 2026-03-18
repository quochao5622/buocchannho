<?php

namespace Quochao56\PlanningEvaluation;

use Filament\Contracts\Plugin;
use Filament\Panel;

class PlanningEvaluationPlugin implements Plugin
{
    public function getId(): string
    {
        return 'planning-evaluation';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            Filament\Resources\Plannings\PlanningResource::class,
            Filament\Resources\Evaluations\EvaluationResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
