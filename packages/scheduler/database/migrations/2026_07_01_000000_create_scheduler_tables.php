<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('equipment_id')->nullable()->constrained('equipments')->nullOnDelete(); // Phòng chức năng
            $table->string('title');
            $table->enum('type', ['individual', 'group'])->default('individual');
            $table->unsignedTinyInteger('day_of_week')->nullable(); // 1: Chủ nhật, 2-7: Thứ 2 - Thứ 7. Null nếu lịch 1 lần.
            $table->time('start_time');
            $table->time('end_time');
            $table->date('start_date');
            $table->date('end_date')->nullable(); // Null nếu lịch vô hạn
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['employee_id', 'status']);
        });

        Schema::create('schedule_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules')->cascadeOnDelete();
            $table->date('exception_date');
            $table->enum('action', ['cancel', 'reschedule', 'substitute']);
            $table->foreignId('new_employee_id')->nullable()->constrained('employees')->nullOnDelete(); // Giáo viên dạy thay
            $table->foreignId('new_equipment_id')->nullable()->constrained('equipments')->nullOnDelete(); // Phòng thay thế
            $table->time('new_start_time')->nullable();
            $table->time('new_end_time')->nullable();
            $table->text('reason')->nullable(); // Lý do hủy/dạy thay
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['schedule_id', 'exception_date']);
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('schedule_id')->nullable()->constrained('schedules')->nullOnDelete();
            $table->date('attendance_date');
            $table->enum('status', ['present', 'absent_excused', 'absent_unexcused', 'late'])->default('present');
            $table->dateTime('check_in_at')->nullable();
            $table->dateTime('check_out_at')->nullable();
            $table->decimal('total_hours', 4, 2)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('verified_by_employee_id')->constrained('employees')->cascadeOnDelete(); // Người xác nhận điểm danh
            $table->foreignId('actual_employee_id')->nullable()->constrained('employees')->nullOnDelete(); // Giáo viên thực tế đứng lớp
            $table->timestamps();

            $table->index(['student_id', 'attendance_date']);
            $table->index(['schedule_id', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('schedule_exceptions');
        Schema::dropIfExists('schedules');
    }
};
