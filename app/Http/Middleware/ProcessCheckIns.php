<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\ReservationService;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ProcessCheckIns
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Process today's check-ins only once per day (using cache)
        $cacheKey = 'check_ins_processed_' . now()->toDateString();
        
        if (!Cache::has($cacheKey)) {
            try {
                $reservationService = app(ReservationService::class);
                $reservationService->processTodayCheckIns();
                
                // Cache for 24 hours to prevent multiple runs per day
                Cache::put($cacheKey, true, now()->addDay());
            } catch (\Exception $e) {
                // Log error but don't block the request
                \Log::warning('Failed to process today\'s check-ins: ' . $e->getMessage());
            }
        }

        return $next($request);
    }
}

