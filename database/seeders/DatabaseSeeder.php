<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Quochao56\Scheduler\Database\Seeders\SchedulerSeeder;
use Quochao56\SessionLog\Database\Seeders\SessionLogSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(SettingsSeeder::class);
        $this->call(PlanningEvaluationSeeder::class);
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(SessionLogSeeder::class);
        $this->call(SchedulerSeeder::class);
        $this->call(EmployeeAttendanceSeeder::class);
    }
}
