<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Reservation extends Model
{
    protected $fillable = [
        'user_id',
        'room_id',
        'reservation_number',
        'check_in_date',
        'check_out_date',
        'adults',
        'children',
        'total_amount',
        'discount_amount',
        'promo_code',
        'status',
        'special_requests',
        'guest_details',
        'confirmed_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'guest_details' => 'array',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Get the user that owns the reservation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the room that belongs to the reservation.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get the payment for the reservation.
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Scope a query to only include pending reservations.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include confirmed reservations.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope a query to only include cancelled reservations.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope a query to only include completed reservations.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('check_in_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to filter by user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get the reservation's duration in days.
     */
    protected function duration(): Attribute
    {
        return Attribute::make(
            get: fn () => Carbon::parse($this->check_in_date)->diffInDays(Carbon::parse($this->check_out_date)),
        );
    }

    /**
     * Get the reservation's total guests.
     */
    protected function totalGuests(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->adults + $this->children,
        );
    }

    /**
     * Get the reservation's formatted total amount.
     */
    protected function formattedTotalAmount(): Attribute
    {
        return Attribute::make(
            get: fn () => '₱' . number_format($this->total_amount, 2),
        );
    }

    /**
     * Get the reservation's status badge color.
     */
    protected function statusColor(): Attribute
    {
        return Attribute::make(
            get: fn () => match($this->status) {
                'pending' => 'warning',
                'confirmed' => 'success',
                'cancelled' => 'danger',
                'completed' => 'info',
                'no_show' => 'secondary',
                default => 'secondary',
            },
        );
    }

    /**
     * Check if reservation can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']) && 
               Carbon::parse($this->check_in_date)->isFuture();
    }

    /**
     * Check if reservation can be modified.
     */
    public function canBeModified(): bool
    {
        return $this->status === 'pending' && 
               Carbon::parse($this->check_in_date)->isFuture();
    }

    /**
     * Check if reservation is active.
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    /**
     * Get the reservation's check-in status.
     */
    public function getCheckInStatus(): string
    {
        $checkInDate = Carbon::parse($this->check_in_date);
        $now = Carbon::now();

        if ($checkInDate->isFuture()) {
            return 'upcoming';
        } elseif ($checkInDate->isToday()) {
            return 'today';
        } elseif ($checkInDate->isPast() && Carbon::parse($this->check_out_date)->isFuture()) {
            return 'checked_in';
        } else {
            return 'completed';
        }
    }

    /**
     * Calculate the total nights.
     */
    public function getTotalNights(): int
    {
        return Carbon::parse($this->check_in_date)->diffInDays(Carbon::parse($this->check_out_date));
    }

    /**
     * Get the reservation's display status.
     */
    public function getDisplayStatus(): string
    {
        return match($this->status) {
            'pending' => 'Pending Confirmation',
            'confirmed' => 'Confirmed',
            'cancelled' => 'Cancelled',
            'completed' => 'Completed',
            'no_show' => 'No Show',
            default => 'Unknown',
        };
    }
}
