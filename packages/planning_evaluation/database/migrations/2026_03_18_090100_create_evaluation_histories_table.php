<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')->constrained('evaluations')->cascadeOnDelete();
            $table->json('snapshot');
            $table->unsignedBigInteger('saved_by')->nullable();
            $table->timestamps();

            $table->index(['evaluation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_histories');
    }
};
