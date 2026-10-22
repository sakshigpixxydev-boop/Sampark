<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ChangePinController extends Controller
{
    /**
     * Show the change PIN form
     */
    public function show()
    {
        return view('profile.change-pin');
    }

    /**
     * Update the user's PIN
     */
    public function update(Request $request)
    {
        $request->validate([
            'current_pin' => 'required|digits:4',
            'pin' => 'required|digits:4|confirmed',
        ]);

        $user = Auth::user();

        // Verify current PIN
        if (!$user->checkPin($request->current_pin)) {
            throw ValidationException::withMessages([
                'current_pin' => ['The current PIN is incorrect.'],
            ]);
        }

        // Update PIN
        $user->pin = $request->pin;
        $user->save();

        return redirect()->route('profile.show')->with('success', 'PIN changed successfully!');
    }
}
