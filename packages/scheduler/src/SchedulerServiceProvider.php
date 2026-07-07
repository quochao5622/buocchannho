<?php

namespace Quochao56\Scheduler;

use Illuminate\Support\Facades\Gate;
use Quochao56\Scheduler\Models\Attendance;
use Quochao56\Scheduler\Models\Schedule;
use Quochao56\Scheduler\Models\ScheduleException;
use Quochao56\Scheduler\Policies\AttendancePolicy;
use Quochao56\Scheduler\Policies\ScheduleExceptionPolicy;
use Quochao56\Scheduler\Policies\SchedulePolicy;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SchedulerServiceProvider extends PackageServiceProvider
{
    public static string $name = 'scheduler';

    public static string $viewNamespace = 'scheduler';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name);

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if (is_dir(__DIR__.'/../lang')) {
            $this->loadTranslationsFrom(__DIR__.'/../lang', 'packages.scheduler');
        }

        if (file_exists(__DIR__.'/../config/permissions.php')) {
            $this->mergeConfigFrom(__DIR__.'/../config/permissions.php', 'permissions');
        }
    }

    public function packageBooted(): void
    {
        Gate::policy(
            Schedule::class,
            SchedulePolicy::class
        );
        Gate::policy(
            ScheduleException::class,
            ScheduleExceptionPolicy::class
        );
        Gate::policy(
            Attendance::class,
            AttendancePolicy::class
        );
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            '2026_07_01_000000_create_scheduler_tables',
            '2026_07_01_000001_create_classrooms_table',
            '2026_07_01_000002_change_day_of_week_to_json_in_schedules_table',
            '2026_07_01_000003_add_operational_fields_to_attendances_table',
        ];
    }
}
