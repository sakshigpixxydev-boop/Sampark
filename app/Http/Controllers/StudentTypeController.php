<?php

namespace App\Http\Controllers;

use App\Models\StudentType;
use Illuminate\Http\Request;

class StudentTypeController extends Controller
{
    public function index()
    {
        $studentTypes = StudentType::orderBy('studenttypename')->paginate(15);
        
        return view('student-types.index', compact('studentTypes'));
    }

    public function create()
    {
        return view('student-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'studenttypename' => 'required|string|max:255',
        ]);

        StudentType::create($validated);

        return redirect()->route('student-types.index')->with('success', 'Student type created successfully.');
    }

    public function edit(StudentType $studentType)
    {
        return view('student-types.edit', compact('studentType'));
    }

    public function update(Request $request, StudentType $studentType)
    {
        $validated = $request->validate([
            'studenttypename' => 'required|string|max:255',
        ]);

        $studentType->update($validated);

        return redirect()->route('student-types.index')->with('success', 'Student type updated successfully.');
    }

    public function destroy(StudentType $studentType)
    {
        // Check if student type is in use
        if ($studentType->students()->count() > 0) {
            return redirect()->route('student-types.index')
                ->with('error', 'Cannot delete student type that is assigned to students.');
        }

        $studentType->delete();

        return redirect()->route('student-types.index')->with('success', 'Student type deleted successfully.');
    }
}
