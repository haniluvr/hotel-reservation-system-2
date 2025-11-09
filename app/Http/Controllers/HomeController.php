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
     * Display the homepage - Belmont Hotel El Nido
     */
    public function index()
    {
        // Get Belmont Hotel (single property)
        $hotel = Hotel::with(['rooms' => function ($query) {
            $query->active()->available();
        }])
        ->where('name', 'Belmont Hotel')
        ->firstOrFail();

        // Get featured rooms (first 6 active rooms, regardless of availability)
        $featuredRooms = $hotel->rooms()
            ->active()
            ->limit(6)
            ->get()
            ->map(function ($room) {
                return [
                    'id' => $room->id,
                    'room_type' => $room->room_type,
                    'slug' => $room->slug,
                    'price_per_night' => $room->price_per_night ?? 0,
                    'description' => $room->description,
                    'image' => $room->getRoomImages()[0] ?? 'https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
                    'images' => $room->getRoomImages(),
                    'available_quantity' => $room->available_quantity ?? 0,
                    'max_guests' => $room->max_guests ?? 2,
                    'max_adults' => $room->max_adults ?? 2,
                    'max_children' => $room->max_children ?? 0,
                    'size' => $room->size,
                    'amenities' => $room->amenities ?? [],
                ];
            });

        $popularDestinations = [
            [
                'name' => 'Big Lagoon',
                'image' => 'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=900&auto=format&fit=crop&q=80',
                'count' => 'Beach • Snorkeling',
                'description' => 'Hidden paradise accessible through a narrow opening'
            ],
            [
                'name' => 'Small Lagoon',
                'image' => 'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=900&auto=format&fit=crop&q=80',
                'count' => 'Beach • Snorkeling',
                'description' => 'Hidden paradise accessible through a narrow opening'
            ],
            [
                'name' => 'Secret Lagoon',
                'image' => 'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=900&auto=format&fit=crop&q=80',
                'count' => 'Beach • Snorkeling',
                'description' => 'Hidden paradise accessible through a narrow opening'
            ]
        ];

        return view('home', compact('hotel', 'featuredRooms', 'popularDestinations'));
    }

    /**
     * Search rooms (updated to return rooms instead of hotels)
     */
    public function search(Request $request)
    {
        // Get Belmont Hotel
        $hotel = Hotel::where('name', 'Belmont Hotel')->firstOrFail();

        // Start with all rooms
        $query = Room::where('hotel_id', $hotel->id)->active();

        // Apply search filters
        if ($request->filled('room_type')) {
            $query->where('room_type', $request->room_type);
        }

        if ($request->filled('check_in') && $request->filled('check_out')) {
            // Filter by availability - rooms with available quantity > 0
            $query->where('available_quantity', '>', 0);
        }

        if ($request->filled('adults') || $request->filled('children')) {
            $totalGuests = ($request->adults ?? 0) + ($request->children ?? 0);
            if ($totalGuests > 0) {
                $query->where('max_guests', '>=', $totalGuests);
            }
            if ($request->filled('adults')) {
                $query->where('max_adults', '>=', $request->adults);
            }
        }

        if ($request->filled('min_price')) {
            $query->where('price_per_night', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price_per_night', '<=', $request->max_price);
        }

        // Apply sorting
        $sort = $request->get('sort', 'popularity');
        switch ($sort) {
            case 'price_low':
                $query->orderBy('price_per_night', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price_per_night', 'desc');
                break;
            case 'popularity':
            default:
                $query->orderBy('available_quantity', 'desc')
                      ->orderBy('price_per_night', 'asc');
                break;
        }

        $rooms = $query->with('hotel')->paginate(12);

        return view('hotels.search', compact('rooms', 'hotel'));
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
    public function showRoom($slug)
    {
        $room = Room::where('slug', $slug)
            ->active()
            ->firstOrFail();

        $hotel = $room->hotel;

        $similarRooms = Room::where('hotel_id', $hotel->id)
            ->where('id', '!=', $room->id)
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

    /**
     * Display all accommodations (rooms)
     */
    public function accommodations(Request $request)
    {
        // Get Belmont Hotel (should be the only hotel)
        $hotel = Hotel::firstOrFail();

        // Start with all rooms
        $query = Room::where('hotel_id', $hotel->id)->active();

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('room_type', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('room_type')) {
            $query->where('room_type', $request->room_type);
        }

        if ($request->filled('min_price')) {
            $query->where('price_per_night', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price_per_night', '<=', $request->max_price);
        }

        if ($request->filled('adults') || $request->filled('children')) {
            $totalGuests = ($request->adults ?? 0) + ($request->children ?? 0);
            if ($totalGuests > 0) {
                $query->where('max_guests', '>=', $totalGuests);
            }
            if ($request->filled('adults')) {
                $query->where('max_adults', '>=', $request->adults);
            }
        }

        // Apply sorting
        $sort = $request->get('sort', 'popularity');
        switch ($sort) {
            case 'price_low':
                $query->orderBy('price_per_night', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price_per_night', 'desc');
                break;
            case 'popularity':
            default:
                // Default: Sort by availability (most available first), then by price
                $query->orderBy('available_quantity', 'desc')
                      ->orderBy('price_per_night', 'asc');
                break;
        }

        $rooms = $query->paginate(12);

        return view('accommodations.index', compact('rooms', 'hotel'));
    }
}
