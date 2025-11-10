<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove foreign key constraint from rooms table
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropForeign(['hotel_id']);
        });
        
        // Make hotel_id nullable in rooms table
        Schema::table('rooms', function (Blueprint $table) {
            $table->unsignedBigInteger('hotel_id')->nullable()->change();
        });
        
        // Set existing hotel_id values to NULL in rooms
        DB::table('rooms')->update(['hotel_id' => null]);
        
        // Remove foreign key constraint from reviews table
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['hotel_id']);
        });
        
        // Make hotel_id nullable in reviews table
        Schema::table('reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('hotel_id')->nullable()->change();
        });
        
        // Set existing hotel_id values to NULL in reviews
        DB::table('reviews')->update(['hotel_id' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore foreign key constraint for rooms
        Schema::table('rooms', function (Blueprint $table) {
            $table->unsignedBigInteger('hotel_id')->nullable(false)->change();
            $table->foreign('hotel_id')->references('id')->on('hotels')->onDelete('cascade');
        });
        
        // Restore foreign key constraint for reviews
        Schema::table('reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('hotel_id')->nullable(false)->change();
            $table->foreign('hotel_id')->references('id')->on('hotels')->onDelete('cascade');
        });
    }
};
