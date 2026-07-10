<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_leave_requests', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'status',
                'approved_by',
                'approved_at',
                'rejection_reason',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('student_leave_requests', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('reason');
            $table->foreignId('approved_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable()->after('approved_by');
            $table->text('rejection_reason')->nullable()->after('approved_at');
        });
    }
};
