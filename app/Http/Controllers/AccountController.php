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
            ->with(['room.hotel'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('account.index', compact('user', 'bookings'));
    }

    /**
     * Update personal information
     */
    public function updatePersonalInfo(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user->update($validated);

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
     * Archive account (move to archived_users table)
     */
    public function archiveAccount(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'confirm' => ['required', 'accepted'],
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
                ->with('success', 'Your account has been archived. You can now use the same email to create a new account.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to archive account. Please try again.']);
        }
    }
}
