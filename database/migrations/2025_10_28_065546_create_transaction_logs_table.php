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
        Schema::create('transaction_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('room_id')->constrained()->onDelete('cascade');
            $table->string('action'); // 'reserve', 'confirm', 'cancel', 'checkout', 'inventory_adjustment'
            $table->json('before_state')->nullable(); // Room state before action
            $table->json('after_state')->nullable(); // Room state after action
            $table->integer('quantity_change')->default(0); // +1 or -1 for inventory changes
            $table->text('description')->nullable();
            $table->string('performed_by')->nullable(); // User ID or 'system'
            $table->timestamps();
            
            // Indexes for audit trail
            $table->index(['reservation_id', 'action']);
            $table->index(['room_id', 'action']);
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_logs');
    }
};
