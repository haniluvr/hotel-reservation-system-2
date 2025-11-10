<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Room;
use App\Models\Reservation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingQueueSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();
        $users = User::all();
        $rooms = Room::all();
        $reservations = Reservation::all();
        
        if ($users->isEmpty() || $rooms->isEmpty()) {
            $this->command->warn('Users or Rooms not found. Please run UserSeeder and ensure rooms exist.');
            return;
        }
        
        // Generate booking queue entries - mix of linked to reservations and standalone
        $queueCount = rand(50, 100);
        
        for ($i = 0; $i < $queueCount; $i++) {
            $user = $users->random();
            $room = $rooms->random();
            
            // 60% linked to existing reservations, 40% standalone
            $reservation = null;
            if (rand(1, 100) <= 60 && $reservations->isNotEmpty()) {
                $reservation = $reservations->random();
                $user = $reservation->user;
                $room = $reservation->room;
            }
            
            // Determine status
            $status = $faker->randomElement(['pending', 'processing', 'completed', 'failed']);
            $priority = rand(0, 10);
            $processedAt = null;
            $errorMessage = null;
            
            if ($status === 'completed') {
                $processedAt = Carbon::now()->subDays(rand(1, 180))->addHours(rand(1, 24));
            } elseif ($status === 'failed') {
                $errorMessage = $faker->randomElement([
                    'Room no longer available',
                    'Payment processing failed',
                    'Invalid booking request',
                    'System error during processing',
                    'Booking timeout',
                ]);
                $processedAt = Carbon::now()->subDays(rand(1, 90))->addHours(rand(1, 12));
            } elseif ($status === 'processing') {
                $processedAt = Carbon::now()->subHours(rand(1, 48));
            }
            
            // Generate request data
            $checkInDate = $reservation 
                ? (is_string($reservation->check_in_date) 
                    ? Carbon::parse($reservation->check_in_date) 
                    : $reservation->check_in_date)
                : Carbon::now()->addDays(rand(1, 90));
            $checkOutDate = $reservation
                ? (is_string($reservation->check_out_date) 
                    ? Carbon::parse($reservation->check_out_date) 
                    : $reservation->check_out_date)
                : Carbon::now()->addDays(rand(2, 100));
            
            $requestData = [
                'check_in_date' => $checkInDate->format('Y-m-d'),
                'check_out_date' => $checkOutDate->format('Y-m-d'),
                'adults' => $reservation ? $reservation->adults : rand(1, 4),
                'children' => $reservation ? $reservation->children : rand(0, 2),
                'special_requests' => $faker->optional(0.3)->randomElement([
                    'Late check-in',
                    'Early check-in',
                    'Non-smoking room',
                    'High floor preferred',
                ]),
            ];
            
            $createdAt = $reservation 
                ? Carbon::parse($reservation->created_at)
                : Carbon::now()->subDays(rand(1, 180));
            
            DB::table('booking_queue')->insert([
                'user_id' => $user->id,
                'room_id' => $room->id,
                'request_data' => json_encode($requestData),
                'status' => $status,
                'priority' => $priority,
                'processed_at' => $processedAt?->format('Y-m-d H:i:s'),
                'error_message' => $errorMessage,
                'created_at' => $createdAt->format('Y-m-d H:i:s'),
                'updated_at' => $processedAt ? $processedAt->format('Y-m-d H:i:s') : $createdAt->format('Y-m-d H:i:s'),
            ]);
        }
    }
}
