<?php

namespace App\Http\Controllers;

use App\Services\XenditPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    private XenditPaymentService $xenditService;

    public function __construct(XenditPaymentService $xenditService)
    {
        $this->xenditService = $xenditService;
    }

    /**
     * Handle Xendit webhook notifications
     */
    public function handleXenditWebhook(Request $request)
    {
        try {
            // Get the webhook signature from headers
            $signature = $request->header('x-callback-token') ?? $request->header('X-Callback-Token');
            
            if (!$signature) {
                Log::warning('Xendit webhook received without signature');
                return response()->json([
                    'success' => false,
                    'error' => 'Missing webhook signature'
                ], 400);
            }

            // Get the payload
            $payload = $request->all();

            Log::info('Xendit webhook received', [
                'event' => $payload['event'] ?? 'unknown',
                'invoice_id' => $payload['data']['id'] ?? 'unknown'
            ]);

            // Process the webhook
            $result = $this->xenditService->processWebhook($payload, $signature);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Webhook processed successfully'
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => $result['error'] ?? 'Webhook processing failed'
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Xendit webhook processing error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error'
            ], 500);
        }
    }
}

