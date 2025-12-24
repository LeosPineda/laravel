<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            Schema::table('carts', function (Blueprint $table) {
                // Optimize cart queries for active cart items
                $table->index(['user_id', 'status']);
                $table->index(['user_id', 'product_id']);
                $table->index(['vendor_id']);
            });
        } catch (\Exception $e) {
            // Index might already exist, continue
        }

        try {
            Schema::table('orders', function (Blueprint $table) {
                // Optimize order queries for customer orders
                $table->index(['customer_id', 'status']);
                $table->index(['vendor_id', 'status']);
                $table->index(['created_at']);
                $table->index(['payment_method']);
            });
        } catch (\Exception $e) {
            // Index might already exist, continue
        }

        try {
            Schema::table('order_items', function (Blueprint $table) {
                // Optimize order item queries
                $table->index(['order_id']);
                $table->index(['product_id']);
            });
        } catch (\Exception $e) {
            // Index might already exist, continue
        }

        try {
            Schema::table('products', function (Blueprint $table) {
                // Optimize product queries for vendor menus
                $table->index(['vendor_id', 'stock_quantity']);
                $table->index(['vendor_id', 'is_active']);
                $table->index(['category']);
            });
        } catch (\Exception $e) {
            // Index might already exist, continue
        }

        try {
            Schema::table('notifications', function (Blueprint $table) {
                // Optimize notification queries for vendors
                $table->index(['vendor_id', 'created_at']);
                $table->index(['vendor_id', 'is_read']);
                $table->index(['order_id']);
            });
        } catch (\Exception $e) {
            // Index might already exist, continue
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['user_id', 'product_id']);
            $table->dropIndex(['vendor_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['customer_id', 'status']);
            $table->dropIndex(['vendor_id', 'status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['payment_method']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['order_id']);
            $table->dropIndex(['product_id']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['vendor_id', 'stock_quantity']);
            $table->dropIndex(['vendor_id', 'is_active']);
            $table->dropIndex(['category']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['vendor_id', 'created_at']);
            $table->dropIndex(['vendor_id', 'is_read']);
            $table->dropIndex(['order_id']);
        });
    }
};
