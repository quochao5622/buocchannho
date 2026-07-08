<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_attendances', function (Blueprint $table) {
            if (! Schema::hasColumn('employee_attendances', 'session')) {
                $table->string('session', 20)->nullable()->after('date');
            }
        });

        DB::table('employee_attendances')
            ->orderBy('id')
            ->get()
            ->each(function ($record): void {
                $session = 'morning';

                if ($record->check_in_at) {
                    $checkInAt = Carbon::parse($record->check_in_at);
                    $currentTime = Carbon::createFromFormat('H:i:s', $checkInAt->format('H:i:s'));
                    $morningEnd = Carbon::createFromTimeString((string) (settings('office_morning_end') ?? '12:00:00'));
                    $eveningStart = Carbon::createFromTimeString((string) (settings('office_evening_start') ?? '18:00:00'));

                    if ($currentTime->lte($morningEnd)) {
                        $session = 'morning';
                    } elseif ($currentTime->lt($eveningStart)) {
                        $session = 'afternoon';
                    } else {
                        $session = 'evening';
                    }
                }

                DB::table('employee_attendances')
                    ->where('id', $record->id)
                    ->update(['session' => $session]);
            });

        Schema::table('employee_attendances', function (Blueprint $table) {
            $table->string('session', 20)->default('morning')->nullable(false)->change();
        });

        Schema::table('employee_attendances', function (Blueprint $table) {
            if (! $this->hasIndex('employee_attendances', 'employee_attendances_employee_date_session_unique')) {
                $table->unique(['employee_id', 'date', 'session'], 'employee_attendances_employee_date_session_unique');
            }

            if (! $this->hasIndex('employee_attendances', 'employee_attendances_date_session_index')) {
                $table->index(['date', 'session'], 'employee_attendances_date_session_index');
            }
        });

        Schema::table('employee_attendances', function (Blueprint $table) {
            if ($this->hasIndex('employee_attendances', 'employee_attendances_employee_id_date_unique')) {
                $table->dropUnique('employee_attendances_employee_id_date_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_attendances', function (Blueprint $table) {
            if ($this->hasIndex('employee_attendances', 'employee_attendances_employee_date_session_unique')) {
                $table->dropUnique('employee_attendances_employee_date_session_unique');
            }

            if ($this->hasIndex('employee_attendances', 'employee_attendances_date_session_index')) {
                $table->dropIndex('employee_attendances_date_session_index');
            }

            if (! $this->hasIndex('employee_attendances', 'employee_attendances_employee_id_date_unique')) {
                $table->unique(['employee_id', 'date'], 'employee_attendances_employee_id_date_unique');
            }

            $table->dropColumn('session');
        });
    }

    private function hasIndex(string $tableName, string $indexName): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('{$tableName}')");

            foreach ($indexes as $index) {
                if (($index->name ?? null) === $indexName) {
                    return true;
                }
            }

            return false;
        }

        return DB::table('information_schema.statistics')
            ->whereRaw('table_schema = schema()')
            ->where('table_name', $tableName)
            ->where('index_name', $indexName)
            ->exists();
    }
};
