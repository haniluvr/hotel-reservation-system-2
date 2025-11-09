<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Check if there's a booking intent in session
        if ($request->session()->has('booking_intent')) {
            $bookingIntent = $request->session()->get('booking_intent');
            $request->session()->forget('booking_intent');
            
            // Redirect to booking page with the stored parameters
            return redirect()->route('booking.create', $bookingIntent);
        }

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

        // Otherwise redirect to intended page or home
        return redirect()->intended(route('home', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
