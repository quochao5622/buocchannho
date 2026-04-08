<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_inventory_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_inventory_id')->constrained('equipment_inventories')->cascadeOnDelete();
            $table->foreignId('equipment_id')->constrained('equipments');
            $table->integer('quantity_expected')->default(0);
            $table->integer('quantity_actual')->default(0);
            $table->string('status')->default('good')->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['equipment_inventory_id', 'equipment_id'], 'eq_inv_detail_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_inventory_details');
    }
};

