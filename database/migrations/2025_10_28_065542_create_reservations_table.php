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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('room_id')->constrained()->onDelete('cascade');
            $table->string('reservation_number')->unique(); // Auto-generated booking reference
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->integer('adults');
            $table->integer('children')->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->string('promo_code')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed', 'no_show'])->default('pending');
            $table->text('special_requests')->nullable();
            $table->json('guest_details')->nullable(); // Additional guest information
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
            
            // Indexes for efficient queries
            $table->index(['user_id', 'status']);
            $table->index(['room_id', 'check_in_date', 'check_out_date']);
            $table->index('reservation_number');
            $table->index('status');
            $table->index('check_in_date');
            $table->index('check_out_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
