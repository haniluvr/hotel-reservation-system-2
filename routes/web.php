<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;

// Homepage
Route::get('/', [HomeController::class, 'index'])->name('home');

// Hotel Search and Details
Route::get('/search', [HomeController::class, 'search'])->name('hotels.search');
Route::get('/hotels/{id}', [HomeController::class, 'showHotel'])->name('hotels.show');
Route::get('/accommodations/{slug}', [HomeController::class, 'showRoom'])->name('rooms.show');

// Accommodations
Route::get('/accommodations', [HomeController::class, 'accommodations'])->name('accommodations.index');

// Reviews (public)
Route::get('/hotels/{hotelId}/reviews', [App\Http\Controllers\ReviewController::class, 'index'])->name('reviews.index');

// Search Suggestions API
Route::get('/api/search-suggestions', [HomeController::class, 'searchSuggestions'])->name('api.search-suggestions');

// Promo Code API
Route::get('/api/promo-codes/validate', [App\Http\Controllers\Api\PromoCodeController::class, 'validate'])->name('api.promo-codes.validate');

// Webhook Routes (no auth required, but signature verified)
Route::post('/api/webhooks/xendit', [App\Http\Controllers\WebhookController::class, 'handleXenditWebhook'])->name('webhooks.xendit');

// Reservation API
Route::middleware('auth')->group(function () {
    Route::get('/api/reservations', [App\Http\Controllers\Api\ReservationController::class, 'index'])->name('api.reservations.index');
    Route::post('/api/reservations', [App\Http\Controllers\Api\ReservationController::class, 'store'])->name('api.reservations.store');
    Route::get('/api/reservations/{id}', [App\Http\Controllers\Api\ReservationController::class, 'show'])->name('api.reservations.show');
    Route::put('/api/reservations/{id}', [App\Http\Controllers\Api\ReservationController::class, 'update'])->name('api.reservations.update');
    Route::delete('/api/reservations/{id}', [App\Http\Controllers\Api\ReservationController::class, 'destroy'])->name('api.reservations.destroy');
});

// React Demo
Route::get('/react-demo', function () {
    return view('react-demo');
})->name('react.demo');

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

// Booking Routes
Route::get('/booking/create', [BookingController::class, 'create'])->name('booking.create');
Route::middleware('auth')->group(function () {
    Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
    Route::get('/account', [App\Http\Controllers\AccountController::class, 'index'])->name('account.index');
    Route::post('/account/personal-info', [App\Http\Controllers\AccountController::class, 'updatePersonalInfo'])->name('account.update-personal-info');
    Route::post('/account/change-password', [App\Http\Controllers\AccountController::class, 'changePassword'])->name('account.change-password');
    Route::post('/account/archive', [App\Http\Controllers\AccountController::class, 'archiveAccount'])->name('account.archive');
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{id}', [BookingController::class, 'show'])->name('bookings.show');
    Route::get('/bookings/{id}/edit', [BookingController::class, 'edit'])->name('bookings.edit');
    Route::put('/bookings/{id}', [BookingController::class, 'update'])->name('bookings.update');
    Route::get('/bookings/{id}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/bookings/{id}/cancel', [BookingController::class, 'processCancellation'])->name('bookings.cancel.process');
    
    // Review Routes
    Route::get('/reviews/create/{reservationId}', [App\Http\Controllers\ReviewController::class, 'create'])->name('reviews.create');
    Route::post('/reviews', [App\Http\Controllers\ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/reviews/{id}/edit', [App\Http\Controllers\ReviewController::class, 'edit'])->name('reviews.edit');
    Route::put('/reviews/{id}', [App\Http\Controllers\ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{id}', [App\Http\Controllers\ReviewController::class, 'destroy'])->name('reviews.destroy');
    
    // Payment Routes
    Route::get('/payments/checkout/{reservationId}', [App\Http\Controllers\PaymentController::class, 'checkout'])->name('payments.checkout');
    Route::post('/payments/process/{reservationId}', [App\Http\Controllers\PaymentController::class, 'process'])->name('payments.process');
    Route::get('/payments/success/{reservationId}', [App\Http\Controllers\PaymentController::class, 'success'])->name('payments.success');
    Route::get('/payments/failure/{reservationId}', [App\Http\Controllers\PaymentController::class, 'failure'])->name('payments.failure');
    
    // Invoice Routes
    Route::get('/invoices/{reservationId}', [App\Http\Controllers\InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{reservationId}/download', [App\Http\Controllers\InvoiceController::class, 'download'])->name('invoices.download');
    
    // Image Upload Routes (Admin only - add middleware as needed)
    Route::post('/api/images/upload', [App\Http\Controllers\ImageController::class, 'upload'])->name('images.upload');
    Route::delete('/api/images/delete', [App\Http\Controllers\ImageController::class, 'delete'])->name('images.delete');
});

require __DIR__.'/auth.php';
