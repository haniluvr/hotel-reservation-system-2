<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\Room;
use App\Models\Reservation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ReviewSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();
        $rooms = Room::all();
        
        if ($rooms->isEmpty()) {
            $this->command->warn('Rooms not found. Please ensure rooms exist.');
            return;
        }
        
        foreach ($rooms as $room) {
            // Get completed reservations for this room that don't have reviews yet
            $completedReservations = Reservation::where('room_id', $room->id)
                ->where('status', 'completed')
                ->whereDoesntHave('review')
                ->take(3)
                ->get();
            
            // Only create reviews if there are completed reservations
            if ($completedReservations->isEmpty()) {
                continue;
            }
            
            // Generate reviews for available reservations (up to 3 per room)
            foreach ($completedReservations as $reservation) {
                $user = $reservation->user;
                $checkOutDate = is_string($reservation->check_out_date) 
                    ? Carbon::parse($reservation->check_out_date) 
                    : $reservation->check_out_date;
                $reviewDate = $checkOutDate->copy()->addDays(rand(1, 30));
                
                // Generate realistic rating distribution (more positive reviews)
                $rating = $this->generateRealisticRating();
                
                // Generate comment based on rating
                $comment = $this->generateComment($rating, $faker);
                
                // Status: mostly approved, some pending
                $status = rand(1, 100) <= 85 ? 'approved' : 'pending';
                
                Review::create([
                    'user_id' => $user->id,
                    'reservation_id' => $reservation->id,
                    'hotel_id' => null, // Hotels table is dropped
                    'room_id' => $room->id,
                    'rating' => $rating,
                    'comment' => $comment,
                    'status' => $status,
                    'created_at' => $reviewDate->format('Y-m-d H:i:s'),
                    'updated_at' => $reviewDate->format('Y-m-d H:i:s'),
                ]);
            }
        }
    }
    
    /**
     * Generate realistic rating distribution
     * More positive reviews (4-5 stars) than negative (1-2 stars)
     */
    private function generateRealisticRating(): int
    {
        $rand = rand(1, 100);
        
        if ($rand <= 50) {
            return 5; // 50% chance of 5 stars
        } elseif ($rand <= 75) {
            return 4; // 25% chance of 4 stars
        } elseif ($rand <= 88) {
            return 3; // 13% chance of 3 stars
        } elseif ($rand <= 95) {
            return 2; // 7% chance of 2 stars
        } else {
            return 1; // 5% chance of 1 star
        }
    }
    
    /**
     * Generate comment based on rating
     */
    private function generateComment(int $rating, $faker): string
    {
        $positiveComments = [
            'Excellent stay! The room was clean and comfortable. Staff was very helpful.',
            'Amazing experience! Would definitely come back. The amenities were great.',
            'Perfect location and wonderful service. Highly recommended!',
            'Beautiful room with great views. Everything was perfect!',
            'Outstanding service and facilities. One of the best hotels I\'ve stayed at.',
            'Great value for money. Clean, comfortable, and well-maintained.',
            'Loved our stay! The room exceeded our expectations.',
            'Fantastic hotel with excellent amenities. Will return soon!',
        ];
        
        $neutralComments = [
            'Decent stay. Room was okay, nothing special.',
            'Average experience. Met expectations but nothing extraordinary.',
            'Room was clean and functional. Service was adequate.',
            'Good location but room could use some updates.',
            'Satisfactory stay. Would consider staying again.',
        ];
        
        $negativeComments = [
            'Room was not as clean as expected. Some maintenance issues.',
            'Service was slow and unresponsive. Disappointed with the experience.',
            'Room had some problems. Not worth the price.',
            'Had issues with room amenities. Staff was not very helpful.',
            'Below expectations. Room quality needs improvement.',
        ];
        
        if ($rating >= 4) {
            return $faker->randomElement($positiveComments);
        } elseif ($rating === 3) {
            return $faker->randomElement($neutralComments);
        } else {
            return $faker->randomElement($negativeComments);
        }
    }
}
