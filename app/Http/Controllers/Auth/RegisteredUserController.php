<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Check for return URL from sessionStorage (passed via hidden input or session)
        $returnUrl = $request->input('return_url') ?? $request->session()->get('auth_return_url');
        
        if ($returnUrl && filter_var($returnUrl, FILTER_VALIDATE_URL)) {
            // Validate it's from the same domain
            $parsedUrl = parse_url($returnUrl);
            $currentHost = parse_url(config('app.url'), PHP_URL_HOST);
            
            if ($parsedUrl && isset($parsedUrl['host']) && $parsedUrl['host'] === $currentHost) {
                $request->session()->forget('auth_return_url');
                return redirect($returnUrl);
            }
        }

        return redirect(route('home', absolute: false));
    }
}
