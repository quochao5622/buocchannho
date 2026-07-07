<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('schedules') || ! Schema::hasColumn('schedules', 'day_of_week')) {
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $columnType = DB::selectOne(
            "SELECT DATA_TYPE
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'schedules'
              AND COLUMN_NAME = 'day_of_week'
            LIMIT 1"
        );

        if (! $columnType) {
            return;
        }

        if (strtolower((string) $columnType->DATA_TYPE) !== 'json') {
            DB::statement('ALTER TABLE `schedules` MODIFY `day_of_week` JSON NULL');
        }

        // Normalize older scalar values (e.g. 2) into JSON arrays (e.g. [2]).
        DB::statement("UPDATE `schedules`
            SET `day_of_week` = JSON_ARRAY(CAST(JSON_UNQUOTE(`day_of_week`) AS UNSIGNED))
            WHERE `day_of_week` IS NOT NULL
              AND JSON_TYPE(`day_of_week`) IN ('INTEGER', 'DOUBLE', 'STRING')");
    }

    public function down(): void
    {
        if (! Schema::hasTable('schedules') || ! Schema::hasColumn('schedules', 'day_of_week')) {
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $columnType = DB::selectOne(
            "SELECT DATA_TYPE
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'schedules'
              AND COLUMN_NAME = 'day_of_week'
            LIMIT 1"
        );

        if (! $columnType || strtolower((string) $columnType->DATA_TYPE) !== 'json') {
            return;
        }

        DB::statement("UPDATE `schedules`
            SET `day_of_week` = CASE
                WHEN JSON_TYPE(`day_of_week`) = 'ARRAY' THEN CAST(JSON_UNQUOTE(JSON_EXTRACT(`day_of_week`, '$[0]')) AS UNSIGNED)
                ELSE CAST(JSON_UNQUOTE(`day_of_week`) AS UNSIGNED)
            END
            WHERE `day_of_week` IS NOT NULL");

        DB::statement('ALTER TABLE `schedules` MODIFY `day_of_week` TINYINT UNSIGNED NULL');
    }
};
