<div id="search-modal" x-data="{ 
    open: false,
    roomType: '',
    checkIn: '',
    checkOut: '',
    adults: 2,
    children: 0,
    init() {
        const self = this;
        
        // Listen for openSearchModal events
        const eventHandler = function(e) {
            self.openModal();
        };
        window.addEventListener('openSearchModal', eventHandler);
        
        // Expose openModal to window for direct access
        window.searchModalOpen = () => {
            self.openModal();
        };
        
        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        this.checkIn = today;
        
        // Set check-out to tomorrow by default
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        this.checkOut = tomorrow.toISOString().split('T')[0];
        
        this.$el.setAttribute('data-modal-ready', 'true');
    },
    openModal() {
        this.open = true;
        document.body.style.overflow = 'hidden';
    },
    closeModal() {
        this.open = false;
        document.body.style.overflow = '';
    },
    validateDates() {
        if (this.checkIn && this.checkOut) {
            const checkInDate = new Date(this.checkIn);
            const checkOutDate = new Date(this.checkOut);
            
            if (checkOutDate <= checkInDate) {
                const nextDay = new Date(checkInDate);
                nextDay.setDate(nextDay.getDate() + 1);
                this.checkOut = nextDay.toISOString().split('T')[0];
            }
        }
    },
    submitSearch() {
        const form = this.$el.querySelector('#search-form');
        if (form) {
            form.submit();
        }
    }
}" 
x-show="open"
x-cloak
@keydown.escape.window="closeModal()"
class="fixed inset-0 z-50 overflow-y-auto"
style="display: none;"
x-transition:enter="ease-out duration-300"
x-transition:enter-start="opacity-0"
x-transition:enter-end="opacity-100"
x-transition:leave="ease-in duration-200"
x-transition:leave-start="opacity-100"
x-transition:leave-end="opacity-0">
    
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" @click="closeModal()"></div>
    
    <!-- Modal Container -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div 
            @click.away="closeModal()"
            class="relative w-full max-w-lg transform overflow-hidden rounded-xl bg-black border border-gray-800 shadow-2xl transition-all"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            
            <!-- Close Button -->
            <button 
                @click="closeModal()"
                class="absolute top-3 right-3 text-gray-400 hover:text-white transition-colors z-10">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            
            <!-- Search Form -->
            <div class="p-6">
                <div class="mb-4">
                    <h2 class="text-xl font-bold text-white mb-1">Search Accommodations</h2>
                    <p class="text-gray-400 text-xs">Find your perfect stay at Belmont Hotel</p>
                </div>

                <form id="search-form" method="GET" action="{{ route('hotels.search') }}" class="space-y-4">
                    <!-- Room Type -->
                    <div>
                        <label for="search-room-type" class="block text-xs font-medium text-gray-300 mb-1.5">
                            <svg class="w-3.5 h-3.5 inline-block mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            Room Type
                        </label>
                        <select 
                            id="search-room-type"
                            x-model="roomType"
                            name="room_type"
                            class="block w-full px-3 py-2 text-sm bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white transition-all">
                            <option value="">All Room Types</option>
                            @php
                                $roomTypes = \App\Models\Room::select('room_type')
                                    ->distinct()
                                    ->orderBy('room_type')
                                    ->pluck('room_type');
                            @endphp
                            @foreach($roomTypes as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date Range -->
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Check-in -->
                        <div>
                            <label for="search-check-in" class="block text-xs font-medium text-gray-300 mb-1.5">
                                <svg class="w-3.5 h-3.5 inline-block mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Check-in
                            </label>
                            <input 
                                id="search-check-in"
                                x-model="checkIn"
                                @change="validateDates()"
                                type="date" 
                                name="check_in"
                                :min="new Date().toISOString().split('T')[0]"
                                class="block w-full px-3 py-2 text-sm bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white transition-all"
                            />
                        </div>

                        <!-- Check-out -->
                        <div>
                            <label for="search-check-out" class="block text-xs font-medium text-gray-300 mb-1.5">
                                <svg class="w-3.5 h-3.5 inline-block mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Check-out
                            </label>
                            <input 
                                id="search-check-out"
                                x-model="checkOut"
                                @change="validateDates()"
                                type="date" 
                                name="check_out"
                                :min="checkIn || new Date().toISOString().split('T')[0]"
                                class="block w-full px-3 py-2 text-sm bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white transition-all"
                            />
                        </div>
                    </div>

                    <!-- Adults and Children -->
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Adults -->
                        <div>
                            <label for="search-adults" class="block text-xs font-medium text-gray-300 mb-1.5">
                                <svg class="w-3.5 h-3.5 inline-block mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Adults
                            </label>
                            <div class="flex items-center gap-2">
                                <button 
                                    type="button"
                                    @click="adults = Math.max(1, adults - 1)"
                                    class="w-8 h-8 rounded-lg bg-gray-800 border border-gray-700 text-white hover:bg-gray-700 transition-colors flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                    </svg>
                                </button>
                                <input 
                                    id="search-adults"
                                    x-model="adults"
                                    type="number" 
                                    name="adults"
                                    min="1"
                                    max="10"
                                    class="flex-1 px-3 py-2 text-sm bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white text-center transition-all"
                                    required
                                />
                                <button 
                                    type="button"
                                    @click="adults = Math.min(10, adults + 1)"
                                    class="w-8 h-8 rounded-lg bg-gray-800 border border-gray-700 text-white hover:bg-gray-700 transition-colors flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Children -->
                        <div>
                            <label for="search-children" class="block text-xs font-medium text-gray-300 mb-1.5">
                                <svg class="w-3.5 h-3.5 inline-block mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                Children
                            </label>
                            <div class="flex items-center gap-2">
                                <button 
                                    type="button"
                                    @click="children = Math.max(0, children - 1)"
                                    class="w-8 h-8 rounded-lg bg-gray-800 border border-gray-700 text-white hover:bg-gray-700 transition-colors flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                    </svg>
                                </button>
                                <input 
                                    id="search-children"
                                    x-model="children"
                                    type="number" 
                                    name="children"
                                    min="0"
                                    max="10"
                                    class="flex-1 px-3 py-2 text-sm bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white text-center transition-all"
                                    required
                                />
                                <button 
                                    type="button"
                                    @click="children = Math.min(10, children + 1)"
                                    class="w-8 h-8 rounded-lg bg-gray-800 border border-gray-700 text-white hover:bg-gray-700 transition-colors flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit"
                        class="w-full py-2.5 px-4 bg-primary-green hover:bg-primary-green-hover text-black font-semibold text-sm rounded-lg transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Search Accommodations
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
