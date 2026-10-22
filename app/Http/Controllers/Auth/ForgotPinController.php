<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SysUser;
use App\Notifications\ResetPinNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ForgotPinController extends Controller
{
    /**
     * Show the forgot PIN form
     */
    public function showLinkRequestForm()
    {
        return view('auth.pin.email');
    }

    /**
     * Send PIN reset link
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = SysUser::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['We could not find a user with that email address.'],
            ]);
        }

        // Generate token
        $token = Str::random(64);

        // Store token in database (reusing password_reset_tokens table with a prefix)
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => 'pin_' . $request->email],
            [
                'email' => 'pin_' . $request->email,
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // Send email notification
        $user->notify(new ResetPinNotification($token));

        return back()->with('status', 'We have emailed your PIN reset link!');
    }

    /**
     * Show the reset PIN form
     */
    public function showResetForm(Request $request, $token)
    {
        return view('auth.pin.reset', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Reset the PIN
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'pin' => 'required|digits:4|confirmed',
        ]);

        // Verify token
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', 'pin_' . $request->email)
            ->first();

        if (!$resetRecord || !Hash::check($request->token, $resetRecord->token)) {
            throw ValidationException::withMessages([
                'email' => ['This PIN reset token is invalid.'],
            ]);
        }

        // Check if token is expired (60 minutes)
        if (now()->diffInMinutes($resetRecord->created_at) > 60) {
            throw ValidationException::withMessages([
                'email' => ['This PIN reset token has expired.'],
            ]);
        }

        // Update PIN
        $user = SysUser::where('email', $request->email)->first();
        $user->pin = $request->pin;
        $user->save();

        // Delete token
        DB::table('password_reset_tokens')->where('email', 'pin_' . $request->email)->delete();

        return redirect()->route('login')->with('status', 'Your PIN has been reset!');
    }
}
