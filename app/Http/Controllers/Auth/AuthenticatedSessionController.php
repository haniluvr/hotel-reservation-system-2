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
