<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use OwenIt\Auditing\Models\Audit;
use Quochao56\PlanningEvaluation\Models\EvaluationHistory;
use Quochao56\PlanningEvaluation\Models\PlanningHistory;
use Spatie\Activitylog\Models\Activity;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    Audit::where('created_at', '<', now()->subMonths(6))->delete();
    PlanningHistory::where('created_at', '<', now()->subMonths(6))->delete();
    EvaluationHistory::where('created_at', '<', now()->subMonths(6))->delete();
    Activity::where('created_at', '<', now()->subMonths(6))->delete();
})->daily();

Schedule::command('attendance:detect-absent')->dailyAt('21:30');
