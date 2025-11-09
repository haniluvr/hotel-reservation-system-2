@extends('layouts.app')

@section('title', 'Reviews - The Belmont Hotel - Discover Your Perfect Getaway. Experience luxury and comfort at Belmont Hotel El Nido, Palawan.')

@section('content')
<div class="min-h-screen bg-black py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-8">Guest Reviews</h1>

        @if($reviews->count() > 0)
            <div class="space-y-6">
                @foreach($reviews as $review)
                    <div class="bg-gray-900 rounded-2xl shadow-lg border border-gray-800 p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <div class="flex items-center mb-2">
                                    <div class="flex text-yellow-400 mr-3">
                                        @for($i = 0; $i < $review->rating; $i++)
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @endfor
                                    </div>
                                    <span class="text-white font-semibold">{{ $review->user->name }}</span>
                                </div>
                                <p class="text-sm text-gray-400">{{ $review->created_at->format('F d, Y') }}</p>
                            </div>
                        </div>
                        
                        @if($review->comment)
                            <p class="text-gray-300 mb-4">{{ $review->comment }}</p>
                        @endif

                        @if($review->room)
                            <p class="text-sm text-gray-500">Room: {{ $review->room->room_type }}</p>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $reviews->links() }}
            </div>
        @else
            <div class="bg-gray-900 rounded-2xl shadow-lg border border-gray-800 p-12 text-center">
                <p class="text-gray-400 text-lg">No reviews yet. Be the first to review!</p>
            </div>
        @endif
    </div>
</div>
@endsection

