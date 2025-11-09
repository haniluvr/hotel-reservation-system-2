<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Illuminate\Http\Request;

class PromoCodeController extends Controller
{
    /**
     * Validate a promo code
     */
    public function validate(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        $code = strtoupper(trim($request->code));
        $amount = floatval($request->amount);

        $promoCode = PromoCode::where('code', $code)
            ->available()
            ->first();

        if (!$promoCode) {
            return response()->json([
                'success' => false,
                'message' => 'Promo code not found or expired',
                'discount' => 0,
            ]);
        }

        if (!$promoCode->canBeUsedFor($amount)) {
            return response()->json([
                'success' => false,
                'message' => 'Promo code cannot be used for this amount. Minimum amount: ₱' . number_format($promoCode->minimum_amount, 2),
                'discount' => 0,
            ]);
        }

        $result = $promoCode->applyTo($amount);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['success'] ? 'Promo code applied successfully!' : ($result['message'] ?? 'Invalid promo code'),
            'discount' => $result['discount'] ?? 0,
            'final_total' => $result['final_total'] ?? $amount,
        ]);
    }
}

