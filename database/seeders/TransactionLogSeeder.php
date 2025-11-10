<?php

namespace Database\Seeders;

use App\Models\TransactionLog;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TransactionLogSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();
        $reservations = Reservation::all();
        
        if ($reservations->isEmpty()) {
            $this->command->warn('Reservations not found. Please run ReservationSeeder first.');
            return;
        }
        
        foreach ($reservations as $reservation) {
            $room = $reservation->room;
            $createdAt = Carbon::parse($reservation->created_at);
            
            // Log reservation creation
            TransactionLog::create([
                'reservation_id' => $reservation->id,
                'room_id' => $room->id,
                'action' => 'reserve',
                'before_state' => [
                    'available_quantity' => $room->available_quantity + 1,
                ],
                'after_state' => [
                    'available_quantity' => $room->available_quantity,
                ],
                'quantity_change' => -1,
                'description' => "Reservation {$reservation->reservation_number} created",
                'performed_by' => $reservation->user_id,
                'created_at' => $createdAt->format('Y-m-d H:i:s'),
                'updated_at' => $createdAt->format('Y-m-d H:i:s'),
            ]);
            
            // Log confirmation if reservation is confirmed
            if ($reservation->status === 'confirmed' && $reservation->confirmed_at) {
                $confirmedAt = Carbon::parse($reservation->confirmed_at);
                TransactionLog::create([
                    'reservation_id' => $reservation->id,
                    'room_id' => $room->id,
                    'action' => 'confirm',
                    'before_state' => [
                        'status' => 'pending',
                    ],
                    'after_state' => [
                        'status' => 'confirmed',
                    ],
                    'quantity_change' => 0,
                    'description' => "Reservation {$reservation->reservation_number} confirmed",
                    'performed_by' => $reservation->user_id,
                    'created_at' => $confirmedAt->format('Y-m-d H:i:s'),
                    'updated_at' => $confirmedAt->format('Y-m-d H:i:s'),
                ]);
            }
            
            // Log cancellation if reservation is cancelled
            if ($reservation->status === 'cancelled' && $reservation->cancelled_at) {
                $cancelledAt = Carbon::parse($reservation->cancelled_at);
                TransactionLog::create([
                    'reservation_id' => $reservation->id,
                    'room_id' => $room->id,
                    'action' => 'cancel',
                    'before_state' => [
                        'status' => $reservation->confirmed_at ? 'confirmed' : 'pending',
                        'available_quantity' => $room->available_quantity,
                    ],
                    'after_state' => [
                        'status' => 'cancelled',
                        'available_quantity' => $room->available_quantity + 1,
                    ],
                    'quantity_change' => 1,
                    'description' => "Reservation {$reservation->reservation_number} cancelled: {$reservation->cancellation_reason}",
                    'performed_by' => $reservation->user_id,
                    'created_at' => $cancelledAt->format('Y-m-d H:i:s'),
                    'updated_at' => $cancelledAt->format('Y-m-d H:i:s'),
                ]);
            }
            
            // Log checkout if reservation is completed
            if ($reservation->status === 'completed') {
                $checkOutDate = Carbon::parse($reservation->check_out_date);
                TransactionLog::create([
                    'reservation_id' => $reservation->id,
                    'room_id' => $room->id,
                    'action' => 'checkout',
                    'before_state' => [
                        'status' => 'confirmed',
                        'available_quantity' => $room->available_quantity,
                    ],
                    'after_state' => [
                        'status' => 'completed',
                        'available_quantity' => $room->available_quantity + 1,
                    ],
                    'quantity_change' => 1,
                    'description' => "Guest checked out from reservation {$reservation->reservation_number}",
                    'performed_by' => 'system',
                    'created_at' => $checkOutDate->format('Y-m-d H:i:s'),
                    'updated_at' => $checkOutDate->format('Y-m-d H:i:s'),
                ]);
            }
        }
        
        // Generate some inventory adjustment logs
        $rooms = Room::all();
        foreach ($rooms->random(min(10, $rooms->count())) as $room) {
            $adjustmentDate = Carbon::now()->subDays(rand(1, 180));
            $adjustmentType = $faker->randomElement(['maintenance', 'cleaning', 'renovation']);
            $quantityChange = rand(-2, 2);
            
            TransactionLog::create([
                'reservation_id' => null,
                'room_id' => $room->id,
                'action' => 'inventory_adjustment',
                'before_state' => [
                    'available_quantity' => $room->available_quantity - $quantityChange,
                ],
                'after_state' => [
                    'available_quantity' => $room->available_quantity,
                ],
                'quantity_change' => $quantityChange,
                'description' => "Inventory adjustment due to {$adjustmentType}",
                'performed_by' => 'system',
                'created_at' => $adjustmentDate->format('Y-m-d H:i:s'),
                'updated_at' => $adjustmentDate->format('Y-m-d H:i:s'),
            ]);
        }
    }
}
