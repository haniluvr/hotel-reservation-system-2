@extends('layouts.app')

@section('title', 'My Bookings - The Belmont Hotel - Discover Your Perfect Getaway. Experience luxury and comfort at Belmont Hotel El Nido, Palawan.')

@section('content')
<div class="min-h-screen bg-black py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">My Bookings</h1>
            <p class="text-gray-300 text-lg">View and manage your reservations</p>
        </div>

        @if($bookings->count() > 0)
            <div class="space-y-6">
                @foreach($bookings as $booking)
                    <div class="bg-gray-900 rounded-2xl shadow-lg border border-gray-800 overflow-hidden">
                        <div class="p-6">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-4 mb-4">
                                        <h3 class="text-xl font-semibold text-white">{{ $booking->room->room_type }}</h3>
                                        <span class="px-3 py-1 rounded-full text-xs font-medium
                                            @if($booking->status == 'confirmed') bg-green-900/30 text-green-400 border border-green-800
                                            @elseif($booking->status == 'pending') bg-yellow-900/30 text-yellow-400 border border-yellow-800
                                            @elseif($booking->status == 'cancelled') bg-red-900/30 text-red-400 border border-red-800
                                            @elseif($booking->status == 'completed') bg-blue-900/30 text-blue-400 border border-blue-800
                                            @else bg-gray-800 text-gray-400 border border-gray-700
                                            @endif">
                                            {{ ucfirst($booking->status) }}
                                        </span>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-300">
                                        <div>
                                            <span class="font-medium text-gray-400">Hotel:</span> <span class="text-white">{{ $booking->room->hotel->name }}</span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-400">Reservation #:</span> <span class="text-white">{{ $booking->reservation_number }}</span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-400">Check-in:</span> <span class="text-white">{{ \Carbon\Carbon::parse($booking->check_in_date)->format('M d, Y') }}</span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-400">Check-out:</span> <span class="text-white">{{ \Carbon\Carbon::parse($booking->check_out_date)->format('M d, Y') }}</span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-400">Guests:</span> <span class="text-white">{{ $booking->adults }} {{ $booking->adults == 1 ? 'adult' : 'adults' }}
                                            @if($booking->children > 0)
                                                , {{ $booking->children }} {{ $booking->children == 1 ? 'child' : 'children' }}
                                            @endif</span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-400">Total Amount:</span> 
                                            <span class="font-semibold text-primary-green">₱{{ number_format($booking->total_amount, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4 md:mt-0 md:ml-6">
                                    <a href="{{ route('bookings.show', $booking->id) }}"
                                        class="inline-block px-4 py-2 bg-primary-green hover:bg-primary-green-hover text-white font-medium rounded-lg transition-colors duration-200">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $bookings->links() }}
            </div>
        @else
            <div class="bg-gray-900 rounded-2xl shadow-lg border border-gray-800 p-12 text-center">
                <svg class="w-16 h-16 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="text-xl font-semibold text-white mb-2">No Bookings Yet</h3>
                <p class="text-gray-300 mb-6">You haven't made any reservations yet.</p>
                <a href="{{ route('accommodations.index') }}"
                    class="inline-block px-6 py-3 bg-primary-green hover:bg-primary-green-hover text-white font-semibold rounded-lg transition-colors duration-200">
                    Browse Rooms
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

