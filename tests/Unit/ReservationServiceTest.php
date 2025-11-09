<?php

namespace Tests\Unit;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use App\Models\Hotel;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationServiceTest extends TestCase
{
    use RefreshDatabase;

    private ReservationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ReservationService::class);
    }

    public function test_check_availability_returns_true_when_room_available(): void
    {
        $hotel = Hotel::factory()->create();
        $room = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'available_quantity' => 5,
        ]);

        $checkIn = now()->addDays(7)->format('Y-m-d');
        $checkOut = now()->addDays(9)->format('Y-m-d');

        $result = $this->service->checkAvailability($room->id, $checkIn, $checkOut);

        $this->assertTrue($result);
    }

    public function test_check_availability_returns_false_when_room_unavailable(): void
    {
        $hotel = Hotel::factory()->create();
        $room = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'available_quantity' => 0,
        ]);

        $checkIn = now()->addDays(7)->format('Y-m-d');
        $checkOut = now()->addDays(9)->format('Y-m-d');

        $result = $this->service->checkAvailability($room->id, $checkIn, $checkOut);

        $this->assertFalse($result);
    }

    public function test_get_reservation_stats_returns_correct_data(): void
    {
        $user = User::factory()->create();
        $hotel = Hotel::factory()->create();
        $room = Room::factory()->create(['hotel_id' => $hotel->id]);

        Reservation::factory()->create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'status' => 'pending',
            'total_amount' => 1000.00,
        ]);

        Reservation::factory()->create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'status' => 'confirmed',
            'total_amount' => 2000.00,
        ]);

        $stats = $this->service->getReservationStats();

        $this->assertEquals(2, $stats['total_reservations']);
        $this->assertEquals(1, $stats['pending']->count);
        $this->assertEquals(1, $stats['confirmed']->count);
        $this->assertEquals(3000.00, $stats['total_revenue']);
    }
}

