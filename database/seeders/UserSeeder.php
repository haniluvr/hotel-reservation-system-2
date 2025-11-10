<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();
        
        // Generate 100 customers
        for ($i = 0; $i < 100; $i++) {
            // Registration dates from past year to present
            $createdAt = Carbon::now()->subYear()->addDays(rand(0, 365));
            
            User::create([
                'name' => $faker->name(),
                'email' => $faker->unique()->safeEmail(),
                'phone' => $faker->phoneNumber(),
                'password' => Hash::make('password'), // Default password for all seeded users
                'email_verified_at' => $faker->boolean(80) ? $createdAt->copy()->addMinutes(rand(5, 60)) : null, // 80% verified
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addDays(rand(0, 30)),
            ]);
        }
    }
}
