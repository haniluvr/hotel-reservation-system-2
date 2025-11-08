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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->onDelete('cascade');
            $table->string('xendit_invoice_id')->unique(); // Xendit payment reference
            $table->string('payment_method'); // credit_card, e_wallet, bank_transfer, etc.
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('PHP');
            $table->enum('status', ['pending', 'paid', 'failed', 'expired', 'cancelled'])->default('pending');
            $table->string('payment_url')->nullable(); // Xendit payment link
            $table->json('payment_details')->nullable(); // Additional payment info
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();
            
            // Indexes for payment tracking
            $table->index(['reservation_id', 'status']);
            $table->index('xendit_invoice_id');
            $table->index('status');
            $table->index('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
