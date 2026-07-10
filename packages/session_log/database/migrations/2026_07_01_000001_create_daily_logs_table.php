<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('log_date');
            $table->string('emotion');
            $table->string('focus_level');
            $table->string('cooperation_level');
            $table->text('eating_note')->nullable();
            $table->text('sleeping_note')->nullable();
            $table->text('hygiene_note')->nullable();
            $table->text('general_note')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();

            $table->unique(['student_id', 'log_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_logs');
    }
};
