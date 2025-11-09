@props(['type' => 'login'])

<div id="auth-modal" x-data="{ 
    open: false, 
    type: '{{ $type }}',
    switchType() {
        this.type = this.type === 'login' ? 'register' : 'login';
    },
    openModal(type) {
        this.type = type || 'login';
        this.open = true;
        // Prevent body scroll when modal is open
        document.body.style.overflow = 'hidden';
    },
    closeModal() {
        this.open = false;
        // Restore body scroll
        document.body.style.overflow = '';
        // Reset form errors after closing
        setTimeout(() => {
            const errorElements = document.querySelectorAll('.modal-error');
            errorElements.forEach(el => el.remove());
        }, 300);
    },
    init() {
        console.log('Auth modal initialized');
        // Listen for openAuthModal events
        const self = this;
        const eventHandler = function(e) {
            console.log('openAuthModal event received:', e.detail);
            // Store current URL before opening modal
            if (window.location.pathname !== '/login' && window.location.pathname !== '/register') {
                sessionStorage.setItem('auth_return_url', window.location.href);
            }
            self.openModal(e.detail?.type || 'login');
        };
        window.addEventListener('openAuthModal', eventHandler);
        
        // Expose openModal to window for direct access
        window.authModalOpen = (type) => {
            console.log('authModalOpen called with type:', type);
            // Store current URL before opening modal
            if (window.location.pathname !== '/login' && window.location.pathname !== '/register') {
                sessionStorage.setItem('auth_return_url', window.location.href);
            }
            self.openModal(type);
        };
        
        // Also expose via data attribute for direct DOM access
        this.$el.setAttribute('data-modal-ready', 'true');
        console.log('Modal ready, data-modal-ready attribute set');
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
            class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-black border border-gray-800 shadow-2xl transition-all"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            
            <!-- Close Button -->
            <button 
                @click="closeModal()"
                class="absolute top-4 right-4 text-gray-400 hover:text-white transition-colors z-10">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            
            <!-- Login Form -->
            <div x-show="type === 'login'" class="p-8">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-white mb-2">Welcome Back</h2>
                    <p class="text-gray-400 text-sm">Sign in to your account to continue</p>
                </div>

                <form method="POST" action="{{ route('login') }}" class="space-y-5" x-data="{ returnUrl: sessionStorage.getItem('auth_return_url') || '' }">
                    @csrf
                    <input type="hidden" name="return_url" x-bind:value="returnUrl">

                    <!-- Email Address -->
                    <div>
                        <label for="modal-email" class="block text-sm font-medium text-gray-300 mb-2">Email Address</label>
                        <input id="modal-email" 
                               class="block w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white placeholder-gray-500 transition-all" 
                               type="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required 
                               autofocus 
                               autocomplete="username"
                               placeholder="Enter your email" />
                        @error('email')
                            <p class="mt-2 text-sm text-red-400 modal-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="modal-password" class="block text-sm font-medium text-gray-300 mb-2">Password</label>
                        <input id="modal-password" 
                               class="block w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white placeholder-gray-500 transition-all"
                               type="password"
                               name="password"
                               required 
                               autocomplete="current-password"
                               placeholder="Enter your password" />
                        @error('password')
                            <p class="mt-2 text-sm text-red-400 modal-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between">
                        <label for="modal-remember" class="inline-flex items-center">
                            <input id="modal-remember" 
                                   type="checkbox" 
                                   class="rounded border-gray-600 bg-gray-800 text-primary-green focus:ring-primary-green focus:ring-offset-0" 
                                   name="remember">
                            <span class="ms-2 text-sm text-gray-400">Remember me</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm text-primary-green hover:text-primary-green-hover font-medium transition-colors" href="{{ route('password.request') }}">
                                Forgot password?
                            </a>
                        @endif
                    </div>

                    <!-- Login Button -->
                    <div class="pt-2">
                        <button type="submit" class="w-full py-3 bg-primary-green hover:bg-primary-green-hover text-white font-semibold rounded-lg transition-colors">
                            Sign In
                        </button>
                    </div>

                    <!-- Sign Up Link -->
                    <div class="pt-4 border-t border-gray-800">
                        <p class="text-center text-sm text-gray-400">
                            Don't have an account? 
                            <button type="button" @click="switchType()" class="text-primary-green hover:text-primary-green-hover font-semibold underline">
                                Create Account
                            </button>
                        </p>
                    </div>
                </form>
            </div>
            
            <!-- Register Form -->
            <div x-show="type === 'register'" class="p-8">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-white mb-2">Create Account</h2>
                    <p class="text-gray-400 text-sm">Join Belmont Hotel and start booking your perfect getaway</p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-5" x-data="{ returnUrl: sessionStorage.getItem('auth_return_url') || '' }">
                    @csrf
                    <input type="hidden" name="return_url" x-bind:value="returnUrl">

                    <!-- Name -->
                    <div>
                        <label for="modal-name" class="block text-sm font-medium text-gray-300 mb-2">Full Name</label>
                        <input id="modal-name" 
                               class="block w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white placeholder-gray-500 transition-all" 
                               type="text" 
                               name="name" 
                               value="{{ old('name') }}" 
                               required 
                               autofocus 
                               autocomplete="name"
                               placeholder="Enter your full name" />
                        @error('name')
                            <p class="mt-2 text-sm text-red-400 modal-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email Address -->
                    <div>
                        <label for="modal-register-email" class="block text-sm font-medium text-gray-300 mb-2">Email Address</label>
                        <input id="modal-register-email" 
                               class="block w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white placeholder-gray-500 transition-all" 
                               type="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required 
                               autocomplete="username"
                               placeholder="Enter your email" />
                        @error('email')
                            <p class="mt-2 text-sm text-red-400 modal-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="modal-register-password" class="block text-sm font-medium text-gray-300 mb-2">Password</label>
                        <input id="modal-register-password" 
                               class="block w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white placeholder-gray-500 transition-all"
                               type="password"
                               name="password"
                               required 
                               autocomplete="new-password"
                               placeholder="Create a password" />
                        @error('password')
                            <p class="mt-2 text-sm text-red-400 modal-error">{{ $message }}</p>
                        @enderror
                        @if($errors->has('password'))
                            <p class="mt-1 text-xs text-gray-500">Password must be at least 8 characters</p>
                        @endif
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="modal-password-confirmation" class="block text-sm font-medium text-gray-300 mb-2">Confirm Password</label>
                        <input id="modal-password-confirmation" 
                               class="block w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-primary-green focus:border-transparent text-white placeholder-gray-500 transition-all"
                               type="password"
                               name="password_confirmation" 
                               required 
                               autocomplete="new-password"
                               placeholder="Confirm your password" />
                        @error('password_confirmation')
                            <p class="mt-2 text-sm text-red-400 modal-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Register Button -->
                    <div class="pt-2">
                        <button type="submit" class="w-full py-3 bg-primary-green hover:bg-primary-green-hover text-white font-semibold rounded-lg transition-colors">
                            Create Account
                        </button>
                    </div>

                    <!-- Sign In Link -->
                    <div class="pt-4 border-t border-gray-800">
                        <p class="text-center text-sm text-gray-400">
                            Already have an account? 
                            <button type="button" @click="switchType()" class="text-primary-green hover:text-primary-green-hover font-semibold underline">
                                Sign In
                            </button>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
[x-cloak] { display: none !important; }
</style>

