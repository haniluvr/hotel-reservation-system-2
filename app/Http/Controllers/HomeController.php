<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hotel;
use App\Models\Room;
use App\Services\HotelBedsService;

class HomeController extends Controller
{
    private HotelBedsService $hotelBedsService;

    public function __construct(HotelBedsService $hotelBedsService)
    {
        $this->hotelBedsService = $hotelBedsService;
    }

    /**
     * Display the homepage with scroll expansion hero
     */
    public function index()
    {
        // Get featured hotels
        $featuredHotels = Hotel::with(['rooms' => function ($query) {
            $query->active()->available();
        }])
        ->active()
        ->withStars(4)
        ->limit(6)
        ->get();

        // Get popular destinations with images and hotel counts
        $popularDestinations = [
            [
                'name' => 'Manila',
                'image' => 'https://images.unsplash.com/photo-1555993539-1732b0258235?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80',
                'count' => Hotel::where('city', 'Manila')->active()->count() . ' hotels'
            ],
            [
                'name' => 'Cebu',
                'image' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80',
                'count' => Hotel::where('city', 'Cebu')->active()->count() . ' hotels'
            ],
            [
                'name' => 'Boracay',
                'image' => 'https://images.unsplash.com/photo-1506905925346-14bda5d4f34f?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80',
                'count' => Hotel::where('city', 'Boracay')->active()->count() . ' hotels'
            ],
            [
                'name' => 'Palawan',
                'image' => 'https://images.unsplash.com/photo-1506905925346-14bda5d4f34f?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80',
                'count' => Hotel::where('city', 'Palawan')->active()->count() . ' hotels'
            ],
            [
                'name' => 'Davao',
                'image' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80',
                'count' => Hotel::where('city', 'Davao')->active()->count() . ' hotels'
            ],
            [
                'name' => 'Baguio',
                'image' => 'https://images.unsplash.com/photo-1555993539-1732b0258235?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80',
                'count' => Hotel::where('city', 'Baguio')->active()->count() . ' hotels'
            ],
            [
                'name' => 'Iloilo',
                'image' => 'https://images.unsplash.com/photo-1506905925346-14bda5d4f34f?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80',
                'count' => Hotel::where('city', 'Iloilo')->active()->count() . ' hotels'
            ],
            [
                'name' => 'Bohol',
                'image' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80',
                'count' => Hotel::where('city', 'Bohol')->active()->count() . ' hotels'
            ]
        ];

        return view('home', compact('featuredHotels', 'popularDestinations'));
    }

    /**
     * Search hotels
     */
    public function search(Request $request)
    {
        $query = Hotel::with(['rooms' => function ($q) {
            $q->active()->available();
        }]);

        // Apply search filters
        if ($request->filled('destination')) {
            $query->inCity($request->destination);
        }

        if ($request->filled('check_in') && $request->filled('check_out')) {
            // Filter rooms by availability for the selected dates
            $query->whereHas('rooms', function ($q) use ($request) {
                $q->where('available_quantity', '>', 0);
            });
        }

        if ($request->filled('guests')) {
            $query->whereHas('rooms', function ($q) use ($request) {
                $q->forGuests($request->guests);
            });
        }

        if ($request->filled('min_price') && $request->filled('max_price')) {
            $query->whereHas('rooms', function ($q) use ($request) {
                $q->priceRange($request->min_price, $request->max_price);
            });
        }

        if ($request->filled('stars')) {
            $query->withStars($request->stars);
        }

        if ($request->filled('amenities')) {
            $amenities = is_array($request->amenities) ? $request->amenities : [$request->amenities];
            $query->where(function ($q) use ($amenities) {
                foreach ($amenities as $amenity) {
                    $q->orWhereJsonContains('amenities', $amenity);
                }
            });
        }

        $hotels = $query->active()->paginate(12);

        return view('hotels.search', compact('hotels'));
    }

    /**
     * Show hotel details
     */
    public function showHotel($id)
    {
        $hotel = Hotel::with(['rooms' => function ($q) {
            $q->active();
        }])->findOrFail($id);

        $relatedHotels = Hotel::where('city', $hotel->city)
            ->where('id', '!=', $hotel->id)
            ->active()
            ->limit(4)
            ->get();

        return view('hotels.show', compact('hotel', 'relatedHotels'));
    }

    /**
     * Show room details
     */
    public function showRoom($hotelId, $roomId)
    {
        $hotel = Hotel::findOrFail($hotelId);
        $room = Room::where('hotel_id', $hotelId)
            ->where('id', $roomId)
            ->active()
            ->firstOrFail();

        $similarRooms = Room::where('hotel_id', $hotelId)
            ->where('id', '!=', $roomId)
            ->where('room_type', 'like', '%' . explode(' ', $room->room_type)[0] . '%')
            ->active()
            ->limit(3)
            ->get();

        return view('rooms.show', compact('hotel', 'room', 'similarRooms'));
    }

    /**
     * Get search suggestions
     */
    public function searchSuggestions(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $suggestions = Hotel::select('city', 'country')
            ->where('city', 'like', '%' . $query . '%')
            ->active()
            ->distinct()
            ->limit(5)
            ->get()
            ->map(function ($hotel) {
                return [
                    'text' => $hotel->city . ', ' . $hotel->country,
                    'value' => $hotel->city
                ];
            });

        return response()->json($suggestions);
    }
}
