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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // Customer notifications
            $table->foreignId('vendor_id')->nullable()->constrained()->onDelete('cascade'); // Vendor notifications
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('type')->nullable(); // 'order', 'payment', 'system', 
            $table->string('title')->nullable();
            $table->text('message');
            $table->json('data')->nullable(); // Additional context data
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Performance indexes
            $table->index(['user_id', 'read_at']);
            $table->index(['vendor_id', 'read_at']);
            $table->index('order_id');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
