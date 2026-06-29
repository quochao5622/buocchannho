<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Quochao56\Student\Models\Student;
use App\Policies\StudentPolicy;
use Quochao56\PlanningEvaluation\Models\Planning;
use App\Policies\PlanningPolicy;
use Quochao56\PlanningEvaluation\Models\Evaluation;
use App\Policies\EvaluationPolicy;
use Quochao56\Equipment\Models\Equipment;
use App\Policies\EquipmentPolicy;
use Quochao56\Equipment\Models\EquipmentCategory;
use App\Policies\EquipmentCategoryPolicy;
use Quochao56\Equipment\Models\EquipmentInventory;
use App\Policies\EquipmentInventoryPolicy;
use Quochao56\Employee\Models\Employee;
use App\Policies\EmployeePolicy;
use OwenIt\Auditing\Models\Audit;
use Tapp\FilamentAuditing\Models\Audit as TappAudit;
use App\Policies\AuditPolicy;

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
