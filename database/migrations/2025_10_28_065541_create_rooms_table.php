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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->onDelete('cascade');
            $table->string('room_type'); // Deluxe Room, Executive Suite, etc.
            $table->text('description')->nullable();
            $table->decimal('price_per_night', 10, 2);
            $table->integer('quantity')->default(1); // Total rooms of this type
            $table->integer('available_quantity')->default(1); // Current available rooms
            $table->integer('max_guests')->default(2);
            $table->integer('max_adults')->default(2);
            $table->integer('max_children')->default(0);
            $table->json('amenities')->nullable(); // Room-specific amenities
            $table->json('images')->nullable(); // Room images
            $table->string('size')->nullable(); // Room size in sq ft
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Critical indexes for inventory management
            $table->index(['hotel_id', 'room_type']);
            $table->index('available_quantity');
            $table->index('price_per_night');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
