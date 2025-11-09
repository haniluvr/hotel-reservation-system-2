<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Payment;
use App\Models\Reservation;
use App\Mail\PaymentReceipt;

/**
 * XenditPaymentService - Payment Processing with Xendit API
 * 
 * Handles payment creation, webhook processing, and payment status updates.
 * Implements secure webhook signature verification.
 */
class XenditPaymentService
{
    private string $secretKey;
    private string $webhookToken;
    private string $baseUrl;

    public function __construct()
    {
        $this->secretKey = config('services.xendit.api_key') ?? 'demo_key';
        $this->webhookToken = config('services.xendit.webhook_token') ?? 'demo_token';
        $this->baseUrl = config('services.xendit.base_url', 'https://api.xendit.co');
    }

    /**
     * Create a payment invoice
     */
    public function createInvoice(array $paymentData): array
    {
        try {
            // Build customer data - only include mobile_number if it's provided and not null
            $customerData = [
                'given_names' => $paymentData['customer']['given_names'] ?? 'Guest',
                'email' => $paymentData['customer']['email'] ?? 'guest@example.com',
            ];
            
            // Only include mobile_number if it exists and is not null
            if (isset($paymentData['customer']['mobile_number']) && $paymentData['customer']['mobile_number'] !== null) {
                $customerData['mobile_number'] = (string) $paymentData['customer']['mobile_number'];
            }
            
            $invoiceData = [
                'external_id' => $paymentData['external_id'] ?? uniqid('BEL_'),
                'amount' => $paymentData['amount'],
                'description' => $paymentData['description'] ?? 'Hotel Reservation Payment',
                'invoice_duration' => 86400, // 24 hours
                'customer' => $customerData,
                'customer_notification_preference' => [
                    'invoice_created' => ['email'],
                    'invoice_reminder' => ['email'],
                    'invoice_paid' => ['email'],
                ],
                'success_redirect_url' => $paymentData['success_url'] ?? config('app.url') . '/payment/success',
                'failure_redirect_url' => $paymentData['failure_url'] ?? config('app.url') . '/payment/failure',
                'currency' => $paymentData['currency'] ?? 'PHP',
                'items' => $paymentData['items'] ?? [],
                'fees' => $paymentData['fees'] ?? [],
            ];

            $response = $this->makeApiRequest('/v2/invoices', $invoiceData);

            return [
                'success' => true,
                'invoice' => $response,
                'payment_url' => $response['invoice_url'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('Xendit invoice creation failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get invoice details
     */
    public function getInvoice(string $invoiceId): array
    {
        try {
            $response = $this->makeApiRequest("/v2/invoices/{$invoiceId}", [], 'GET');

            return [
                'success' => true,
                'invoice' => $response,
            ];

        } catch (\Exception $e) {
            Log::error("Xendit invoice retrieval failed for {$invoiceId}: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Expire an invoice
     */
    public function expireInvoice(string $invoiceId): array
    {
        try {
            $response = $this->makeApiRequest("/v2/invoices/{$invoiceId}/expire", [], 'POST');

            return [
                'success' => true,
                'invoice' => $response,
            ];

        } catch (\Exception $e) {
            Log::error("Xendit invoice expiration failed for {$invoiceId}: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process webhook notification
     */
    public function processWebhook(array $payload, string $signature): array
    {
        try {
            // Verify webhook signature
            if (!$this->verifyWebhookSignature($payload, $signature)) {
                throw new \Exception('Invalid webhook signature');
            }

            $event = $payload['event'] ?? null;
            $invoiceId = $payload['data']['id'] ?? null;

            if (!$event || !$invoiceId) {
                throw new \Exception('Invalid webhook payload');
            }

            // Find payment record
            $payment = Payment::where('xendit_invoice_id', $invoiceId)->first();
            
            if (!$payment) {
                Log::warning("Payment not found for invoice ID: {$invoiceId}");
                return ['success' => false, 'error' => 'Payment not found'];
            }

            // Process based on event type
            switch ($event) {
                case 'invoice.paid':
                    return $this->handleInvoicePaid($payment, $payload);
                
                case 'invoice.expired':
                    return $this->handleInvoiceExpired($payment, $payload);
                
                case 'invoice.voided':
                    return $this->handleInvoiceVoided($payment, $payload);
                
                default:
                    Log::info("Unhandled webhook event: {$event}");
                    return ['success' => true, 'message' => 'Event not handled'];
            }

        } catch (\Exception $e) {
            Log::error('Webhook processing failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create virtual account payment
     */
    public function createVirtualAccount(array $paymentData): array
    {
        try {
            $vaData = [
                'external_id' => $paymentData['external_id'] ?? uniqid('BEL_VA_'),
                'bank_code' => $paymentData['bank_code'] ?? 'BCA',
                'name' => $paymentData['name'] ?? 'Hotel Reservation',
                'virtual_account_number' => $paymentData['virtual_account_number'] ?? null,
                'suggested_amount' => $paymentData['amount'],
                'is_closed' => true,
                'is_single_use' => true,
                'currency' => $paymentData['currency'] ?? 'PHP',
                'expiration_date' => $paymentData['expiration_date'] ?? now()->addDays(1)->toISOString(),
            ];

            $response = $this->makeApiRequest('/virtual_accounts', $vaData);

            return [
                'success' => true,
                'virtual_account' => $response,
            ];

        } catch (\Exception $e) {
            Log::error('Xendit virtual account creation failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create e-wallet payment (GCash, PayMaya, etc.)
     */
    public function createEwalletPayment(array $paymentData): array
    {
        try {
            $ewalletData = [
                'reference_id' => $paymentData['reference_id'] ?? uniqid('BEL_EW_'),
                'currency' => $paymentData['currency'] ?? 'PHP',
                'amount' => $paymentData['amount'],
                'checkout_method' => 'ONE_TIME_PAYMENT',
                'channel_code' => $paymentData['channel_code'] ?? 'GCASH',
                'channel_properties' => [
                    'success_redirect_url' => $paymentData['success_url'] ?? config('app.url') . '/payment/success',
                    'failure_redirect_url' => $paymentData['failure_url'] ?? config('app.url') . '/payment/failure',
                ],
                'basket' => $paymentData['basket'] ?? [],
                'metadata' => $paymentData['metadata'] ?? [],
            ];

            $response = $this->makeApiRequest('/ewallets', $ewalletData);

            return [
                'success' => true,
                'ewallet' => $response,
            ];

        } catch (\Exception $e) {
            Log::error('Xendit e-wallet payment creation failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get payment methods available
     */
    public function getPaymentMethods(): array
    {
        try {
            $response = $this->makeApiRequest('/payment_methods', [], 'GET');

            return [
                'success' => true,
                'payment_methods' => $response,
            ];

        } catch (\Exception $e) {
            Log::error('Xendit payment methods retrieval failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Refund a payment
     */
    public function createRefund(string $paymentId, float $amount, string $reason = ''): array
    {
        try {
            $refundData = [
                'payment_id' => $paymentId,
                'amount' => $amount,
                'reason' => $reason,
            ];

            $response = $this->makeApiRequest('/refunds', $refundData);

            return [
                'success' => true,
                'refund' => $response,
            ];

        } catch (\Exception $e) {
            Log::error('Xendit refund creation failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // Private helper methods

    private function makeApiRequest(string $endpoint, array $data = [], string $method = 'POST'): array
    {
        $url = $this->baseUrl . $endpoint;
        
        $headers = [
            'Authorization' => 'Basic ' . base64_encode($this->secretKey . ':'),
            'Content-Type' => 'application/json',
        ];

        // For development, disable SSL verification if certificate bundle is not available
        $httpClient = Http::withHeaders($headers)
            ->timeout(30);
        
        // In development, allow SSL verification to be disabled
        if (config('app.env') === 'local' || config('app.env') === 'development') {
            $httpClient = $httpClient->withoutVerifying();
        }

        if ($method === 'GET') {
            $response = $httpClient->get($url, $data);
        } else {
            $response = $httpClient->$method($url, $data);
        }

        if (!$response->successful()) {
            $errorBody = $response->body();
            $statusCode = $response->status();
            throw new \Exception("Xendit API request failed (Status: {$statusCode}): " . $errorBody);
        }

        return $response->json();
    }

    private function verifyWebhookSignature(array $payload, string $signature): bool
    {
        // Xendit uses callback token in header, not HMAC signature
        // For production, verify the callback token matches
        if (config('app.env') === 'production') {
            return hash_equals($this->webhookToken, $signature);
        }
        
        // In development/testing, allow if token is set
        return !empty($this->webhookToken) && !empty($signature);
    }

    private function handleInvoicePaid(Payment $payment, array $payload): array
    {
        try {
            // Update payment status
            $payment->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_details' => $payload['data'],
            ]);

            // Update reservation status
            $reservation = $payment->reservation;
            if ($reservation) {
                $reservation->update([
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                ]);

                // Send payment receipt email
                try {
                    Mail::to($reservation->user->email)->send(new PaymentReceipt($payment->fresh()));
                } catch (\Exception $e) {
                    Log::warning('Failed to send payment receipt email: ' . $e->getMessage());
                }
            }

            Log::info("Payment {$payment->id} marked as paid");

            return [
                'success' => true,
                'message' => 'Payment marked as paid',
                'payment_id' => $payment->id,
            ];

        } catch (\Exception $e) {
            Log::error("Failed to handle invoice paid for payment {$payment->id}: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function handleInvoiceExpired(Payment $payment, array $payload): array
    {
        try {
            // Update payment status
            $payment->update([
                'status' => 'expired',
                'payment_details' => $payload['data'],
            ]);

            // Cancel reservation if still pending
            $reservation = $payment->reservation;
            if ($reservation && $reservation->status === 'pending') {
                $reservation->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancellation_reason' => 'Payment expired',
                ]);

                // Restore room inventory
                $this->restoreRoomInventory($reservation->room_id, 1, 'payment_expired');
            }

            Log::info("Payment {$payment->id} marked as expired");

            return [
                'success' => true,
                'message' => 'Payment marked as expired',
                'payment_id' => $payment->id,
            ];

        } catch (\Exception $e) {
            Log::error("Failed to handle invoice expired for payment {$payment->id}: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function handleInvoiceVoided(Payment $payment, array $payload): array
    {
        try {
            // Update payment status
            $payment->update([
                'status' => 'cancelled',
                'payment_details' => $payload['data'],
            ]);

            Log::info("Payment {$payment->id} marked as voided");

            return [
                'success' => true,
                'message' => 'Payment marked as voided',
                'payment_id' => $payment->id,
            ];

        } catch (\Exception $e) {
            Log::error("Failed to handle invoice voided for payment {$payment->id}: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function restoreRoomInventory(int $roomId, int $quantity, string $reason): void
    {
        try {
            \DB::table('rooms')
                ->where('id', $roomId)
                ->increment('available_quantity', $quantity);

            Log::info("Restored {$quantity} room(s) for room {$roomId} due to: {$reason}");
        } catch (\Exception $e) {
            Log::error("Failed to restore room inventory for room {$roomId}: " . $e->getMessage());
        }
    }
}
