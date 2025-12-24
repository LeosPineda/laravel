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
            $table->string('vendor_group_id')->nullable();
            $table->string('order_number')->unique();
            $table->enum('status', [
                'pending',
                'accepted',
                'preparing',
                'ready_for_pickup',
                'completed',
                'cancelled'
            ])->default('pending');
            $table->decimal('total_amount', 10, 2);
            $table->string('payment_method')->default('qr_code'); // 'qr_code' or 'cash'
            $table->string('payment_status')->default('pending');
            $table->string('table_number')->nullable();
            $table->text('vendor_special_instructions')->nullable();
            $table->text('special_instructions')->nullable();
            $table->text('receipt_url')->nullable();
            $table->decimal('vendor_subtotal', 10, 2)->nullable();
            $table->text('payment_proof_url')->nullable();
            $table->text('cloudinary_proof_url')->nullable();

            $table->timestamp('verification_time')->nullable();
            $table->text('notes')->nullable();
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
