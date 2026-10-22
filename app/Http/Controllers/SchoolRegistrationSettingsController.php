<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SchoolRegistrationSettingsController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $school = $user->getAssignedSchool();

        if (!$school) {
            return redirect()->route('school.dashboard')->with('error', 'No school assigned to your account.');
        }

        return view('school.settings', compact('school'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $school = $user->getAssignedSchool();

        if (!$school) {
            return redirect()->route('school.dashboard')->with('error', 'No school assigned to your account.');
        }

        $validated = $request->validate([
            'reg_form_name' => 'nullable|string|max:255',
            'reg_start_date' => 'nullable|date',
            'reg_end_date' => 'nullable|date|after_or_equal:reg_start_date',
            'notes' => 'nullable|string',
            'offline_payment_name' => 'nullable|string|max:255',
            'offline_payment_contact' => 'nullable|string|max:255',
            'qr_code' => 'nullable|image|max:2048',
            'custom_fees' => 'nullable|array',
            'custom_fees.*.name' => 'required_with:custom_fees|string|max:255',
            'custom_fees.*.amount' => 'required_with:custom_fees|numeric|min:0',
            'services_features' => 'nullable|array',
            'services_features.*' => 'required_with:services_features|string|max:255',
        ]);

        // Clean up empty elements from custom fees and services features
        $customFees = [];
        if ($request->has('custom_fees')) {
            foreach ($validated['custom_fees'] as $fee) {
                if (!empty($fee['name']) && isset($fee['amount'])) {
                    $customFees[] = [
                        'name' => $fee['name'],
                        'amount' => floatval($fee['amount'])
                    ];
                }
            }
        }

        $servicesFeatures = [];
        if ($request->has('services_features')) {
            foreach ($validated['services_features'] as $feature) {
                if (!empty($feature)) {
                    $servicesFeatures[] = $feature;
                }
            }
        }

        $updateData = [
            'reg_form_name' => $validated['reg_form_name'],
            'reg_start_date' => $validated['reg_start_date'],
            'reg_end_date' => $validated['reg_end_date'],
            'notes' => $validated['notes'],
            'offline_payment_name' => $validated['offline_payment_name'],
            'offline_payment_contact' => $validated['offline_payment_contact'],
            'custom_fees' => $customFees,
            'services_features' => $servicesFeatures,
        ];

        if ($request->hasFile('qr_code')) {
            // Delete old QR code if exists
            if ($school->qr_code_path) {
                Storage::disk('public')->delete($school->qr_code_path);
            }
            $path = $request->file('qr_code')->store('qr_codes', 'public');
            $updateData['qr_code_path'] = $path;
        }

        $school->update($updateData);

        return redirect()->route('school.registration-settings.edit')->with('success', 'Registration settings updated successfully.');
    }

    public function generateToken()
    {
        $user = Auth::user();
        $school = $user->getAssignedSchool();

        if (!$school) {
            return redirect()->route('school.dashboard')->with('error', 'No school assigned to your account.');
        }

        do {
            $token = Str::random(40);
        } while (School::where('reg_token', $token)->exists());

        $school->update([
            'reg_token' => $token
        ]);

        return redirect()->route('school.registration-settings.edit')->with('success', 'New registration token generated successfully.');
    }
}
