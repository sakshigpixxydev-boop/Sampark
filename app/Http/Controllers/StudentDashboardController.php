<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Document;
use App\Models\Student;

class StudentDashboardController extends Controller
{
    public function index()
    {
        return view('student.dashboard');
    }

    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $user = auth()->user();
        $student = $user->students()->first();
        if (!$student) {
            return back()->with('error', 'Student record not found.');
        }

        if ($request->hasFile('photo')) {
            $fileName = time() . '_' . $student->student_id . '_photo.' . $request->file('photo')->extension();
            $request->file('photo')->storeAs('user-documents/photo', $fileName, 'public');
            $filePath = 'storage/user-documents/photo/' . $fileName;

            Document::updateOrCreate(
                ['student_id' => $student->student_id],
                ['photo' => $filePath, 'create_at' => now()]
            );

            // If the status is not already Enrolled/Approved/Done, we can set it to Documents Submitted,
            // but if they are already Enrolled/Approved/Done, keep it as is.
            if (!in_array($student->application_status, ['Enrolled', 'Approved', 'Done'])) {
                $student->application_status = 'Documents Submitted';
                $student->save();
            }

            return back()->with('success', 'Photo uploaded successfully.');
        }

        return back()->with('error', 'Please select a photo.');
    }
}
