<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_attendances', function (Blueprint $table) {
            // Geofencing & IP verification flags
            $table->boolean('flagged_location')->default(false)->after('notes');
            $table->string('verification_status')->default('approved')->after('flagged_location'); // pending, approved, rejected
            $table->boolean('unassigned_schedule')->default(false)->after('verification_status');
            $table->foreignId('schedule_id')->nullable()->constrained('schedules')->nullOnDelete()->after('unassigned_schedule');

            // Open session / auto-close
            $table->boolean('auto_closed')->default(false)->after('schedule_id');

            // Missing check-in flag
            $table->boolean('flagged_missing_checkin')->default(false)->after('auto_closed');

            // Correction tracking
            $table->foreignId('corrected_by')->nullable()->constrained('users')->nullOnDelete()->after('flagged_missing_checkin');
            $table->timestamp('corrected_at')->nullable()->after('corrected_by');
        });
    }

    public function down(): void
    {
        Schema::table('employee_attendances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('corrected_by');
            $table->dropConstrainedForeignId('schedule_id');
            $table->dropColumn([
                'flagged_location',
                'verification_status',
                'unassigned_schedule',
                'auto_closed',
                'flagged_missing_checkin',
                'corrected_at',
            ]);
        });
    }
};
