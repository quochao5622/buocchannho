<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipments', function (Blueprint $table) {
            $table->id();
            $table->string('equipment_code')->unique();
            $table->string('name');
            $table->foreignId('category_id')->constrained('equipment_categories');
            $table->string('image')->nullable();
            $table->integer('quantity')->default(0);
            $table->string('status')->default('good')->index();
            $table->string('location')->nullable();
            $table->string('unit')->default('cái');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipments');
    }
};

