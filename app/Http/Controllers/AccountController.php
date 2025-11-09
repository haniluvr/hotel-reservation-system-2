<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    /**
     * Display the account page with user info and bookings
     */
    public function index()
    {
        $user = Auth::user();
        
        $bookings = $user->reservations()
            ->with(['room.hotel', 'payment'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Check and update payment status for pending Xendit payments
        $xenditService = app(\App\Services\XenditPaymentService::class);
        foreach ($bookings as $booking) {
            if ($booking->payment && 
                $booking->payment->payment_method === 'xendit' && 
                $booking->payment->status === 'pending') {
                try {
                    $invoiceResult = $xenditService->getInvoice($booking->payment->xendit_invoice_id);
                    
                    if ($invoiceResult['success'] && isset($invoiceResult['invoice']['status']) && $invoiceResult['invoice']['status'] === 'PAID') {
                        $invoice = $invoiceResult['invoice'];
                        
                        // Update payment status
                        $booking->payment->update([
                            'status' => 'paid',
                            'paid_at' => now(),
                            'payment_details' => array_merge($booking->payment->payment_details ?? [], $invoice),
                        ]);

                        // Update reservation status
                        if ($booking->status === 'pending') {
                            $booking->update([
                                'status' => 'confirmed',
                                'confirmed_at' => now(),
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning('Failed to check payment status from Xendit: ' . $e->getMessage());
                }
            }
        }

        return view('account.index', compact('user', 'bookings'));
    }

    /**
     * Update personal information
     */
    public function updatePersonalInfo(Request $request)
    {
        $user = Auth::user();

        // Check if any changes were made
        $hasChanges = $user->name !== $request->input('name') || 
                     $user->email !== $request->input('email') || 
                     ($user->phone ?? '') !== ($request->input('phone') ?? '');

        // Validate password only if changes are detected
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
        ];

        if ($hasChanges) {
            $rules['password'] = ['required', 'string'];
        }

        $validated = $request->validate($rules);

        // If changes were made, verify password
        if ($hasChanges) {
            if (!Hash::check($validated['password'], $user->password)) {
                return back()->withErrors(['password' => 'The password is incorrect.']);
            }
        }

        // Update user information
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ]);

        return redirect()->route('account.index')
            ->with('success', 'Personal information updated successfully.');
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'old_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // Verify old password
        if (!Hash::check($validated['old_password'], $user->password)) {
            return back()->withErrors(['old_password' => 'The current password is incorrect.']);
        }

        // Update password
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('account.index')
            ->with('success', 'Password changed successfully.');
    }

    /**
     * Delete account (move to archived_users table)
     */
    public function archiveAccount(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'confirm' => ['required', 'accepted'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::beginTransaction();
        try {
            // Copy user data to archived_users
            DB::table('archived_users')->insert([
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'email_verified_at' => $user->email_verified_at,
                'password' => $user->password,
                'remember_token' => $user->remember_token,
                'archived_at' => now(),
                'reason' => $request->input('reason'),
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]);

            // Delete user from users table
            $user->delete();

            DB::commit();

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('home')
                ->with('success', 'Your account has been deleted. You can now use the same email to create a new account.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete account. Please try again.']);
        }
    }
}
