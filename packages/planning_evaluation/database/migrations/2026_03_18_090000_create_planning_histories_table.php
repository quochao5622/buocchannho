<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planning_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planning_id')->constrained('plannings')->cascadeOnDelete();
            $table->json('snapshot');
            $table->unsignedBigInteger('saved_by')->nullable();
            $table->timestamps();

            $table->index(['planning_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planning_histories');
    }
};
