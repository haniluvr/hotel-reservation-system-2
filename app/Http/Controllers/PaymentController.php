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
        $this->middleware('auth');
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
            'payment_method' => 'required|in:credit_card,debit_card,e_wallet,bank_transfer',
        ]);

        try {
            // Check if payment already exists
            if ($reservation->payment) {
                return redirect()->route('bookings.show', $reservation->id)
                    ->with('info', 'Payment already exists for this reservation.');
            }

            // Prepare payment data for Xendit
            $paymentData = [
                'external_id' => $reservation->reservation_number,
                'amount' => $reservation->total_amount,
                'description' => "Hotel Reservation - {$reservation->room->hotel->name} - {$reservation->room->room_type}",
                'customer' => [
                    'given_names' => $reservation->user->name,
                    'email' => $reservation->user->email,
                ],
                'currency' => 'PHP',
                'items' => [
                    [
                        'name' => "{$reservation->room->room_type} - {$reservation->room->hotel->name}",
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
                'payment_method' => $request->payment_method,
                'amount' => $reservation->total_amount,
                'currency' => 'PHP',
                'status' => 'pending',
                'payment_url' => $invoiceResult['payment_url'] ?? $invoice['invoice_url'] ?? null,
                'payment_details' => $invoice,
                'expires_at' => isset($invoice['expiry_date']) ? \Carbon\Carbon::parse($invoice['expiry_date']) : now()->addHours(24),
            ]);

            // Redirect to Xendit payment page
            if ($payment->payment_url) {
                return redirect($payment->payment_url);
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

        // Check payment status
        if ($reservation->payment && $reservation->payment->status === 'paid') {
            return redirect()->route('bookings.show', $reservation->id)
                ->with('success', 'Payment successful! Your reservation is now confirmed.');
        }

        return redirect()->route('bookings.show', $reservation->id)
            ->with('info', 'Payment is being processed. You will receive a confirmation email once it is confirmed.');
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
}

