<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Quochao56\SessionLog\Database\Seeders\SessionLogSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PlanningEvaluationSeeder::class);
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(SessionLogSeeder::class);
    }
}
