<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Room;
use App\Services\HotelBedsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class HotelController extends Controller
{
    private HotelBedsService $hotelBedsService;

    public function __construct(HotelBedsService $hotelBedsService)
    {
        $this->hotelBedsService = $hotelBedsService;
    }

    /**
     * Display a listing of hotels with search and filter capabilities.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Hotel::with(['rooms' => function ($q) {
                $q->active()->available();
            }]);

            // Apply filters
            if ($request->has('city')) {
                $query->inCity($request->city);
            }

            if ($request->has('stars')) {
                $query->withStars($request->stars);
            }

            if ($request->has('min_price') && $request->has('max_price')) {
                $query->whereHas('rooms', function ($q) use ($request) {
                    $q->priceRange($request->min_price, $request->max_price);
                });
            }

            if ($request->has('amenities')) {
                $amenities = is_array($request->amenities) ? $request->amenities : [$request->amenities];
                $query->where(function ($q) use ($amenities) {
                    foreach ($amenities as $amenity) {
                        $q->orWhereJsonContains('amenities', $amenity);
                    }
                });
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $hotels = $query->active()->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $hotels,
                'message' => 'Hotels retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve hotels',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search hotels using HotelBeds API.
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $criteria = $request->only([
                'destination', 'check_in', 'check_out', 'rooms', 
                'adults', 'children', 'min_rate', 'max_rate', 'stars'
            ]);

            $hotels = $this->hotelBedsService->searchHotels($criteria);

            return response()->json([
                'success' => true,
                'data' => $hotels,
                'message' => 'Hotel search completed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Hotel search failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified hotel.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $hotel = Hotel::with(['rooms' => function ($q) {
                $q->active();
            }])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $hotel,
                'message' => 'Hotel retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Hotel not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get hotel details from HotelBeds API.
     */
    public function details(string $hotelCode): JsonResponse
    {
        try {
            $hotelDetails = $this->hotelBedsService->getHotelDetails($hotelCode);

            if (!$hotelDetails) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hotel details not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $hotelDetails,
                'message' => 'Hotel details retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve hotel details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get rooms for a specific hotel.
     */
    public function rooms(Request $request, string $id): JsonResponse
    {
        try {
            $hotel = Hotel::findOrFail($id);
            
            $query = $hotel->rooms()->active();

            // Apply room filters
            if ($request->has('room_type')) {
                $query->byType($request->room_type);
            }

            if ($request->has('min_price') && $request->has('max_price')) {
                $query->priceRange($request->min_price, $request->max_price);
            }

            if ($request->has('guests')) {
                $query->forGuests($request->guests);
            }

            if ($request->has('available_only') && $request->available_only) {
                $query->available();
            }

            $rooms = $query->get();

            return response()->json([
                'success' => true,
                'data' => $rooms,
                'message' => 'Hotel rooms retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve hotel rooms',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search rooms using HotelBeds API.
     */
    public function searchRooms(Request $request): JsonResponse
    {
        try {
            $criteria = $request->only([
                'hotel_codes', 'check_in', 'check_out', 'rooms', 'adults', 'children'
            ]);

            $rooms = $this->hotelBedsService->searchRooms($criteria);

            return response()->json([
                'success' => true,
                'data' => $rooms,
                'message' => 'Room search completed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Room search failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get hotels by price range using BST.
     */
    public function byPriceRange(Request $request): JsonResponse
    {
        try {
            $minPrice = $request->get('min_price', 0);
            $maxPrice = $request->get('max_price', 100000);

            $hotels = $this->hotelBedsService->getHotelsByPriceRange($minPrice, $maxPrice);

            return response()->json([
                'success' => true,
                'data' => $hotels,
                'message' => 'Hotels by price range retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve hotels by price range',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync hotels with HotelBeds API.
     */
    public function sync(Request $request): JsonResponse
    {
        try {
            $hotelCodes = $request->get('hotel_codes', []);
            
            $result = $this->hotelBedsService->syncHotels($hotelCodes);

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Hotel sync completed'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Hotel sync failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get hotel statistics.
     */
    public function stats(string $id): JsonResponse
    {
        try {
            $hotel = Hotel::with('rooms')->findOrFail($id);
            
            $stats = [
                'total_rooms' => $hotel->rooms->count(),
                'available_rooms' => $hotel->getTotalAvailableRooms(),
                'occupancy_rate' => $hotel->getOccupancyRate(),
                'average_price' => $hotel->getAverageRoomPrice(),
                'total_reservations' => $hotel->reservations()->count(),
                'confirmed_reservations' => $hotel->reservations()->confirmed()->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Hotel statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve hotel statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get API status.
     */
    public function apiStatus(): JsonResponse
    {
        try {
            $status = $this->hotelBedsService->getApiStatus();

            return response()->json([
                'success' => true,
                'data' => $status,
                'message' => 'API status retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve API status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
