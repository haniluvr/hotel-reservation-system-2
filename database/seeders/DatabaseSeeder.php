<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            BelmontHotelSeeder::class,
            // PromoCodeSeeder::class, // Uncomment if you want to seed promo codes later
        ]);

        // Only create users if they don't exist (preserve existing login credentials)
        if (!User::where('email', 'admin@belmonthotel.com')->exists()) {
            User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@belmonthotel.com',
            ]);
        }

        if (!User::where('email', 'test@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }
    }
}
