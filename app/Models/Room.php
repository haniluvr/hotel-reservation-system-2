<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Room extends Model
{
    protected $fillable = [
        'hotel_id',
        'room_type',
        'slug',
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

    /**
     * Get the folder name for room images based on slug.
     */
    public function getImageFolderName(): string
    {
        // Map room slugs to folder names
        $slugToFolderMap = [
            'junior-suite-balcony' => 'junior-suite-balcony',
            'junior-suite-lagoon-access' => 'junior-suite-lagoon',
            '1-bedroom-suite-balcony' => '1-bedroom-suite-balcony',
            '1-bedroom-suite-lagoon-access' => '1-bedroom-suite-lagoon',
            'signature-suite-pool-access' => 'signature-suite-pool',
            '2-bedroom-suite-lagoon-access' => '2-bedroom-suite-lagoon',
            '1-bedroom-villa-garden-view-with-private-pool' => '1-bedroom-villa-garden',
            '1-bedroom-villa-resort-view-with-private-pool' => '1-bedroom-villa-resort',
            '1-bedroom-villa-ocean-view-with-private-pool' => '1-bedroom-villa-ocean',
            '1-bedroom-villa-ocean-view-with-balcony-and-private-pool' => '1-bedroom-villa-ocean-balcony',
            '2-bedroom-villa-ocean-view-with-balcony-and-private-pool' => '2-bedroom-villa-ocean-balcony',
            '3-bedroom-villa-ocean-view-with-private-pool' => '3-bedroom-villa-ocean',
        ];

        return $slugToFolderMap[$this->slug] ?? $this->slug;
    }

    /**
     * Get room images from the storage folder.
     */
    public function getRoomImages(): array
    {
        $folderName = $this->getImageFolderName();
        $folderPath = "accomodations/{$folderName}";
        
        if (!Storage::disk('public')->exists($folderPath)) {
            return [];
        }

        $files = Storage::disk('public')->files($folderPath);
        $images = [];
        
        foreach ($files as $file) {
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
                // Use Storage::url() which handles the path correctly
                $url = Storage::disk('public')->url($file);
                // Ensure forward slashes on all platforms
                $images[] = str_replace('\\', '/', $url);
            }
        }
        
        // Sort images naturally (e.g., image-1.jpg, image-2.jpg, etc.)
        natsort($images);
        
        return array_values($images);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($room) {
            if (empty($room->slug)) {
                $room->slug = Str::slug($room->room_type);
                
                // Ensure uniqueness
                $originalSlug = $room->slug;
                $counter = 1;
                while (static::where('slug', $room->slug)->exists()) {
                    $room->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }
        });

        static::updating(function ($room) {
            if ($room->isDirty('room_type') && empty($room->slug)) {
                $room->slug = Str::slug($room->room_type);
                
                // Ensure uniqueness
                $originalSlug = $room->slug;
                $counter = 1;
                while (static::where('slug', $room->slug)->where('id', '!=', $room->id)->exists()) {
                    $room->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }
        });
    }
}
