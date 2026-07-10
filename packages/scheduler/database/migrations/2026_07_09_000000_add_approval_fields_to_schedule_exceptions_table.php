<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedule_exceptions', function (Blueprint $table) {
            $table->string('status')->default('approved')->after('notes');
            $table->unsignedBigInteger('requested_by')->nullable()->after('status');
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('requested_by');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->text('review_note')->nullable()->after('reviewed_at');

            $table->foreign('requested_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('schedule_exceptions', function (Blueprint $table) {
            $table->dropForeign(['requested_by']);
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn(['status', 'requested_by', 'reviewed_by', 'reviewed_at', 'review_note']);
        });
    }
};
