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
        
        if ($returnUrl) {
            // Handle both absolute and relative URLs
            $isAbsoluteUrl = filter_var($returnUrl, FILTER_VALIDATE_URL);
            
            if ($isAbsoluteUrl) {
                // Validate it's from the same domain
                $parsedUrl = parse_url($returnUrl);
                $appUrl = config('app.url');
                $currentHost = parse_url($appUrl, PHP_URL_HOST);
                
                if ($parsedUrl && isset($parsedUrl['host']) && $parsedUrl['host'] === $currentHost) {
                    $request->session()->forget('auth_return_url');
                    return redirect($returnUrl);
                }
            } else {
                // It's a relative URL, validate it's safe
                $path = parse_url($returnUrl, PHP_URL_PATH) ?? $returnUrl;
                // Only allow paths starting with /, not full URLs with different hosts
                if (strpos($path, '/') === 0 && strpos($path, 'http') === false) {
                    $request->session()->forget('auth_return_url');
                    return redirect($path);
                }
            }
        }

        return redirect(route('home', absolute: false));
    }
}
