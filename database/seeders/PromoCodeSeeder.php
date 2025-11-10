<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PromoCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $nextYearMarch = Carbon::now()->addYear()->month(3)->endOfMonth();
        $pastYear = Carbon::now()->subYear();
        
        $promoCodes = [
            [
                'code' => 'BIRTHDAY15',
                'name' => 'Birthday Special',
                'description' => '15% off on your birthday month - celebrate with us!',
                'type' => 'percentage',
                'value' => 15.00,
                'minimum_amount' => 5000.00,
                'usage_limit' => 200,
                'used_count' => rand(10, 50),
                'valid_from' => $pastYear->copy(),
                'valid_until' => $nextYearMarch->copy(),
                'is_active' => true,
            ],
            [
                'code' => 'EARLYBIRD20',
                'name' => 'Early Bird Discount',
                'description' => '20% off for bookings made 30+ days in advance',
                'type' => 'percentage',
                'value' => 20.00,
                'minimum_amount' => 8000.00,
                'usage_limit' => 150,
                'used_count' => rand(20, 80),
                'valid_from' => $pastYear->copy()->addMonths(3),
                'valid_until' => $nextYearMarch->copy(),
                'is_active' => true,
            ],
            [
                'code' => 'NEWUSER10',
                'name' => 'New User Welcome',
                'description' => '10% off for first-time customers',
                'type' => 'percentage',
                'value' => 10.00,
                'minimum_amount' => 3000.00,
                'usage_limit' => 300,
                'used_count' => rand(50, 150),
                'valid_from' => $pastYear->copy(),
                'valid_until' => $nextYearMarch->copy(),
                'is_active' => true,
            ],
            [
                'code' => 'SENIORPWD',
                'name' => 'Senior & PWD Discount',
                'description' => 'PHP 500 off for senior citizens and PWD cardholders',
                'type' => 'fixed_amount',
                'value' => 500.00,
                'minimum_amount' => 4000.00,
                'usage_limit' => 100,
                'used_count' => rand(15, 40),
                'valid_from' => $pastYear->copy()->addMonths(2),
                'valid_until' => $nextYearMarch->copy(),
                'is_active' => true,
            ],
            [
                'code' => 'WEEKEND25',
                'name' => 'Weekend Getaway',
                'description' => '25% off for weekend stays (Friday-Sunday)',
                'type' => 'percentage',
                'value' => 25.00,
                'minimum_amount' => 6000.00,
                'usage_limit' => 120,
                'used_count' => rand(30, 70),
                'valid_from' => $pastYear->copy()->addMonths(4),
                'valid_until' => $nextYearMarch->copy(),
                'is_active' => true,
            ],
            [
                'code' => 'LONGSTAY30',
                'name' => 'Long Stay Discount',
                'description' => '30% off for stays of 5 nights or more',
                'type' => 'percentage',
                'value' => 30.00,
                'minimum_amount' => 15000.00,
                'usage_limit' => 80,
                'used_count' => rand(10, 35),
                'valid_from' => $pastYear->copy()->addMonths(1),
                'valid_until' => $nextYearMarch->copy(),
                'is_active' => true,
            ],
            [
                'code' => 'LASTMINUTE15',
                'name' => 'Last Minute Booking',
                'description' => '15% off for bookings made within 48 hours',
                'type' => 'percentage',
                'value' => 15.00,
                'minimum_amount' => 5000.00,
                'usage_limit' => 100,
                'used_count' => rand(20, 60),
                'valid_from' => $pastYear->copy()->addMonths(6),
                'valid_until' => $nextYearMarch->copy(),
                'is_active' => true,
            ],
        ];

        DB::table('promo_codes')->insert($promoCodes);
    }
}
