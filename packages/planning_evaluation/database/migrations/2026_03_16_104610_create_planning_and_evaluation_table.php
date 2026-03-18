<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plannings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();

            $table->json('planning_details')->nullable();

            $table->string('status')->default('published');
            $table->timestamps();
        });

        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();

            $table->foreignId('planning_id')->nullable()->constrained('plannings')->nullOnDelete();
            $table->timestamps();

            // đánh giá
            $table->json('evaluation_details')->nullable();
            $table->string('status')->default('published');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
        Schema::dropIfExists('plannings');
    }
};
