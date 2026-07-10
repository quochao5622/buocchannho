<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_leave_requests', function (Blueprint $table) {
            $table->boolean('half_day')->default(false)->after('end_date');
            $table->string('half_day_session')->nullable()->after('half_day');
        });
    }

    public function down(): void
    {
        Schema::table('student_leave_requests', function (Blueprint $table) {
            $table->dropColumn(['half_day', 'half_day_session']);
        });
    }
};
