<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use App\Models\Hotel;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Hotel $hotel;
    private Room $room;
    private ReservationService $reservationService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->hotel = Hotel::factory()->create();
        $this->room = Room::factory()->create([
            'hotel_id' => $this->hotel->id,
            'available_quantity' => 5,
            'price_per_night' => 2500.00,
        ]);
        $this->reservationService = app(ReservationService::class);
    }

    public function test_user_can_create_reservation(): void
    {
        $this->actingAs($this->user);

        $reservationData = [
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'check_in_date' => now()->addDays(7)->format('Y-m-d'),
            'check_out_date' => now()->addDays(9)->format('Y-m-d'),
            'adults' => 2,
            'children' => 0,
            'total_amount' => 5000.00,
        ];

        $result = $this->reservationService->createReservation($reservationData);

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('reservations', [
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'status' => 'pending',
        ]);
    }

    public function test_reservation_decreases_room_availability(): void
    {
        $this->actingAs($this->user);

        $initialQuantity = $this->room->available_quantity;

        $reservationData = [
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'check_in_date' => now()->addDays(7)->format('Y-m-d'),
            'check_out_date' => now()->addDays(9)->format('Y-m-d'),
            'adults' => 2,
            'children' => 0,
            'total_amount' => 5000.00,
        ];

        $this->reservationService->createReservation($reservationData);

        $this->room->refresh();
        $this->assertEquals($initialQuantity - 1, $this->room->available_quantity);
    }

    public function test_cannot_create_reservation_when_room_unavailable(): void
    {
        $this->actingAs($this->user);

        // Set room availability to 0
        $this->room->update(['available_quantity' => 0]);

        $reservationData = [
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'check_in_date' => now()->addDays(7)->format('Y-m-d'),
            'check_out_date' => now()->addDays(9)->format('Y-m-d'),
            'adults' => 2,
            'children' => 0,
            'total_amount' => 5000.00,
        ];

        $result = $this->reservationService->createReservation($reservationData);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('not available', $result['error']);
    }

    public function test_cannot_create_reservation_with_past_check_in_date(): void
    {
        $this->actingAs($this->user);

        $reservationData = [
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'check_in_date' => now()->subDays(1)->format('Y-m-d'),
            'check_out_date' => now()->addDays(1)->format('Y-m-d'),
            'adults' => 2,
            'children' => 0,
            'total_amount' => 5000.00,
        ];

        $result = $this->reservationService->createReservation($reservationData);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('past', $result['error']);
    }

    public function test_user_can_cancel_reservation(): void
    {
        $this->actingAs($this->user);

        $reservation = Reservation::factory()->create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'status' => 'pending',
            'check_in_date' => now()->addDays(7),
        ]);

        $initialQuantity = $this->room->available_quantity;

        $result = $this->reservationService->cancelReservation($reservation->id, 'Change of plans');

        $this->assertTrue($result['success']);
        $this->room->refresh();
        $this->assertEquals($initialQuantity + 1, $this->room->available_quantity);
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_cancelled_reservation_restores_room_availability(): void
    {
        $this->actingAs($this->user);

        $reservation = Reservation::factory()->create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'status' => 'confirmed',
        ]);

        // Decrease availability manually to simulate reservation
        $this->room->decrement('available_quantity');
        $initialQuantity = $this->room->available_quantity;

        $this->reservationService->cancelReservation($reservation->id, 'Cancelled');

        $this->room->refresh();
        $this->assertEquals($initialQuantity + 1, $this->room->available_quantity);
    }
}

