<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }
        });

        \Illuminate\Support\Facades\Gate::policy(\Quochao56\Student\Models\Student::class, \App\Policies\StudentPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\Quochao56\PlanningEvaluation\Models\Planning::class, \App\Policies\PlanningPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\Quochao56\PlanningEvaluation\Models\Evaluation::class, \App\Policies\EvaluationPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\Quochao56\Equipment\Models\Equipment::class, \App\Policies\EquipmentPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\Quochao56\Equipment\Models\EquipmentCategory::class, \App\Policies\EquipmentCategoryPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\Quochao56\Equipment\Models\EquipmentInventory::class, \App\Policies\EquipmentInventoryPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\Quochao56\Employee\Models\Employee::class, \App\Policies\EmployeePolicy::class);
    }
}
