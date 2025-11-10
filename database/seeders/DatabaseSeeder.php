<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Truncate all tables except rooms
        $this->truncateTables();
        
        // Run seeders in correct order
        $this->call([
            PromoCodeSeeder::class,
            UserSeeder::class,
            AdminSeeder::class,
            ReservationSeeder::class,
            PaymentSeeder::class,
            ReviewSeeder::class,
            TransactionLogSeeder::class,
            BookingQueueSeeder::class,
        ]);
    }
    
    /**
     * Truncate all tables except rooms
     */
    private function truncateTables(): void
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        $tablesToTruncate = [
            'users',
            'reservations',
            'payments',
            'reviews',
            'transaction_logs',
            'promo_codes',
            'booking_queue',
            'archived_users',
            'sessions',
            'password_reset_tokens',
            'cache',
            'cache_locks',
            'failed_jobs',
            'jobs',
            'job_batches',
            'admins',
        ];
        
        foreach ($tablesToTruncate as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
