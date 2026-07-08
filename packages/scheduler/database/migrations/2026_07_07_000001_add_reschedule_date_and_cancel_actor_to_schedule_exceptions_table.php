<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedule_exceptions', function (Blueprint $table) {
            if (! Schema::hasColumn('schedule_exceptions', 'new_exception_date')) {
                $table->date('new_exception_date')->nullable()->after('exception_date');
            }

            if (! Schema::hasColumn('schedule_exceptions', 'cancel_actor')) {
                $table->string('cancel_actor', 20)->nullable()->after('action');
            }

            $table->index(['action', 'new_employee_id'], 'schedule_exceptions_action_new_employee_idx');
            $table->index(['new_exception_date'], 'schedule_exceptions_new_exception_date_idx');
        });
    }

    public function down(): void
    {
        Schema::table('schedule_exceptions', function (Blueprint $table) {
            if (Schema::hasColumn('schedule_exceptions', 'cancel_actor')) {
                $table->dropColumn('cancel_actor');
            }

            if (Schema::hasColumn('schedule_exceptions', 'new_exception_date')) {
                $table->dropColumn('new_exception_date');
            }

            $table->dropIndex('schedule_exceptions_action_new_employee_idx');
            $table->dropIndex('schedule_exceptions_new_exception_date_idx');
        });
    }
};
