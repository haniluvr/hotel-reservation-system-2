<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use App\Models\Hotel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Reservation $reservation;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $hotel = Hotel::factory()->create();
        $room = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'price_per_night' => 2500.00,
        ]);
        
        $this->reservation = Reservation::factory()->create([
            'user_id' => $this->user->id,
            'room_id' => $room->id,
            'status' => 'pending',
            'total_amount' => 5000.00,
        ]);
    }

    public function test_user_can_access_payment_checkout(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('payments.checkout', $this->reservation->id));

        $response->assertStatus(200);
        $response->assertViewIs('payments.checkout');
        $response->assertViewHas('reservation');
    }

    public function test_user_cannot_access_checkout_for_other_users_reservation(): void
    {
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);

        $response = $this->get(route('payments.checkout', $this->reservation->id));

        $response->assertStatus(403);
    }

    public function test_user_cannot_access_checkout_for_paid_reservation(): void
    {
        $this->actingAs($this->user);
        
        Payment::factory()->create([
            'reservation_id' => $this->reservation->id,
            'status' => 'paid',
        ]);
        $this->reservation->update(['status' => 'confirmed']);

        $response = $this->get(route('payments.checkout', $this->reservation->id));

        $response->assertRedirect(route('bookings.show', $this->reservation->id));
    }

    public function test_user_can_process_payment(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('payments.process', $this->reservation->id), [
            'payment_method' => 'credit_card',
        ]);

        // Should redirect (either to Xendit or booking page)
        $response->assertRedirect();
        
        // Payment record should be created
        $this->assertDatabaseHas('payments', [
            'reservation_id' => $this->reservation->id,
            'payment_method' => 'credit_card',
        ]);
    }

    public function test_payment_requires_valid_payment_method(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('payments.process', $this->reservation->id), [
            'payment_method' => 'invalid_method',
        ]);

        $response->assertSessionHasErrors('payment_method');
    }
}

