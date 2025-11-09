@extends('layouts.app')

@section('title', 'Account - The Belmont Hotel - Discover Your Perfect Getaway. Experience luxury and comfort at Belmont Hotel El Nido, Palawan.')

@section('content')
<div class="min-h-screen bg-black pt-24 pb-12 px-4 sm:px-6 lg:px-8" 
     x-data="{ 
         activeTab: 'personal',
         deleteModalOpen: false,
         deleteReason: '',
         originalName: '{{ $user->name }}',
         originalEmail: '{{ $user->email }}',
         originalPhone: '{{ $user->phone ?? '' }}',
         currentName: '{{ $user->name }}',
         currentEmail: '{{ $user->email }}',
         currentPhone: '{{ $user->phone ?? '' }}',
         password: '',
         get hasChanges() {
             return this.currentName !== this.originalName || 
                    this.currentEmail !== this.originalEmail || 
                    this.currentPhone !== this.originalPhone;
         },
         get canSave() {
             return !this.hasChanges || (this.hasChanges && this.password.length > 0);
         }
     }"
     @open-delete-account-modal.window="deleteModalOpen = true">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">My Account</h1>
            <p class="text-gray-300 text-lg">Manage your account information and bookings</p>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-900/30 border border-green-800 text-green-300 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 bg-red-900/30 border border-red-800 text-red-300 px-4 py-3 rounded-lg">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-gray-900 rounded-2xl shadow-lg border border-gray-800 p-4">
                    <nav class="space-y-2">
                        <button 
                            @click="activeTab = 'personal'"
                            :class="activeTab === 'personal' ? 'bg-primary-green text-white' : 'text-gray-300 hover:bg-gray-800'"
                            class="w-full text-left px-4 py-3 rounded-lg transition-colors duration-200 font-medium">
                            Personal Information
                        </button>
                        <button 
                            @click="activeTab = 'reservations'"
                            :class="activeTab === 'reservations' ? 'bg-primary-green text-white' : 'text-gray-300 hover:bg-gray-800'"
                            class="w-full text-left px-4 py-3 rounded-lg transition-colors duration-200 font-medium">
                            Reservation History
                        </button>
                        <button 
                            @click="activeTab = 'settings'"
                            :class="activeTab === 'settings' ? 'bg-primary-green text-white' : 'text-gray-300 hover:bg-gray-800'"
                            class="w-full text-left px-4 py-3 rounded-lg transition-colors duration-200 font-medium">
                            Account Settings
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Content Containers -->
            <div class="lg:col-span-3">
                <!-- Personal Information Container -->
                <div x-show="activeTab === 'personal'" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform translate-y-4"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     class="bg-gray-900 rounded-2xl shadow-lg border border-gray-800 p-6">
                    <h2 class="text-2xl font-semibold text-white mb-6">Personal Information</h2>
                    
                    <form method="POST" action="{{ route('account.update-personal-info') }}" class="space-y-6">
                        @csrf
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-400 mb-2">Full Name</label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   x-model="currentName"
                                   value="{{ old('name', $user->name) }}"
                                   class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white placeholder-gray-500">
                            @error('name')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-400 mb-2">Email Address</label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   x-model="currentEmail"
                                   value="{{ old('email', $user->email) }}"
                                   class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white placeholder-gray-500">
                            @error('email')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-400 mb-2">Phone Number</label>
                            <input type="text" 
                                   id="phone" 
                                   name="phone" 
                                   x-model="currentPhone"
                                   value="{{ old('phone', $user->phone) }}"
                                   placeholder="Not provided"
                                   class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white placeholder-gray-500">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password Confirmation (shown when changes are detected) -->
                        <div x-show="hasChanges"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform translate-y-2"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 transform translate-y-0"
                             x-transition:leave-end="opacity-0 transform translate-y-2"
                             class="pt-4 border-t border-gray-800">
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-400 mb-2">
                                    Confirm Password <span class="text-red-400">*</span>
                                </label>
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       x-model="password"
                                       :required="hasChanges"
                                       placeholder="Enter your password to confirm changes"
                                       class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white placeholder-gray-500">
                                @error('password')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Please confirm your password to save changes.</p>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button type="submit"
                                    :disabled="!canSave"
                                    :class="!canSave ? 'opacity-50 cursor-not-allowed' : ''"
                                    class="px-6 py-3 bg-primary-green hover:bg-primary-green-hover text-white font-medium rounded-lg transition-colors duration-200">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Reservation History Container -->
                <div x-show="activeTab === 'reservations'" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform translate-y-4"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     class="bg-gray-900 rounded-2xl shadow-lg border border-gray-800 p-6">
                    <h2 class="text-2xl font-semibold text-white mb-2">Reservation History</h2>
                    <p class="text-gray-300 text-sm mb-6">View and manage your reservations</p>

                    @if($bookings->count() > 0)
                        <div class="space-y-6">
                            @foreach($bookings as $booking)
                                <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
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
                        <div class="bg-gray-800 rounded-xl border border-gray-700 p-12 text-center">
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

                <!-- Account Settings Container -->
                <div x-show="activeTab === 'settings'" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform translate-y-4"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     class="bg-gray-900 rounded-2xl shadow-lg border border-gray-800 p-6">
                    <h2 class="text-2xl font-semibold text-white mb-6">Account Settings</h2>
                    
                    <div class="space-y-8">
                        <!-- Change Password Section -->
                        <div>
                            <h3 class="text-lg font-medium text-white mb-4">Change Password</h3>
                            <p class="text-gray-300 text-sm mb-6">Update your password to keep your account secure.</p>
                            
                            <form method="POST" action="{{ route('account.change-password') }}" class="space-y-4">
                                @csrf
                                <div>
                                    <label for="old_password" class="block text-sm font-medium text-gray-400 mb-2">Current Password</label>
                                    <input type="password" 
                                           id="old_password" 
                                           name="old_password" 
                                           required
                                           class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white placeholder-gray-500">
                                    @error('old_password')
                                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-400 mb-2">New Password</label>
                                    <input type="password" 
                                           id="password" 
                                           name="password" 
                                           required
                                           class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white placeholder-gray-500">
                                    @error('password')
                                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="password_confirmation" class="block text-sm font-medium text-gray-400 mb-2">Confirm New Password</label>
                                    <input type="password" 
                                           id="password_confirmation" 
                                           name="password_confirmation" 
                                           required
                                           class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white placeholder-gray-500">
                                </div>

                                <div class="flex justify-end">
                                    <button type="submit"
                                            class="px-6 py-3 bg-primary-green hover:bg-primary-green-hover text-white font-medium rounded-lg transition-colors duration-200">
                                        Change Password
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Logout Section -->
                        <div class="pt-6 border-t border-gray-800">
                            <h3 class="text-lg font-medium text-white mb-4">Logout</h3>
                            <p class="text-gray-300 text-sm mb-6">Sign out of your account. You can sign back in anytime.</p>
                            
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <div class="flex justify-end">
                                    <button type="submit"
                                            class="px-6 py-3 bg-gray-700 hover:bg-gray-600 text-white font-medium rounded-lg transition-colors duration-200">
                                        Logout
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Delete Account Section -->
                        <div class="pt-6 border-t border-gray-800">
                            <h3 class="text-lg font-medium text-white mb-4">Delete Account</h3>
                            <p class="text-gray-300 text-sm mb-6">Permanently delete your account and all associated data. This action cannot be undone.</p>
                            
                            <div class="flex justify-end">
                                <button type="button"
                                        @click="$dispatch('open-delete-account-modal')"
                                        class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors duration-200">
                                    Delete Account
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Account Confirmation Modal -->
    <div x-show="deleteModalOpen"
         x-cloak
         @keydown.escape.window="deleteModalOpen = false"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" @click="deleteModalOpen = false"></div>
        
        <!-- Modal Container -->
        <div class="flex min-h-full items-center justify-center p-4">
            <div 
                @click.away="deleteModalOpen = false"
                class="relative w-full max-w-md transform overflow-hidden rounded-xl bg-gray-900 border border-gray-800 shadow-2xl transition-all"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                
                <!-- Close Button -->
                <button 
                    @click="deleteModalOpen = false"
                    class="absolute top-3 right-3 text-gray-400 hover:text-white transition-colors z-10">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                
                <!-- Modal Content -->
                <div class="p-6">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-white mb-2">Delete Account</h2>
                        <p class="text-gray-300 text-sm">Are you sure you want to delete your account? This action cannot be undone.</p>
                    </div>

                    <form method="POST" action="{{ route('account.archive') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="confirm" value="1">
                        
                        <!-- Reason (Optional) -->
                        <div>
                            <label for="delete_reason" class="block text-sm font-medium text-gray-300 mb-2">
                                Reason for deletion (optional)
                            </label>
                            <textarea 
                                id="delete_reason"
                                name="reason"
                                x-model="deleteReason"
                                rows="4"
                                placeholder="Please let us know why you're deleting your account..."
                                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-red-600 focus:border-transparent text-white placeholder-gray-500 resize-none"></textarea>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-3 pt-4">
                            <button 
                                type="button"
                                @click="deleteModalOpen = false"
                                class="flex-1 px-4 py-3 bg-gray-800 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200">
                                Cancel
                            </button>
                            <button 
                                type="submit"
                                class="flex-1 px-4 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors duration-200">
                                Delete Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
