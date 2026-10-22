<?php

namespace App\Http\Controllers;

use App\Models\SchoolType;
use Illuminate\Http\Request;

class SchoolTypeController extends Controller
{
    public function index()
    {
        $schoolTypes = SchoolType::orderBy('schooltypename')->paginate(15);
        
        return view('school-types.index', compact('schoolTypes'));
    }

    public function create()
    {
        return view('school-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'schooltypename' => 'required|string|max:255',
        ]);

        SchoolType::create($validated);

        return redirect()->route('school-types.index')->with('success', 'School type created successfully.');
    }

    public function edit(SchoolType $schoolType)
    {
        return view('school-types.edit', compact('schoolType'));
    }

    public function update(Request $request, SchoolType $schoolType)
    {
        $validated = $request->validate([
            'schooltypename' => 'required|string|max:255',
        ]);

        $schoolType->update($validated);

        return redirect()->route('school-types.index')->with('success', 'School type updated successfully.');
    }

    public function destroy(SchoolType $schoolType)
    {
        // Check if school type is in use
        if ($schoolType->schools()->count() > 0) {
            return redirect()->route('school-types.index')
                ->with('error', 'Cannot delete school type that is assigned to schools.');
        }

        $schoolType->delete();

        return redirect()->route('school-types.index')->with('success', 'School type deleted successfully.');
    }
}
