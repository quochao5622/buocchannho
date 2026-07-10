<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::table('schedules', function (Blueprint $table) {
            if (Schema::hasColumn('schedules', 'equipment_id')) {
                $table->dropForeign(['equipment_id']);
                $table->dropColumn('equipment_id');
            }
            $table->foreignId('classroom_id')->nullable()->after('employee_id')->constrained('classrooms')->nullOnDelete();
        });

        Schema::table('schedule_exceptions', function (Blueprint $table) {
            if (Schema::hasColumn('schedule_exceptions', 'new_equipment_id')) {
                $table->dropForeign(['new_equipment_id']);
                $table->dropColumn('new_equipment_id');
            }
            $table->foreignId('new_classroom_id')->nullable()->after('new_employee_id')->constrained('classrooms')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('schedule_exceptions', function (Blueprint $table) {
            if (Schema::hasColumn('schedule_exceptions', 'new_classroom_id')) {
                $table->dropForeign(['new_classroom_id']);
                $table->dropColumn('new_classroom_id');
            }
            $table->foreignId('new_equipment_id')->nullable()->after('new_employee_id')->constrained('equipments')->nullOnDelete();
        });

        Schema::table('schedules', function (Blueprint $table) {
            if (Schema::hasColumn('schedules', 'classroom_id')) {
                $table->dropForeign(['classroom_id']);
                $table->dropColumn('classroom_id');
            }
            $table->foreignId('equipment_id')->nullable()->after('employee_id')->constrained('equipments')->nullOnDelete();
        });

        Schema::dropIfExists('classrooms');
    }
};
