<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roomTypes = [
            'Standard Room',
            'Deluxe Room', 
            'Executive Suite',
            'Presidential Suite',
            'Family Room',
            'Ocean View Room',
            'Garden View Room',
            'Pool View Room'
        ];

        $rooms = [];
        
        // Generate rooms for each hotel (5 rooms per hotel = 50 total)
        for ($hotelId = 1; $hotelId <= 10; $hotelId++) {
            foreach ($roomTypes as $index => $roomType) {
                $basePrice = $this->getBasePrice($roomType);
                $quantity = rand(3, 8); // Random quantity between 3-8 rooms
                
                $rooms[] = [
                    'hotel_id' => $hotelId,
                    'room_type' => $roomType,
                    'description' => $this->getRoomDescription($roomType),
                    'price_per_night' => $basePrice + rand(-500, 1000), // Add variation
                    'quantity' => $quantity,
                    'available_quantity' => $quantity, // Initially all rooms available
                    'max_guests' => $this->getMaxGuests($roomType),
                    'max_adults' => $this->getMaxAdults($roomType),
                    'max_children' => $this->getMaxChildren($roomType),
                    'amenities' => json_encode($this->getRoomAmenities($roomType)),
                    'images' => json_encode([$roomType . '_1.jpg', $roomType . '_2.jpg', $roomType . '_3.jpg']),
                    'size' => $this->getRoomSize($roomType),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('rooms')->insert($rooms);
    }

    private function getBasePrice($roomType)
    {
        return match($roomType) {
            'Standard Room' => 2500,
            'Deluxe Room' => 3500,
            'Executive Suite' => 5500,
            'Presidential Suite' => 8500,
            'Family Room' => 4000,
            'Ocean View Room' => 4500,
            'Garden View Room' => 3000,
            'Pool View Room' => 3800,
            default => 3000
        };
    }

    private function getRoomDescription($roomType)
    {
        return match($roomType) {
            'Standard Room' => 'Comfortable room with modern amenities and city view.',
            'Deluxe Room' => 'Spacious room with premium amenities and elegant decor.',
            'Executive Suite' => 'Luxurious suite with separate living area and premium services.',
            'Presidential Suite' => 'Ultimate luxury with panoramic views and butler service.',
            'Family Room' => 'Perfect for families with connecting rooms and child-friendly amenities.',
            'Ocean View Room' => 'Breathtaking ocean views with private balcony.',
            'Garden View Room' => 'Peaceful garden views with natural lighting.',
            'Pool View Room' => 'Direct pool access with tropical landscaping views.',
            default => 'Comfortable accommodation with modern amenities.'
        };
    }

    private function getMaxGuests($roomType)
    {
        return match($roomType) {
            'Standard Room' => 2,
            'Deluxe Room' => 3,
            'Executive Suite' => 4,
            'Presidential Suite' => 6,
            'Family Room' => 6,
            'Ocean View Room' => 3,
            'Garden View Room' => 2,
            'Pool View Room' => 3,
            default => 2
        };
    }

    private function getMaxAdults($roomType)
    {
        return match($roomType) {
            'Standard Room' => 2,
            'Deluxe Room' => 2,
            'Executive Suite' => 3,
            'Presidential Suite' => 4,
            'Family Room' => 4,
            'Ocean View Room' => 2,
            'Garden View Room' => 2,
            'Pool View Room' => 2,
            default => 2
        };
    }

    private function getMaxChildren($roomType)
    {
        return match($roomType) {
            'Standard Room' => 1,
            'Deluxe Room' => 2,
            'Executive Suite' => 2,
            'Presidential Suite' => 3,
            'Family Room' => 3,
            'Ocean View Room' => 2,
            'Garden View Room' => 1,
            'Pool View Room' => 2,
            default => 1
        };
    }

    private function getRoomAmenities($roomType)
    {
        $baseAmenities = ['WiFi', 'Air Conditioning', 'TV', 'Mini Bar', 'Safe'];
        
        return match($roomType) {
            'Standard Room' => array_merge($baseAmenities, ['Work Desk']),
            'Deluxe Room' => array_merge($baseAmenities, ['Balcony', 'Coffee Machine']),
            'Executive Suite' => array_merge($baseAmenities, ['Living Room', 'Kitchenette', 'Balcony', 'Coffee Machine']),
            'Presidential Suite' => array_merge($baseAmenities, ['Living Room', 'Kitchen', 'Balcony', 'Coffee Machine', 'Butler Service']),
            'Family Room' => array_merge($baseAmenities, ['Connecting Rooms', 'Child Safety Features']),
            'Ocean View Room' => array_merge($baseAmenities, ['Ocean View', 'Balcony', 'Coffee Machine']),
            'Garden View Room' => array_merge($baseAmenities, ['Garden View', 'Balcony']),
            'Pool View Room' => array_merge($baseAmenities, ['Pool View', 'Pool Access', 'Balcony']),
            default => $baseAmenities
        };
    }

    private function getRoomSize($roomType)
    {
        return match($roomType) {
            'Standard Room' => '25 sqm',
            'Deluxe Room' => '35 sqm',
            'Executive Suite' => '65 sqm',
            'Presidential Suite' => '120 sqm',
            'Family Room' => '45 sqm',
            'Ocean View Room' => '30 sqm',
            'Garden View Room' => '28 sqm',
            'Pool View Room' => '32 sqm',
            default => '25 sqm'
        };
    }
}
