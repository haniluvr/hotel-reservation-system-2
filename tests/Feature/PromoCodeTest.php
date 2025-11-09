<?php

namespace Tests\Feature;

use App\Models\PromoCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromoCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_validate_valid_promo_code(): void
    {
        $promoCode = PromoCode::factory()->create([
            'code' => 'SUMMER2024',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'min_amount' => 1000.00,
            'max_uses' => 100,
            'used_count' => 0,
            'starts_at' => now()->subDays(1),
            'expires_at' => now()->addDays(30),
        ]);

        $response = $this->get(route('api.promo-codes.validate', [
            'code' => 'SUMMER2024',
            'amount' => 5000.00,
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'valid' => true,
            'code' => 'SUMMER2024',
        ]);
    }

    public function test_invalid_promo_code_returns_false(): void
    {
        $response = $this->get(route('api.promo-codes.validate', [
            'code' => 'INVALID',
            'amount' => 5000.00,
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'valid' => false,
        ]);
    }

    public function test_expired_promo_code_is_invalid(): void
    {
        $promoCode = PromoCode::factory()->create([
            'code' => 'EXPIRED',
            'expires_at' => now()->subDays(1),
        ]);

        $response = $this->get(route('api.promo-codes.validate', [
            'code' => 'EXPIRED',
            'amount' => 5000.00,
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'valid' => false,
        ]);
    }

    public function test_promo_code_below_minimum_amount_is_invalid(): void
    {
        $promoCode = PromoCode::factory()->create([
            'code' => 'MIN1000',
            'min_amount' => 1000.00,
        ]);

        $response = $this->get(route('api.promo-codes.validate', [
            'code' => 'MIN1000',
            'amount' => 500.00, // Below minimum
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'valid' => false,
        ]);
    }

    public function test_promo_code_exceeds_max_uses_is_invalid(): void
    {
        $promoCode = PromoCode::factory()->create([
            'code' => 'MAXUSED',
            'max_uses' => 10,
            'used_count' => 10,
        ]);

        $response = $this->get(route('api.promo-codes.validate', [
            'code' => 'MAXUSED',
            'amount' => 5000.00,
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'valid' => false,
        ]);
    }

    public function test_percentage_discount_calculation(): void
    {
        $promoCode = PromoCode::factory()->create([
            'code' => 'PERCENT10',
            'discount_type' => 'percentage',
            'discount_value' => 10,
        ]);

        $response = $this->get(route('api.promo-codes.validate', [
            'code' => 'PERCENT10',
            'amount' => 1000.00,
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'valid' => true,
            'discount_amount' => 100.00,
            'final_amount' => 900.00,
        ]);
    }

    public function test_fixed_discount_calculation(): void
    {
        $promoCode = PromoCode::factory()->create([
            'code' => 'FIXED100',
            'discount_type' => 'fixed',
            'discount_value' => 100.00,
        ]);

        $response = $this->get(route('api.promo-codes.validate', [
            'code' => 'FIXED100',
            'amount' => 1000.00,
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'valid' => true,
            'discount_amount' => 100.00,
            'final_amount' => 900.00,
        ]);
    }
}

