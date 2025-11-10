<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admins = [
            [
                'name' => 'Admin User',
                'email' => 'admin@belmonthotel.com',
                'password' => Hash::make('password'),
                'is_active' => true,
            ],
            [
                'name' => 'Manager Admin',
                'email' => 'manager@belmonthotel.com',
                'password' => Hash::make('password'),
                'is_active' => true,
            ],
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@belmonthotel.com',
                'password' => Hash::make('password'),
                'is_active' => true,
            ],
        ];

        foreach ($admins as $admin) {
            Admin::updateOrCreate(
                ['email' => $admin['email']],
                $admin
            );
        }
    }
}
