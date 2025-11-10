<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PaymentSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();
        $reservations = Reservation::all();
        
        if ($reservations->isEmpty()) {
            $this->command->warn('Reservations not found. Please run ReservationSeeder first.');
            return;
        }
        
        $paymentMethods = ['credit_card', 'e_wallet', 'bank_transfer', 'debit_card'];
        
        foreach ($reservations as $reservation) {
            // Determine payment status based on reservation status
            $status = 'pending';
            $paidAt = null;
            $expiresAt = null;
            $failureReason = null;
            
            if ($reservation->status === 'cancelled') {
                // Cancelled reservations: failed or expired payments
                $status = $faker->randomElement(['failed', 'expired', 'cancelled']);
                if ($status === 'failed') {
                    $failureReason = $faker->randomElement([
                        'Insufficient funds',
                        'Card declined',
                        'Payment gateway error',
                        'Transaction timeout',
                    ]);
                }
                $expiresAt = $reservation->created_at->copy()->addHours(24);
            } elseif ($reservation->status === 'completed') {
                // Completed reservations: mostly paid
                if (rand(1, 100) <= 90) {
                    $status = 'paid';
                    $paidAt = $reservation->created_at->copy()->addHours(rand(1, 48));
                } else {
                    $status = 'pending';
                }
                $expiresAt = $reservation->created_at->copy()->addHours(24);
            } elseif ($reservation->status === 'confirmed') {
                // Confirmed reservations: mostly paid, some pending
                if (rand(1, 100) <= 80) {
                    $status = 'paid';
                    $paidAt = $reservation->confirmed_at 
                        ? Carbon::parse($reservation->confirmed_at)->copy()->addHours(rand(1, 12))
                        : $reservation->created_at->copy()->addHours(rand(1, 48));
                } else {
                    $status = 'pending';
                }
                $expiresAt = $reservation->created_at->copy()->addHours(24);
            } else {
                // Pending reservations: pending or expired payments
                if (rand(1, 100) <= 70) {
                    $status = 'pending';
                } else {
                    $status = 'expired';
                }
                $expiresAt = $reservation->created_at->copy()->addHours(24);
            }
            
            // Generate Xendit invoice ID
            $xenditInvoiceId = 'xnd_' . strtolower($faker->bothify('??#########'));
            
            // Ensure unique invoice ID
            while (Payment::where('xendit_invoice_id', $xenditInvoiceId)->exists()) {
                $xenditInvoiceId = 'xnd_' . strtolower($faker->bothify('??#########'));
            }
            
            // Payment details based on method
            $paymentMethod = $faker->randomElement($paymentMethods);
            $paymentDetails = null;
            
            if ($paymentMethod === 'credit_card' || $paymentMethod === 'debit_card') {
                $paymentDetails = [
                    'card_type' => $faker->randomElement(['Visa', 'Mastercard', 'Amex']),
                    'last_four' => $faker->numerify('####'),
                    'cardholder_name' => $faker->name(),
                ];
            } elseif ($paymentMethod === 'e_wallet') {
                $paymentDetails = [
                    'wallet_type' => $faker->randomElement(['GCash', 'PayMaya', 'GrabPay']),
                    'account_number' => $faker->numerify('####-####-####'),
                ];
            } elseif ($paymentMethod === 'bank_transfer') {
                $paymentDetails = [
                    'bank_name' => $faker->randomElement(['BDO', 'BPI', 'Metrobank', 'Security Bank']),
                    'account_number' => $faker->numerify('####-####-####'),
                    'reference_number' => $faker->bothify('??#########'),
                ];
            }
            
            Payment::create([
                'reservation_id' => $reservation->id,
                'xendit_invoice_id' => $xenditInvoiceId,
                'payment_method' => $paymentMethod,
                'amount' => $reservation->total_amount,
                'currency' => 'PHP',
                'status' => $status,
                'payment_url' => $status === 'pending' ? 'https://checkout.xendit.co/web/' . $xenditInvoiceId : null,
                'payment_details' => $paymentDetails,
                'paid_at' => $paidAt?->format('Y-m-d H:i:s'),
                'expires_at' => $expiresAt?->format('Y-m-d H:i:s'),
                'failure_reason' => $failureReason,
                'created_at' => $reservation->created_at,
                'updated_at' => $paidAt ? $paidAt : $reservation->updated_at,
            ]);
        }
    }
}
