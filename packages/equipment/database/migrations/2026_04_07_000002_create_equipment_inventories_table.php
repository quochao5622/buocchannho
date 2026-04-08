<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_inventories', function (Blueprint $table) {
            $table->id();
            $table->string('inventory_code')->unique();
            $table->foreignId('inspector_id')->constrained('users');
            $table->date('inventory_date')->index();
            $table->string('status')->default('draft')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_inventories');
    }
};

