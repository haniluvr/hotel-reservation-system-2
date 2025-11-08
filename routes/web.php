<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

// Homepage
Route::get('/', [HomeController::class, 'index'])->name('home');

// Hotel Search and Details
Route::get('/hotels/search', [HomeController::class, 'search'])->name('hotels.search');
Route::get('/hotels/{id}', [HomeController::class, 'showHotel'])->name('hotels.show');
Route::get('/hotels/{hotelId}/rooms/{roomId}', [HomeController::class, 'showRoom'])->name('rooms.show');

// Search Suggestions API
Route::get('/api/search-suggestions', [HomeController::class, 'searchSuggestions'])->name('api.search-suggestions');

// Dashboard
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Profile Management
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
