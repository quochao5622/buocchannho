<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('employee_code')->nullable()->unique()->after('id');
            $table->string('address')->nullable()->after('phone');
            $table->enum('employment_type', ['full-time', 'part-time', 'intern', 'contract'])->nullable()->after('position');
            $table->date('probation_end_at')->nullable()->after('hired_at');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropUnique('employees_employee_code_unique');
            $table->dropColumn(['employee_code', 'address', 'employment_type', 'probation_end_at']);
        });
    }
};