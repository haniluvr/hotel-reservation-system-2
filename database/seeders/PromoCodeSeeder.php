<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PromoCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $promoCodes = [
            [
                'code' => 'WELCOME10',
                'name' => 'Welcome Discount',
                'description' => '10% off for new customers',
                'type' => 'percentage',
                'value' => 10.00,
                'minimum_amount' => 5000.00,
                'usage_limit' => 100,
                'used_count' => 0,
                'valid_from' => now()->subDays(30),
                'valid_until' => now()->addDays(30),
                'is_active' => true,
            ],
            [
                'code' => 'EARLYBIRD',
                'name' => 'Early Bird Special',
                'description' => '15% off for bookings made 30 days in advance',
                'type' => 'percentage',
                'value' => 15.00,
                'minimum_amount' => 8000.00,
                'usage_limit' => 50,
                'used_count' => 0,
                'valid_from' => now()->subDays(15),
                'valid_until' => now()->addDays(45),
                'is_active' => true,
            ],
            [
                'code' => 'FAMILY500',
                'name' => 'Family Package',
                'description' => 'PHP 500 off for family bookings',
                'type' => 'fixed_amount',
                'value' => 500.00,
                'minimum_amount' => 10000.00,
                'usage_limit' => 25,
                'used_count' => 0,
                'valid_from' => now()->subDays(10),
                'valid_until' => now()->addDays(60),
                'is_active' => true,
            ],
            [
                'code' => 'SUMMER20',
                'name' => 'Summer Vacation',
                'description' => '20% off for summer bookings',
                'type' => 'percentage',
                'value' => 20.00,
                'minimum_amount' => 12000.00,
                'usage_limit' => 75,
                'used_count' => 0,
                'valid_from' => now()->subDays(5),
                'valid_until' => now()->addDays(90),
                'is_active' => true,
            ],
            [
                'code' => 'LOYALTY1000',
                'name' => 'Loyalty Reward',
                'description' => 'PHP 1000 off for returning customers',
                'type' => 'fixed_amount',
                'value' => 1000.00,
                'minimum_amount' => 15000.00,
                'usage_limit' => 15,
                'used_count' => 0,
                'valid_from' => now()->subDays(20),
                'valid_until' => now()->addDays(30),
                'is_active' => true,
            ],
            [
                'code' => 'WEEKEND25',
                'name' => 'Weekend Getaway',
                'description' => '25% off for weekend stays',
                'type' => 'percentage',
                'value' => 25.00,
                'minimum_amount' => 6000.00,
                'usage_limit' => 40,
                'used_count' => 0,
                'valid_from' => now()->subDays(7),
                'valid_until' => now()->addDays(21),
                'is_active' => true,
            ],
            [
                'code' => 'BUSINESS200',
                'name' => 'Business Traveler',
                'description' => 'PHP 200 off for business bookings',
                'type' => 'fixed_amount',
                'value' => 200.00,
                'minimum_amount' => 3000.00,
                'usage_limit' => 100,
                'used_count' => 0,
                'valid_from' => now()->subDays(14),
                'valid_until' => now()->addDays(45),
                'is_active' => true,
            ],
            [
                'code' => 'HONEYMOON30',
                'name' => 'Honeymoon Special',
                'description' => '30% off for honeymoon bookings',
                'type' => 'percentage',
                'value' => 30.00,
                'minimum_amount' => 20000.00,
                'usage_limit' => 10,
                'used_count' => 0,
                'valid_from' => now()->subDays(3),
                'valid_until' => now()->addDays(120),
                'is_active' => true,
            ],
        ];

        DB::table('promo_codes')->insert($promoCodes);
    }
}
