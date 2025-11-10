<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Services\XenditPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    private XenditPaymentService $xenditService;

    public function __construct(XenditPaymentService $xenditService)
    {
        $this->xenditService = $xenditService;
    }

    /**
     * Show the payment checkout page
     */
    public function checkout($reservationId)
    {
        $reservation = Reservation::with(['room.hotel', 'payment', 'user'])
            ->where('user_id', Auth::id())
            ->findOrFail($reservationId);

        // Check if already paid
        if ($reservation->payment && $reservation->payment->status === 'paid') {
            return redirect()->route('bookings.show', $reservation->id)
                ->with('info', 'This reservation has already been paid.');
        }

        // Check if reservation is still pending
        if ($reservation->status !== 'pending') {
            return redirect()->route('bookings.show', $reservation->id)
                ->with('info', 'This reservation is no longer pending payment.');
        }

        // Always show the checkout page - don't redirect directly to Xendit
        // The JavaScript will handle opening Xendit in a new window when user clicks Pay
        return view('payments.checkout', compact('reservation'));
    }

    /**
     * Process payment
     */
    public function process(Request $request, $reservationId)
    {
        $reservation = Reservation::with(['room.hotel', 'payment', 'user'])
            ->where('user_id', Auth::id())
            ->findOrFail($reservationId);

        $request->validate([
            'payment_method' => 'required|in:xendit,cash',
        ]);

        try {
            // Check if payment already exists and is still valid
            if ($reservation->payment) {
                // If payment is paid, redirect back
                if ($reservation->payment->status === 'paid') {
                    if ($request->expectsJson() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'This reservation has already been paid.'
                        ], 400);
                    }
                    return redirect()->route('bookings.show', $reservation->id)
                        ->with('info', 'This reservation has already been paid.');
                }
                
                // If payment is pending Xendit and not expired, return existing payment URL
                if ($reservation->payment->status === 'pending' && 
                    $reservation->payment->payment_method === 'xendit' &&
                    $reservation->payment->payment_url &&
                    (!$reservation->payment->expires_at || $reservation->payment->expires_at->isFuture())) {
                    
                    // Return JSON with existing payment URL for JavaScript to open in new window
                    if ($request->expectsJson() || $request->wantsJson()) {
                        return response()->json([
                            'success' => true,
                            'payment_url' => $reservation->payment->payment_url,
                            'existing' => true
                        ]);
                    }
                    
                    // For non-AJAX requests, redirect to checkout page
                    return redirect()->route('payments.checkout', $reservation->id);
                }
                
                // If cash payment exists, allow changing payment method
                if ($reservation->payment->payment_method === 'cash') {
                    // Allow user to proceed to checkout page to potentially change payment method
                }
                
                // If payment is expired or failed, we'll create a new one below
                // (fall through to create new payment)
            }

            // Handle cash payment
            if ($request->payment_method === 'cash') {
                // Create payment record for cash payment
                $payment = $reservation->payment()->create([
                    'xendit_invoice_id' => 'CASH_' . $reservation->reservation_number,
                    'payment_method' => 'cash',
                    'amount' => $reservation->total_amount,
                    'currency' => 'PHP',
                    'status' => 'pending',
                    'payment_url' => null,
                    'payment_details' => ['type' => 'cash', 'note' => 'Payment to be collected at hotel'],
                    'expires_at' => now()->addDays(30), // Cash payments have longer expiry
                ]);

                return redirect()->route('payments.confirmation', $reservation->id);
            }

            // Handle Xendit payment
            // Prepare payment data for Xendit
            $customerData = [
                'given_names' => $reservation->user->name,
                'email' => $reservation->user->email,
            ];
            
            // Include phone number if available
            if ($reservation->user->phone) {
                $customerData['mobile_number'] = $reservation->user->phone;
            }
            
            $paymentData = [
                'external_id' => $reservation->reservation_number,
                'amount' => $reservation->total_amount,
                'description' => "Hotel Reservation - Belmont Hotel - {$reservation->room->room_type}",
                'customer' => $customerData,
                'currency' => 'PHP',
                'items' => [
                    [
                        'name' => "{$reservation->room->room_type} - Belmont Hotel",
                        'quantity' => 1,
                        'price' => $reservation->total_amount,
                    ]
                ],
                'success_url' => route('payments.success', $reservation->id),
                'failure_url' => route('payments.failure', $reservation->id),
            ];

            // Create invoice with Xendit
            $invoiceResult = $this->xenditService->createInvoice($paymentData);

            if (!$invoiceResult['success']) {
                throw new \Exception($invoiceResult['error'] ?? 'Failed to create payment invoice');
            }

            $invoice = $invoiceResult['invoice'];

            // Create payment record
            $payment = $reservation->payment()->create([
                'xendit_invoice_id' => $invoice['id'] ?? $invoice['external_id'],
                'payment_method' => 'xendit',
                'amount' => $reservation->total_amount,
                'currency' => 'PHP',
                'status' => 'pending',
                'payment_url' => $invoiceResult['payment_url'] ?? $invoice['invoice_url'] ?? null,
                'payment_details' => $invoice,
                'expires_at' => isset($invoice['expiry_date']) ? \Carbon\Carbon::parse($invoice['expiry_date']) : now()->addHours(24),
            ]);

            // For Xendit, return JSON with payment URL to open in new window
            if ($payment->payment_url) {
                if ($request->expectsJson() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'payment_url' => $payment->payment_url
                    ]);
                }
                // For regular requests, still redirect but we'll handle it with JS
                return response()->json([
                    'success' => true,
                    'payment_url' => $payment->payment_url
                ])->header('Content-Type', 'application/json');
            }

            // Fallback: redirect to booking page with payment info
            return redirect()->route('bookings.show', $reservation->id)
                ->with('info', 'Payment invoice created. Please check your email for payment instructions.');

        } catch (\Exception $e) {
            Log::error('Payment processing failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An error occurred while processing your payment. Please try again.']);
        }
    }

    /**
     * Handle successful payment redirect
     */
    public function success($reservationId)
    {
        $reservation = Reservation::with(['room.hotel', 'payment'])
            ->where('user_id', Auth::id())
            ->findOrFail($reservationId);

        // Check and update payment status from Xendit if needed
        if ($reservation->payment && $reservation->payment->payment_method === 'xendit' && $reservation->payment->status === 'pending') {
            try {
                // Get latest invoice status from Xendit
                $invoiceResult = $this->xenditService->getInvoice($reservation->payment->xendit_invoice_id);
                
                if ($invoiceResult['success'] && isset($invoiceResult['invoice']['status']) && $invoiceResult['invoice']['status'] === 'PAID') {
                    $invoice = $invoiceResult['invoice'];
                    
                    // Update payment status
                    $reservation->payment->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                        'payment_details' => array_merge($reservation->payment->payment_details ?? [], $invoice),
                    ]);

                    // Update reservation status
                    if ($reservation->status === 'pending') {
                        $reservation->update([
                            'status' => 'confirmed',
                            'confirmed_at' => now(),
                        ]);
                    }
                    
                    // Refresh relationships
                    $reservation->load('payment');
                }
            } catch (\Exception $e) {
                Log::warning('Failed to sync payment status from Xendit: ' . $e->getMessage());
            }
        }

        // Return a simple view that closes the window
        return view('payments.success-callback');
    }

    /**
     * Handle failed payment redirect
     */
    public function failure($reservationId)
    {
        $reservation = Reservation::with(['room.hotel', 'payment'])
            ->where('user_id', Auth::id())
            ->findOrFail($reservationId);

        return redirect()->route('payments.checkout', $reservation->id)
            ->withErrors(['error' => 'Payment failed. Please try again or use a different payment method.']);
    }

    /**
     * Show confirmation page for payments
     */
    public function confirmation($reservationId)
    {
        $reservation = Reservation::with(['room.hotel', 'payment', 'user'])
            ->where('user_id', Auth::id())
            ->findOrFail($reservationId);

        // Refresh payment status from database
        $reservation->load('payment');
        
        if (!$reservation->payment) {
            return redirect()->route('bookings.show', $reservation->id);
        }

        return view('payments.confirmation', compact('reservation'));
    }

    /**
     * Check payment status (API endpoint)
     */
    public function checkStatus($reservationId)
    {
        $reservation = Reservation::with(['payment'])
            ->where('user_id', Auth::id())
            ->findOrFail($reservationId);

        Log::info("Checking payment status for reservation {$reservationId}", [
            'payment_method' => $reservation->payment?->payment_method,
            'payment_status' => $reservation->payment?->status,
            'xendit_invoice_id' => $reservation->payment?->xendit_invoice_id,
        ]);

        // If payment is pending and using Xendit, check status from Xendit
        if ($reservation->payment && 
            $reservation->payment->payment_method === 'xendit' && 
            $reservation->payment->status === 'pending') {
            try {
                $invoiceResult = $this->xenditService->getInvoice($reservation->payment->xendit_invoice_id);
                
                Log::info("Xendit invoice status check result", [
                    'success' => $invoiceResult['success'],
                    'status' => $invoiceResult['invoice']['status'] ?? 'unknown',
                ]);
                
                if ($invoiceResult['success'] && isset($invoiceResult['invoice']['status']) && $invoiceResult['invoice']['status'] === 'PAID') {
                    $invoice = $invoiceResult['invoice'];
                    
                    // Update payment status
                    $reservation->payment->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                        'payment_details' => array_merge($reservation->payment->payment_details ?? [], $invoice),
                    ]);

                    // Update reservation status
                    if ($reservation->status === 'pending') {
                        $reservation->update([
                            'status' => 'confirmed',
                            'confirmed_at' => now(),
                        ]);
                    }
                    
                    // Refresh the relationship
                    $reservation->load('payment');
                    
                    Log::info("Payment status updated to paid for reservation {$reservationId}");
                }
            } catch (\Exception $e) {
                Log::error('Failed to check payment status from Xendit: ' . $e->getMessage(), [
                    'reservation_id' => $reservationId,
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        return response()->json([
            'status' => $reservation->payment ? $reservation->payment->status : 'none',
            'payment_method' => $reservation->payment ? $reservation->payment->payment_method : null,
            'reservation_status' => $reservation->status,
        ]);
    }
}

