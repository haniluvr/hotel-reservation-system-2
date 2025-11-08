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
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('address');
            $table->string('city');
            $table->string('country');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('star_rating')->default(3);
            $table->json('amenities')->nullable(); // Pool, WiFi, Parking, etc.
            $table->json('images')->nullable(); // Array of image URLs
            $table->string('hotelbeds_code')->nullable(); // External API reference
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['city', 'country']);
            $table->index('star_rating');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};
