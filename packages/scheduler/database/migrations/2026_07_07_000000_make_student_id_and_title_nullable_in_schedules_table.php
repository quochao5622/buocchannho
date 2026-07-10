<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->unsignedBigInteger('student_id')->nullable()->change();
            $table->string('title')->nullable()->change();
        });

    }

    public function down(): void
    {

        Schema::table('schedules', function (Blueprint $table) {
            $table->string('title')->nullable(false)->change();
            $table->unsignedBigInteger('student_id')->nullable(false)->change();
        });
    }
};
