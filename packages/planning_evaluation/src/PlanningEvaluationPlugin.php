<?php

namespace Quochao56\PlanningEvaluation;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Quochao56\PlanningEvaluation\Filament\Pages\PlanningEvaluationTracker;
use Quochao56\PlanningEvaluation\Filament\Pages\StudentProgressReport;
use Quochao56\PlanningEvaluation\Filament\Resources\Evaluations\EvaluationResource;
use Quochao56\PlanningEvaluation\Filament\Resources\Plannings\PlanningResource;

class PlanningEvaluationPlugin implements Plugin
{
    public function getId(): string
    {
        return 'planning-evaluation';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            PlanningResource::class,
            EvaluationResource::class,
        ])->pages([
            PlanningEvaluationTracker::class,
            StudentProgressReport::class,
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
