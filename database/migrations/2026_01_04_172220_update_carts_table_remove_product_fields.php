<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Remove product-related fields from carts table since they moved to cart_items.
     */
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['product_id']);

            // Then drop the columns
            $table->dropColumn(['product_id', 'quantity', 'unit_price', 'selected_addons']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('unit_price', 8, 2);
            $table->json('selected_addons')->nullable();
        });
    }
};
