<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Hotel;
use App\Models\Room;
use App\DataStructures\HashTable;
use App\DataStructures\BST;

/**
 * HotelBedsService - HotelBeds API Integration Service
 * 
 * Handles hotel and room data synchronization with HotelBeds API.
 * Implements caching and data structures for efficient lookups.
 */
class HotelBedsService
{
    private string $apiKey;
    private string $secret;
    private string $baseUrl;
    private HashTable $hotelCache;
    private BST $priceIndex;
    private int $cacheTimeout;

    public function __construct()
    {
        $this->apiKey = config('services.hotelbeds.api_key') ?? 'demo_key';
        $this->secret = config('services.hotelbeds.secret') ?? 'demo_secret';
        $this->baseUrl = config('services.hotelbeds.base_url', 'https://api.test.hotelbeds.com');
        $this->hotelCache = new HashTable();
        $this->priceIndex = new BST();
        $this->cacheTimeout = 3600; // 1 hour
    }

    /**
     * Search for hotels by location and criteria
     */
    public function searchHotels(array $criteria): array
    {
        $cacheKey = 'hotelbeds_search_' . md5(serialize($criteria));
        
        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($criteria) {
            try {
                $response = $this->makeApiRequest('/hotel-api/1.0/hotels', [
                    'destination' => $criteria['destination'] ?? null,
                    'checkIn' => $criteria['check_in'] ?? null,
                    'checkOut' => $criteria['check_out'] ?? null,
                    'rooms' => $criteria['rooms'] ?? 1,
                    'adults' => $criteria['adults'] ?? 2,
                    'children' => $criteria['children'] ?? 0,
                    'minRate' => $criteria['min_rate'] ?? null,
                    'maxRate' => $criteria['max_rate'] ?? null,
                    'stars' => $criteria['stars'] ?? null,
                    'accommodations' => $criteria['accommodations'] ?? null,
                ]);

                return $this->processHotelSearchResponse($response);
            } catch (\Exception $e) {
                Log::error('HotelBeds API search error: ' . $e->getMessage());
                return $this->getFallbackHotels($criteria);
            }
        });
    }

    /**
     * Get hotel details by hotel code
     */
    public function getHotelDetails(string $hotelCode): ?array
    {
        $cacheKey = "hotelbeds_hotel_{$hotelCode}";
        
        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($hotelCode) {
            try {
                $response = $this->makeApiRequest("/hotel-api/1.0/hotels/{$hotelCode}");
                return $this->processHotelDetailsResponse($response);
            } catch (\Exception $e) {
                Log::error("HotelBeds API hotel details error for {$hotelCode}: " . $e->getMessage());
                return $this->getLocalHotelDetails($hotelCode);
            }
        });
    }

    /**
     * Search for room availability
     */
    public function searchRooms(array $criteria): array
    {
        $cacheKey = 'hotelbeds_rooms_' . md5(serialize($criteria));
        
        return Cache::remember($cacheKey, 1800, function () use ($criteria) { // 30 minutes cache
            try {
                $response = $this->makeApiRequest('/hotel-api/1.0/checkrates', [
                    'hotels' => $criteria['hotel_codes'] ?? [],
                    'checkIn' => $criteria['check_in'] ?? null,
                    'checkOut' => $criteria['check_out'] ?? null,
                    'rooms' => $criteria['rooms'] ?? 1,
                    'adults' => $criteria['adults'] ?? 2,
                    'children' => $criteria['children'] ?? 0,
                ]);

                return $this->processRoomSearchResponse($response);
            } catch (\Exception $e) {
                Log::error('HotelBeds API room search error: ' . $e->getMessage());
                return $this->getFallbackRooms($criteria);
            }
        });
    }

    /**
     * Synchronize hotel data with local database
     */
    public function syncHotels(array $hotelCodes = []): array
    {
        $syncedHotels = [];
        $errors = [];

        try {
            if (empty($hotelCodes)) {
                // Sync all hotels from local database
                $hotelCodes = Hotel::whereNotNull('hotelbeds_code')
                    ->pluck('hotelbeds_code')
                    ->toArray();
            }

            foreach ($hotelCodes as $hotelCode) {
                try {
                    $hotelData = $this->getHotelDetails($hotelCode);
                    if ($hotelData) {
                        $syncedHotels[] = $this->updateLocalHotel($hotelCode, $hotelData);
                    }
                } catch (\Exception $e) {
                    $errors[] = "Failed to sync hotel {$hotelCode}: " . $e->getMessage();
                    Log::error("Hotel sync error for {$hotelCode}: " . $e->getMessage());
                }
            }

            // Update cache after sync
            $this->refreshCache();

        } catch (\Exception $e) {
            Log::error('Hotel sync process error: ' . $e->getMessage());
            $errors[] = 'Hotel sync process failed: ' . $e->getMessage();
        }

        return [
            'synced_hotels' => $syncedHotels,
            'errors' => $errors,
            'total_synced' => count($syncedHotels),
        ];
    }

    /**
     * Get hotels by price range using BST
     */
    public function getHotelsByPriceRange(float $minPrice, float $maxPrice): array
    {
        $this->buildPriceIndex();
        return $this->priceIndex->rangeQuery($minPrice, $maxPrice);
    }

    /**
     * Get hotel by ID with O(1) lookup
     */
    public function getHotelById(int $hotelId): ?array
    {
        $cached = $this->hotelCache->get($hotelId);
        if ($cached) {
            return $cached;
        }

        $hotel = Hotel::with('rooms')->find($hotelId);
        if ($hotel) {
            $hotelData = $this->formatHotelData($hotel);
            $this->hotelCache->put($hotelId, $hotelData);
            return $hotelData;
        }

        return null;
    }

    /**
     * Clear all caches
     */
    public function clearCache(): void
    {
        Cache::flush();
        $this->hotelCache->clear();
        $this->priceIndex->clear();
    }

    /**
     * Get API status and health
     */
    public function getApiStatus(): array
    {
        try {
            $response = $this->makeApiRequest('/hotel-api/1.0/status');
            return [
                'status' => 'healthy',
                'response_time' => $response['response_time'] ?? null,
                'api_version' => $response['apiVersion'] ?? null,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    // Private helper methods

    private function makeApiRequest(string $endpoint, array $params = []): array
    {
        $url = $this->baseUrl . $endpoint;
        
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Api-Key' => $this->apiKey,
            'X-Signature' => $this->generateSignature($endpoint),
        ];

        $response = Http::withHeaders($headers)
            ->timeout(30)
            ->get($url, $params);

        if (!$response->successful()) {
            throw new \Exception("API request failed: " . $response->body());
        }

        return $response->json();
    }

    private function generateSignature(string $endpoint): string
    {
        $timestamp = time();
        $signatureString = $this->apiKey . $this->secret . $timestamp;
        return hash('sha256', $signatureString);
    }

    private function processHotelSearchResponse(array $response): array
    {
        $hotels = [];
        
        if (isset($response['hotels'])) {
            foreach ($response['hotels'] as $hotel) {
                $hotels[] = [
                    'hotelbeds_code' => $hotel['code'] ?? null,
                    'name' => $hotel['name'] ?? 'Unknown Hotel',
                    'category' => $hotel['category'] ?? 3,
                    'description' => $hotel['description'] ?? '',
                    'address' => $hotel['address'] ?? '',
                    'city' => $hotel['city'] ?? '',
                    'country' => $hotel['country'] ?? '',
                    'latitude' => $hotel['coordinates']['latitude'] ?? null,
                    'longitude' => $hotel['coordinates']['longitude'] ?? null,
                    'star_rating' => $hotel['category'] ?? 3,
                    'amenities' => $hotel['amenities'] ?? [],
                    'images' => $hotel['images'] ?? [],
                ];
            }
        }

        return $hotels;
    }

    private function processHotelDetailsResponse(array $response): ?array
    {
        if (isset($response['hotel'])) {
            $hotel = $response['hotel'];
            return [
                'hotelbeds_code' => $hotel['code'] ?? null,
                'name' => $hotel['name'] ?? 'Unknown Hotel',
                'description' => $hotel['description'] ?? '',
                'address' => $hotel['address'] ?? '',
                'city' => $hotel['city'] ?? '',
                'country' => $hotel['country'] ?? '',
                'latitude' => $hotel['coordinates']['latitude'] ?? null,
                'longitude' => $hotel['coordinates']['longitude'] ?? null,
                'star_rating' => $hotel['category'] ?? 3,
                'amenities' => $hotel['amenities'] ?? [],
                'images' => $hotel['images'] ?? [],
                'rooms' => $hotel['rooms'] ?? [],
            ];
        }

        return null;
    }

    private function processRoomSearchResponse(array $response): array
    {
        $rooms = [];
        
        if (isset($response['hotels'])) {
            foreach ($response['hotels'] as $hotel) {
                if (isset($hotel['rooms'])) {
                    foreach ($hotel['rooms'] as $room) {
                        $rooms[] = [
                            'hotel_code' => $hotel['code'] ?? null,
                            'room_code' => $room['code'] ?? null,
                            'name' => $room['name'] ?? 'Standard Room',
                            'description' => $room['description'] ?? '',
                            'price' => $room['price'] ?? 0,
                            'currency' => $room['currency'] ?? 'PHP',
                            'max_guests' => $room['maxOccupancy'] ?? 2,
                            'amenities' => $room['amenities'] ?? [],
                            'images' => $room['images'] ?? [],
                        ];
                    }
                }
            }
        }

        return $rooms;
    }

    private function updateLocalHotel(string $hotelCode, array $hotelData): Hotel
    {
        $hotel = Hotel::where('hotelbeds_code', $hotelCode)->first();
        
        if (!$hotel) {
            $hotel = new Hotel();
        }

        $hotel->fill([
            'name' => $hotelData['name'],
            'description' => $hotelData['description'],
            'address' => $hotelData['address'],
            'city' => $hotelData['city'],
            'country' => $hotelData['country'],
            'latitude' => $hotelData['latitude'],
            'longitude' => $hotelData['longitude'],
            'star_rating' => $hotelData['star_rating'],
            'amenities' => $hotelData['amenities'],
            'images' => $hotelData['images'],
            'hotelbeds_code' => $hotelCode,
        ]);

        $hotel->save();
        return $hotel;
    }

    private function getFallbackHotels(array $criteria): array
    {
        // Return local hotels as fallback
        $query = Hotel::query();
        
        if (isset($criteria['destination'])) {
            $query->where('city', 'like', '%' . $criteria['destination'] . '%');
        }
        
        if (isset($criteria['stars'])) {
            $query->where('star_rating', $criteria['stars']);
        }

        return $query->limit(10)->get()->map(function ($hotel) {
            return $this->formatHotelData($hotel);
        })->toArray();
    }

    private function getFallbackRooms(array $criteria): array
    {
        // Return local rooms as fallback
        $query = Room::with('hotel');
        
        if (isset($criteria['hotel_codes'])) {
            $query->whereHas('hotel', function ($q) use ($criteria) {
                $q->whereIn('hotelbeds_code', $criteria['hotel_codes']);
            });
        }

        return $query->limit(20)->get()->map(function ($room) {
            return [
                'hotel_code' => $room->hotel->hotelbeds_code,
                'room_code' => $room->id,
                'name' => $room->room_type,
                'description' => $room->description,
                'price' => $room->price_per_night,
                'currency' => 'PHP',
                'max_guests' => $room->max_guests,
                'amenities' => $room->amenities ?? [],
                'images' => $room->images ?? [],
            ];
        })->toArray();
    }

    private function getLocalHotelDetails(string $hotelCode): ?array
    {
        $hotel = Hotel::where('hotelbeds_code', $hotelCode)->with('rooms')->first();
        
        if ($hotel) {
            return $this->formatHotelData($hotel);
        }

        return null;
    }

    private function formatHotelData(Hotel $hotel): array
    {
        return [
            'id' => $hotel->id,
            'hotelbeds_code' => $hotel->hotelbeds_code,
            'name' => $hotel->name,
            'description' => $hotel->description,
            'address' => $hotel->address,
            'city' => $hotel->city,
            'country' => $hotel->country,
            'latitude' => $hotel->latitude,
            'longitude' => $hotel->longitude,
            'star_rating' => $hotel->star_rating,
            'amenities' => $hotel->amenities,
            'images' => $hotel->images,
            'rooms' => $hotel->rooms->map(function ($room) {
                return [
                    'id' => $room->id,
                    'room_type' => $room->room_type,
                    'price_per_night' => $room->price_per_night,
                    'available_quantity' => $room->available_quantity,
                    'max_guests' => $room->max_guests,
                ];
            }),
        ];
    }

    private function buildPriceIndex(): void
    {
        if (!$this->priceIndex->isEmpty()) {
            return; // Already built
        }

        $rooms = Room::with('hotel')->get();
        
        foreach ($rooms as $room) {
            $this->priceIndex->insert($room->price_per_night, [
                'room_id' => $room->id,
                'hotel_id' => $room->hotel_id,
                'room_type' => $room->room_type,
                'price' => $room->price_per_night,
                'available' => $room->available_quantity > 0,
            ]);
        }
    }

    private function refreshCache(): void
    {
        $this->hotelCache->clear();
        $this->priceIndex->clear();
        
        // Pre-load frequently accessed hotels
        $popularHotels = Hotel::with('rooms')->limit(20)->get();
        
        foreach ($popularHotels as $hotel) {
            $this->hotelCache->put($hotel->id, $this->formatHotelData($hotel));
        }
    }
}
