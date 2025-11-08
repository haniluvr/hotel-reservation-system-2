<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class PromoCode extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'minimum_amount',
        'usage_limit',
        'used_count',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'minimum_amount' => 'decimal:2',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to only include active promo codes.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('valid_from', '<=', now())
                    ->where('valid_until', '>=', now());
    }

    /**
     * Scope a query to only include available promo codes.
     */
    public function scopeAvailable($query)
    {
        return $query->active()
                    ->where(function ($q) {
                        $q->whereNull('usage_limit')
                          ->orWhereRaw('used_count < usage_limit');
                    });
    }

    /**
     * Scope a query to filter by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Check if promo code is valid.
     */
    public function isValid(): bool
    {
        return $this->is_active &&
               $this->valid_from <= now() &&
               $this->valid_until >= now() &&
               ($this->usage_limit === null || $this->used_count < $this->usage_limit);
    }

    /**
     * Check if promo code can be used for the given amount.
     */
    public function canBeUsedFor(float $amount): bool
    {
        return $this->isValid() && $amount >= $this->minimum_amount;
    }

    /**
     * Calculate discount amount for the given total.
     */
    public function calculateDiscount(float $total): float
    {
        if (!$this->canBeUsedFor($total)) {
            return 0;
        }

        if ($this->type === 'percentage') {
            return ($total * $this->value) / 100;
        }

        return min($this->value, $total);
    }

    /**
     * Apply promo code to a total amount.
     */
    public function applyTo(float $total): array
    {
        if (!$this->canBeUsedFor($total)) {
            return [
                'success' => false,
                'message' => 'Promo code cannot be used for this amount',
                'discount' => 0,
                'final_total' => $total,
            ];
        }

        $discount = $this->calculateDiscount($total);
        $finalTotal = max(0, $total - $discount);

        return [
            'success' => true,
            'discount' => $discount,
            'final_total' => $finalTotal,
            'savings' => $discount,
        ];
    }

    /**
     * Increment usage count.
     */
    public function incrementUsage(): bool
    {
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        $this->increment('used_count');
        return true;
    }

    /**
     * Get the promo code's formatted value.
     */
    protected function formattedValue(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->type === 'percentage' 
                ? $this->value . '%' 
                : '₱' . number_format($this->value, 2),
        );
    }

    /**
     * Get the promo code's validity status.
     */
    protected function validityStatus(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->isValid() ? 'Valid' : 'Invalid',
        );
    }

    /**
     * Get the promo code's usage percentage.
     */
    public function getUsagePercentage(): float
    {
        if (!$this->usage_limit) {
            return 0;
        }

        return ($this->used_count / $this->usage_limit) * 100;
    }

    /**
     * Get the promo code's remaining uses.
     */
    public function getRemainingUses(): ?int
    {
        if (!$this->usage_limit) {
            return null;
        }

        return max(0, $this->usage_limit - $this->used_count);
    }

    /**
     * Check if promo code is expiring soon.
     */
    public function isExpiringSoon(int $days = 7): bool
    {
        return $this->valid_until->diffInDays(now()) <= $days;
    }
}
