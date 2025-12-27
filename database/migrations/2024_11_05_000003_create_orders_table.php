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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->string('vendor_group_id')->nullable(); // Links orders from same checkout
            $table->string('order_number')->unique();
            $table->enum('status', [
                'pending',      // Order just placed
                'accepted',     // Vendor accepted
                'ready_for_pickup', // Food is ready (NOW THE FINAL STATUS)
                'cancelled',    // Vendor declined
            ])->default('pending');
            $table->decimal('total_amount', 10, 2);
            $table->string('payment_method')->default('cash'); // 'qr_code' or 'cash'
            $table->string('table_number')->nullable();
            $table->text('special_instructions')->nullable();
            $table->string('payment_proof_url')->nullable(); // Local storage path
            $table->string('receipt_url')->nullable(); // Generated receipt path
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
