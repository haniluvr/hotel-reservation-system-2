<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Hotel extends Model
{
    protected $fillable = [
        'name',
        'description',
        'address',
        'city',
        'country',
        'latitude',
        'longitude',
        'star_rating',
        'amenities',
        'images',
        'hotelbeds_code',
        'is_active',
    ];

    protected $casts = [
        'amenities' => 'array',
        'images' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
    ];

    /**
     * Get the rooms for the hotel.
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    /**
     * Get the reservations for the hotel through rooms.
     */
    public function reservations(): HasMany
    {
        return $this->hasManyThrough(Reservation::class, Room::class);
    }

    /**
     * Get the reviews for the hotel.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the average rating for the hotel.
     */
    public function getAverageRating(): float
    {
        return $this->reviews()->approved()->avg('rating') ?? 0;
    }

    /**
     * Get the total number of reviews for the hotel.
     */
    public function getTotalReviews(): int
    {
        return $this->reviews()->approved()->count();
    }

    /**
     * Scope a query to only include active hotels.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by city.
     */
    public function scopeInCity($query, string $city)
    {
        return $query->where('city', 'like', '%' . $city . '%');
    }

    /**
     * Scope a query to filter by star rating.
     */
    public function scopeWithStars($query, int $stars)
    {
        return $query->where('star_rating', '>=', $stars);
    }

    /**
     * Get the hotel's full address.
     */
    protected function fullAddress(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->address}, {$this->city}, {$this->country}",
        );
    }

    /**
     * Get the hotel's average room price.
     */
    public function getAverageRoomPrice(): float
    {
        return $this->rooms()->avg('price_per_night') ?? 0;
    }

    /**
     * Get the hotel's total available rooms.
     */
    public function getTotalAvailableRooms(): int
    {
        return $this->rooms()->sum('available_quantity');
    }

    /**
     * Get the hotel's occupancy rate.
     */
    public function getOccupancyRate(): float
    {
        $totalRooms = $this->rooms()->sum('quantity');
        $availableRooms = $this->getTotalAvailableRooms();
        
        if ($totalRooms == 0) {
            return 0;
        }
        
        return (($totalRooms - $availableRooms) / $totalRooms) * 100;
    }
}
