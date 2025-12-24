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
        if (!Schema::hasTable('receipts')) {
            Schema::create('receipts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->onDelete('cascade');
                $table->string('receipt_number')->unique();
                $table->string('pdf_path');
                $table->decimal('total_amount', 10, 2);
                $table->json('order_data')->nullable();
                $table->string('template_used')->default('customer'); // compact/detailed/customer/vendor
                $table->timestamp('generated_at');
                $table->timestamps();

                // Indexes for performance
                $table->index(['order_id', 'generated_at']);
                $table->index('receipt_number');
                $table->index('generated_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
