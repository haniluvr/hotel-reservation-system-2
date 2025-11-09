<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Welcome Back</h2>
        <p class="text-gray-600 text-sm">Sign in to your account to continue</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email Address')" class="text-gray-700 font-medium mb-2" />
            <x-text-input id="email" 
                          class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#667f5f] focus:border-transparent transition-all" 
                          type="email" 
                          name="email" 
                          :value="old('email')" 
                          required 
                          autofocus 
                          autocomplete="username"
                          placeholder="Enter your email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" class="text-gray-700 font-medium mb-2" />
            <x-text-input id="password" 
                          class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#667f5f] focus:border-transparent transition-all"
                          type="password"
                          name="password"
                          required 
                          autocomplete="current-password"
                          placeholder="Enter your password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" 
                       type="checkbox" 
                       class="rounded border-gray-300 text-[#667f5f] shadow-sm focus:ring-[#667f5f] focus:ring-offset-0" 
                       name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-[#667f5f] hover:text-[#4a5d3a] font-medium transition-colors" href="{{ route('password.request') }}">
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <!-- Login Button -->
        <div class="pt-2">
            <x-primary-button class="w-full justify-center py-3 bg-[#667f5f] hover:bg-[#4a5d3a] text-white font-semibold rounded-lg transition-colors">
                {{ __('Sign In') }}
            </x-primary-button>
        </div>

        <!-- Sign Up Link -->
        <div class="pt-4 border-t border-gray-200">
            <p class="text-center text-sm text-gray-600 mb-3">
                Don't have an account?
            </p>
            <a href="{{ route('register') }}" 
               class="block w-full text-center py-3 bg-white border-2 border-[#667f5f] text-[#667f5f] hover:bg-[#667f5f] hover:text-white font-semibold rounded-lg transition-colors">
                Create Account
            </a>
        </div>
    </form>
</x-guest-layout>
