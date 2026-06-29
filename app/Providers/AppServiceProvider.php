<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use OwenIt\Auditing\Models\Audit;
use Quochao56\Core\Policies\AuditPolicy;
use Quochao56\Employee\Models\Employee;
use Quochao56\Employee\Policies\EmployeePolicy;
use Quochao56\Equipment\Models\Equipment;
use Quochao56\Equipment\Models\EquipmentCategory;
use Quochao56\Equipment\Models\EquipmentInventory;
use Quochao56\Equipment\Policies\EquipmentCategoryPolicy;
use Quochao56\Equipment\Policies\EquipmentInventoryPolicy;
use Quochao56\Equipment\Policies\EquipmentPolicy;
use Quochao56\PlanningEvaluation\Models\Evaluation;
use Quochao56\PlanningEvaluation\Models\Planning;
use Quochao56\PlanningEvaluation\Policies\EvaluationPolicy;
use Quochao56\PlanningEvaluation\Policies\PlanningPolicy;
use Quochao56\Student\Models\Student;
use Quochao56\Student\Policies\StudentPolicy;
use Tapp\FilamentAuditing\Models\Audit as TappAudit;

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
        Gate::before(function ($user, $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }
        });

        Gate::policy(Student::class, StudentPolicy::class);
        Gate::policy(Planning::class, PlanningPolicy::class);
        Gate::policy(Evaluation::class, EvaluationPolicy::class);
        Gate::policy(Equipment::class, EquipmentPolicy::class);
        Gate::policy(EquipmentCategory::class, EquipmentCategoryPolicy::class);
        Gate::policy(EquipmentInventory::class, EquipmentInventoryPolicy::class);
        Gate::policy(Employee::class, EmployeePolicy::class);
        Gate::policy(Audit::class, AuditPolicy::class);
        Gate::policy(TappAudit::class, AuditPolicy::class);
    }
}
