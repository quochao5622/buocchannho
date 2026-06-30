<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipments', function (Blueprint $table) {
            if (Schema::hasColumn('equipments', 'status')) {
                $table->dropIndex(['status']);
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('equipments', 'quantity')) {
                $table->renameColumn('quantity', 'quantity_good');
            }
        });

        Schema::table('equipments', function (Blueprint $table) {
            $table->integer('quantity_broken')->default(0)->after('quantity_good');
            $table->integer('quantity_missing')->default(0)->after('quantity_broken');
            $table->integer('quantity')->default(0)->after('image');
        });

        // Copy quantity_good to quantity
        DB::table('equipments')->update([
            'quantity' => DB::raw('quantity_good'),
        ]);

        Schema::table('equipment_inventory_details', function (Blueprint $table) {
            if (Schema::hasColumn('equipment_inventory_details', 'quantity_expected')) {
                $table->dropColumn('quantity_expected');
            }
            if (Schema::hasColumn('equipment_inventory_details', 'quantity_actual')) {
                $table->dropColumn('quantity_actual');
            }
            if (Schema::hasColumn('equipment_inventory_details', 'status')) {
                $table->dropIndex(['status']);
                $table->dropColumn('status');
            }
            $table->integer('quantity_expected_good')->default(0)->after('equipment_id');
            $table->integer('quantity_actual_good')->default(0)->after('quantity_expected_good');
            $table->integer('quantity_expected_broken')->default(0)->after('quantity_actual_good');
            $table->integer('quantity_actual_broken')->default(0)->after('quantity_expected_broken');
            $table->integer('quantity_expected_missing')->default(0)->after('quantity_actual_broken');
            $table->integer('quantity_actual_missing')->default(0)->after('quantity_expected_missing');
        });
    }

    public function down(): void
    {
        Schema::table('equipments', function (Blueprint $table) {
            if (Schema::hasColumn('equipments', 'quantity')) {
                $table->dropColumn('quantity');
            }
        });

        Schema::table('equipments', function (Blueprint $table) {
            if (Schema::hasColumn('equipments', 'quantity_good')) {
                $table->renameColumn('quantity_good', 'quantity');
            }
            $table->string('status')->default('good')->index()->after('quantity');
            $table->dropColumn(['quantity_broken', 'quantity_missing']);
        });

        Schema::table('equipment_inventory_details', function (Blueprint $table) {
            $table->integer('quantity_expected')->default(0)->after('equipment_id');
            $table->integer('quantity_actual')->default(0)->after('quantity_expected');
            $table->string('status')->default('good')->index()->after('quantity_actual');
            $table->dropColumn([
                'quantity_expected_good', 'quantity_actual_good',
                'quantity_expected_broken', 'quantity_actual_broken',
                'quantity_expected_missing', 'quantity_actual_missing',
            ]);
        });
    }
};
