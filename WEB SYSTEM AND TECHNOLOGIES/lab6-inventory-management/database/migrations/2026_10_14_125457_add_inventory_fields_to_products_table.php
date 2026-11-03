<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('sku')->nullable()->unique();
            $table->string('category')->nullable();
            $table->unsignedInteger('reorder_level')->default(10);
            $table->string('supplier')->nullable();
            $table->decimal('unit_price', 10, 2)->default(0);
        });

        // Preserve existing prices by copying them into unit_price.
        DB::table('products')->update([
            'unit_price' => DB::raw('price'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_sku_unique');

            $table->dropColumn([
                'sku',
                'category',
                'reorder_level',
                'supplier',
                'unit_price',
            ]);
        });
    }
};