<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (! Schema::hasColumn('attendances', 'make_up_for_attendance_id')) {
                $table->foreignId('make_up_for_attendance_id')
                    ->nullable()
                    ->constrained('attendances')
                    ->nullOnDelete()
                    ->after('actual_employee_id');
            }

            if (! Schema::hasColumn('attendances', 'substitute_reason')) {
                $table->string('substitute_reason')->nullable()->after('make_up_for_attendance_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'substitute_reason')) {
                $table->dropColumn('substitute_reason');
            }

            if (Schema::hasColumn('attendances', 'make_up_for_attendance_id')) {
                $table->dropConstrainedForeignId('make_up_for_attendance_id');
            }
        });
    }
};
