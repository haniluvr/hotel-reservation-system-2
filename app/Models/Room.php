<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Room extends Model
{
    protected $fillable = [
        'hotel_id',
        'room_type',
        'description',
        'price_per_night',
        'quantity',
        'available_quantity',
        'max_guests',
        'max_adults',
        'max_children',
        'amenities',
        'images',
        'size',
        'is_active',
    ];

    protected $casts = [
        'amenities' => 'array',
        'images' => 'array',
        'price_per_night' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the hotel that owns the room.
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get the reservations for the room.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Scope a query to only include active rooms.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include available rooms.
     */
    public function scopeAvailable($query)
    {
        return $query->where('available_quantity', '>', 0);
    }

    /**
     * Scope a query to filter by price range.
     */
    public function scopePriceRange($query, float $minPrice, float $maxPrice)
    {
        return $query->whereBetween('price_per_night', [$minPrice, $maxPrice]);
    }

    /**
     * Scope a query to filter by room type.
     */
    public function scopeByType($query, string $roomType)
    {
        return $query->where('room_type', 'like', '%' . $roomType . '%');
    }

    /**
     * Scope a query to filter by guest capacity.
     */
    public function scopeForGuests($query, int $guests)
    {
        return $query->where('max_guests', '>=', $guests);
    }

    /**
     * Check if room is available for booking.
     */
    public function isAvailable(): bool
    {
        return $this->available_quantity > 0 && $this->is_active;
    }

    /**
     * Get the room's occupancy percentage.
     */
    public function getOccupancyPercentage(): float
    {
        if ($this->quantity == 0) {
            return 0;
        }
        
        return (($this->quantity - $this->available_quantity) / $this->quantity) * 100;
    }

    /**
     * Reserve room inventory.
     */
    public function reserveInventory(int $quantity = 1): bool
    {
        if ($this->available_quantity < $quantity) {
            return false;
        }

        $this->available_quantity -= $quantity;
        return $this->save();
    }

    /**
     * Release room inventory.
     */
    public function releaseInventory(int $quantity = 1): bool
    {
        $this->available_quantity = min($this->quantity, $this->available_quantity + $quantity);
        return $this->save();
    }

    /**
     * Get the room's formatted price.
     */
    protected function formattedPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => '₱' . number_format($this->price_per_night, 2),
        );
    }

    /**
     * Get the room's availability status.
     */
    protected function availabilityStatus(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->isAvailable() ? 'Available' : 'Unavailable',
        );
    }
}
