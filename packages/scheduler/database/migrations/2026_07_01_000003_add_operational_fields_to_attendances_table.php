<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (! Schema::hasColumn('attendances', 'session_note')) {
                $table->text('session_note')->nullable()->after('notes');
            }

            if (! Schema::hasColumn('attendances', 'absence_reason')) {
                $table->text('absence_reason')->nullable()->after('session_note');
            }

            if (! Schema::hasColumn('attendances', 'reported_by')) {
                $table->string('reported_by', 30)->nullable()->after('absence_reason');
            }

            if (! Schema::hasColumn('attendances', 'make_up_scheduled')) {
                $table->boolean('make_up_scheduled')->default(false)->after('reported_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'make_up_scheduled')) {
                $table->dropColumn('make_up_scheduled');
            }

            if (Schema::hasColumn('attendances', 'reported_by')) {
                $table->dropColumn('reported_by');
            }

            if (Schema::hasColumn('attendances', 'absence_reason')) {
                $table->dropColumn('absence_reason');
            }

            if (Schema::hasColumn('attendances', 'session_note')) {
                $table->dropColumn('session_note');
            }
        });
    }
};
