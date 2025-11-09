<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Create Account</h2>
        <p class="text-gray-600 text-sm">Join Belmont Hotel and start booking your perfect getaway</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Full Name')" class="text-gray-700 font-medium mb-2" />
            <x-text-input id="name" 
                          class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#667f5f] focus:border-transparent transition-all" 
                          type="text" 
                          name="name" 
                          :value="old('name')" 
                          required 
                          autofocus 
                          autocomplete="name"
                          placeholder="Enter your full name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email Address')" class="text-gray-700 font-medium mb-2" />
            <x-text-input id="email" 
                          class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#667f5f] focus:border-transparent transition-all" 
                          type="email" 
                          name="email" 
                          :value="old('email')" 
                          required 
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
                          autocomplete="new-password"
                          placeholder="Create a password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
            @if($errors->has('password'))
                <p class="mt-1 text-xs text-gray-500">Password must be at least 8 characters</p>
            @endif
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-gray-700 font-medium mb-2" />
            <x-text-input id="password_confirmation" 
                          class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#667f5f] focus:border-transparent transition-all"
                          type="password"
                          name="password_confirmation" 
                          required 
                          autocomplete="new-password"
                          placeholder="Confirm your password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Register Button -->
        <div class="pt-2">
            <x-primary-button class="w-full justify-center py-3 bg-[#667f5f] hover:bg-[#4a5d3a] text-white font-semibold rounded-lg transition-colors">
                {{ __('Create Account') }}
            </x-primary-button>
        </div>

        <!-- Sign In Link -->
        <div class="pt-4 border-t border-gray-200">
            <p class="text-center text-sm text-gray-600 mb-3">
                Already have an account?
            </p>
            <a href="{{ route('login') }}" 
               class="block w-full text-center py-3 bg-white border-2 border-[#667f5f] text-[#667f5f] hover:bg-[#667f5f] hover:text-white font-semibold rounded-lg transition-colors">
                Sign In
            </a>
        </div>
    </form>
</x-guest-layout>

