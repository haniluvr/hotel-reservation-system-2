<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use App\Models\PromoCode;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ReservationSeeder extends Seeder
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
        $promoCodes = PromoCode::all();
        
        if ($users->isEmpty() || $rooms->isEmpty()) {
            $this->command->warn('Users or Rooms not found. Please run UserSeeder and ensure rooms exist.');
            return;
        }
        
        $pastYear = Carbon::now()->subYear();
        $nextYearMarch = Carbon::now()->addYear()->month(3)->endOfMonth();
        
        // Generate 200-300 reservations
        $reservationCount = rand(200, 300);
        
        for ($i = 0; $i < $reservationCount; $i++) {
            $user = $users->random();
            $room = $rooms->random();
            
            // Generate check-in date from past year to next year March
            $checkInDate = Carbon::createFromTimestamp(
                rand($pastYear->timestamp, $nextYearMarch->timestamp)
            );
            
            // Generate check-out date (1-14 nights stay)
            $nights = rand(1, 14);
            $checkOutDate = $checkInDate->copy()->addDays($nights);
            
            // Skip if check-out is beyond next year March
            if ($checkOutDate->gt($nextYearMarch)) {
                continue;
            }
            
            // Calculate base total amount
            $baseAmount = $room->price_per_night * $nights;
            
            // Randomly apply promo code (30% chance)
            $promoCode = null;
            $discountAmount = 0;
            $promoCodeModel = null;
            
            if (rand(1, 100) <= 30 && $promoCodes->isNotEmpty()) {
                $promoCodeModel = $promoCodes->random();
                $promoCode = $promoCodeModel->code;
                
                // Calculate discount
                if ($promoCodeModel->type === 'percentage') {
                    $discountAmount = ($baseAmount * $promoCodeModel->value) / 100;
                } else {
                    $discountAmount = min($promoCodeModel->value, $baseAmount * 0.5); // Cap at 50% of total
                }
            }
            
            $totalAmount = max(0, $baseAmount - $discountAmount);
            
            // Determine status based on dates
            $now = Carbon::now();
            $status = 'pending';
            $confirmedAt = null;
            $cancelledAt = null;
            $cancellationReason = null;
            
            if ($checkOutDate->lt($now)) {
                // Past reservation - completed or cancelled
                if (rand(1, 100) <= 85) {
                    $status = 'completed';
                } else {
                    $status = 'cancelled';
                    $cancelledAt = $checkInDate->copy()->subDays(rand(1, 7));
                    $cancellationReason = $faker->randomElement([
                        'Change of plans',
                        'Found alternative accommodation',
                        'Emergency situation',
                        'Travel restrictions',
                        'Personal reasons',
                    ]);
                }
            } elseif ($checkInDate->lt($now) && $checkOutDate->gt($now)) {
                // Current stay - confirmed
                $status = 'confirmed';
                $confirmedAt = $checkInDate->copy()->subDays(rand(1, 30));
            } elseif ($checkInDate->isFuture()) {
                // Future reservation
                if (rand(1, 100) <= 70) {
                    $status = 'confirmed';
                    $confirmedAt = $checkInDate->copy()->subDays(rand(1, 60));
                } else {
                    $status = 'pending';
                }
            }
            
            // Generate reservation number
            $reservationNumber = 'BEL' . $checkInDate->format('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Ensure unique reservation number
            while (Reservation::where('reservation_number', $reservationNumber)->exists()) {
                $reservationNumber = 'BEL' . $checkInDate->format('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            }
            
            // Generate adults and children
            $maxAdults = $room->max_adults ?? 2;
            $maxChildren = $room->max_children ?? 0;
            $adults = rand(1, min($maxAdults, 4));
            $children = $maxChildren > 0 ? rand(0, min($maxChildren, 2)) : 0;
            
            // Special requests (30% chance)
            $specialRequests = null;
            if (rand(1, 100) <= 30) {
                $specialRequests = $faker->randomElement([
                    'Late check-in requested',
                    'Early check-in if possible',
                    'Non-smoking room preferred',
                    'Extra towels needed',
                    'Quiet room preferred',
                    'High floor preferred',
                    'Accessible room if available',
                    'Anniversary celebration',
                    'Birthday celebration',
                ]);
            }
            
            // Guest details (20% chance)
            $guestDetails = null;
            if (rand(1, 100) <= 20) {
                $guestDetails = [
                    'additional_guests' => rand(0, 2),
                    'special_needs' => $faker->optional()->randomElement(['Wheelchair access', 'Dietary restrictions']),
                ];
            }
            
            $reservation = Reservation::create([
                'user_id' => $user->id,
                'room_id' => $room->id,
                'reservation_number' => $reservationNumber,
                'check_in_date' => $checkInDate->format('Y-m-d'),
                'check_out_date' => $checkOutDate->format('Y-m-d'),
                'adults' => $adults,
                'children' => $children,
                'total_amount' => round($totalAmount, 2),
                'discount_amount' => round($discountAmount, 2),
                'promo_code' => $promoCode,
                'status' => $status,
                'special_requests' => $specialRequests,
                'guest_details' => $guestDetails,
                'confirmed_at' => $confirmedAt?->format('Y-m-d H:i:s'),
                'cancelled_at' => $cancelledAt?->format('Y-m-d H:i:s'),
                'cancellation_reason' => $cancellationReason,
                'created_at' => $checkInDate->copy()->subDays(rand(1, 90))->format('Y-m-d H:i:s'),
                'updated_at' => $checkInDate->copy()->subDays(rand(0, 30))->format('Y-m-d H:i:s'),
            ]);
        }
    }
}
