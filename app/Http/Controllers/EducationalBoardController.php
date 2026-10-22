<?php

namespace App\Http\Controllers;

use App\Models\EducationalBoard;
use Illuminate\Http\Request;

class EducationalBoardController extends Controller
{
    public function index()
    {
        $educationalBoards = EducationalBoard::orderBy('boardname')->paginate(15);
        
        return view('educational-boards.index', compact('educationalBoards'));
    }

    public function create()
    {
        return view('educational-boards.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'boardname' => 'required|string|max:255',
        ]);

        EducationalBoard::create($validated);

        return redirect()->route('educational-boards.index')->with('success', 'Educational board created successfully.');
    }

    public function edit(EducationalBoard $educationalBoard)
    {
        return view('educational-boards.edit', compact('educationalBoard'));
    }

    public function update(Request $request, EducationalBoard $educationalBoard)
    {
        $validated = $request->validate([
            'boardname' => 'required|string|max:255',
        ]);

        $educationalBoard->update($validated);

        return redirect()->route('educational-boards.index')->with('success', 'Educational board updated successfully.');
    }

    public function destroy(EducationalBoard $educationalBoard)
    {
        // Check if educational board is in use by schools or students
        $schoolsCount = $educationalBoard->schools()->count();
        $studentsCount = $educationalBoard->students()->count();
        
        if ($schoolsCount > 0 || $studentsCount > 0) {
            return redirect()->route('educational-boards.index')
                ->with('error', 'Cannot delete educational board that is assigned to schools or students.');
        }

        $educationalBoard->delete();

        return redirect()->route('educational-boards.index')->with('success', 'Educational board deleted successfully.');
    }
}
