<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    /**
     * Show the profile page
     */
    public function show()
    {
        $user = Auth::user();
        $student = null;

        // If user is a student, get their student record
        if ($user->role && strtolower($user->role->rolename) === 'student') {
            $student = $user->students()->first();
            if (!$student) {
                $student = Student::where('email', $user->email)->first();
            }
        }

        return view('profile.show', compact('user', 'student'));
    }

    /**
     * Show the edit profile page
     */
    public function edit()
    {
        $user = Auth::user();
        $student = null;

        // If user is a student, get their student record
        if ($user->role && strtolower($user->role->rolename) === 'student') {
            $student = $user->students()->first();
            if (!$student) {
                // Fallback: try to find student by email
                $student = Student::where('email', $user->email)->first();
            }
        }

        return view('profile.edit', compact('user', 'student'));
    }

    /**
     * Update the user's profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $student = null;

        // If user is a student, get their student record
        if ($user->role && strtolower($user->role->rolename) === 'student') {
            $student = $user->students()->first();
            if (!$student) {
                // Fallback: try to find student by email
                $student = Student::where('email', $user->email)->first();
            }
        }

        // Base validation for all users
        $validationRules = [
            'fname' => 'required|string|max:100',
            'mname' => 'nullable|string|max:100',
            'lname' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:sysuser,email,' . $user->sysuser_id . ',sysuser_id',
            'phone' => 'nullable|digits:10',
            'whatsapp' => 'nullable|digits:10',
            'address1' => 'nullable|string|max:500',
            'address2' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'pincode' => 'nullable|digits:6',
        ];

        // Add student-specific validation if user is a student
        if ($student) {
            $validationRules = array_merge($validationRules, [
                'achievement_stsang' => 'nullable|string|max:2000',
                'achievement_academic' => 'nullable|string|max:2000',
                'activities' => 'nullable|string|max:2000',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);
        }

        $request->validate($validationRules);

        try {
            DB::beginTransaction();

            // Update sysuser profile
            $user->update([
                'fname' => $request->fname,
                'mname' => $request->mname,
                'lname' => $request->lname,
                'email' => $request->email,
                'phone' => $request->phone,
                'whatsapp' => $request->whatsapp,
                'address1' => $request->address1,
                'address2' => $request->address2,
                'city' => $request->city,
                'pincode' => $request->pincode,
            ]);

            // Update student-specific fields if user is a student
            if ($student) {
                $studentUpdateData = [
                    'achievement_stsang' => $request->achievement_stsang,
                    'achievement_academic' => $request->achievement_academic,
                    'activities' => $request->activities,
                ];

                // Also update student contact info if provided
                if ($request->filled('phone')) {
                    $studentUpdateData['contact_no'] = $request->phone;
                }
                if ($request->filled('whatsapp')) {
                    $studentUpdateData['whatsapp_no'] = $request->whatsapp;
                }
                if ($request->filled('address1')) {
                    $studentUpdateData['address'] = strtoupper($request->address1);
                }
                if ($request->filled('city')) {
                    $studentUpdateData['city_village'] = strtoupper($request->city);
                }
                if ($request->filled('pincode')) {
                    $studentUpdateData['pincode'] = $request->pincode;
                }

                $student->update($studentUpdateData);

                // Update photo if uploaded
                if ($request->hasFile('photo')) {
                    $fileName = time() . '_' . $student->student_id . '_photo.' . $request->file('photo')->extension();
                    $request->file('photo')->storeAs('user-documents/photo', $fileName, 'public');
                    $filePath = 'storage/user-documents/photo/' . $fileName;

                    \App\Models\Document::updateOrCreate(
                        ['student_id' => $student->student_id],
                        ['photo' => $filePath, 'create_at' => now()]
                    );

                    // If the status is not already Enrolled/Approved/Done, we can set it to Documents Submitted
                    if (!in_array($student->application_status, ['Enrolled', 'Approved', 'Done'])) {
                        $student->application_status = 'Documents Submitted';
                        $student->save();
                    }
                }
            }

            DB::commit();

            return redirect()->route('profile.show')->with('success', 'Profile updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Profile Update Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to update profile. Please try again.');
        }
    }
}
