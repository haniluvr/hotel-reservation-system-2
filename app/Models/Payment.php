<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Payment extends Model
{
    protected $fillable = [
        'reservation_id',
        'xendit_invoice_id',
        'payment_method',
        'amount',
        'currency',
        'status',
        'payment_url',
        'payment_details',
        'paid_at',
        'expires_at',
        'failure_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_details' => 'array',
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the reservation that owns the payment.
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Scope a query to only include pending payments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include paid payments.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope a query to only include failed payments.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to only include expired payments.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    /**
     * Get the payment's formatted amount.
     */
    protected function formattedAmount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->currency . ' ' . number_format($this->amount, 2),
        );
    }

    /**
     * Check if payment is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if payment is successful.
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if payment can be refunded.
     */
    public function canBeRefunded(): bool
    {
        return $this->status === 'paid' && $this->paid_at && $this->paid_at->isPast();
    }

    /**
     * Get the payment's display status.
     */
    public function getDisplayStatus(): string
    {
        return match($this->status) {
            'pending' => 'Pending Payment',
            'paid' => 'Paid',
            'failed' => 'Failed',
            'expired' => 'Expired',
            'cancelled' => 'Cancelled',
            default => 'Unknown',
        };
    }

    /**
     * Get the payment's status color.
     */
    protected function statusColor(): Attribute
    {
        return Attribute::make(
            get: fn () => match($this->status) {
                'pending' => 'warning',
                'paid' => 'success',
                'failed' => 'danger',
                'expired' => 'secondary',
                'cancelled' => 'secondary',
                default => 'secondary',
            },
        );
    }
}
