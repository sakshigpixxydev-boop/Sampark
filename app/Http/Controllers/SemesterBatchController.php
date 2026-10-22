<?php

namespace App\Http\Controllers;

use App\Models\SemesterBatch;
use App\Models\Standard;
use Illuminate\Http\Request;
use App\Helpers\DateHelper;

class SemesterBatchController extends Controller
{
    public function index()
    {
        $semesterBatches = SemesterBatch::orderBy('semester', 'asc')
            ->orderBy('start_date', 'desc')
            ->paginate(15);
        
        return view('semester-batches.index', compact('semesterBatches'));
    }

    public function create()
    {
        $batches = SemesterBatch::orderBy('start_date', 'desc')->get();
        
        // Fetch courses from Edmingle API
        $edmingleService = new \App\Services\EdmingleService();
        $courses = $edmingleService->getCourses();
        
        return view('semester-batches.create', compact('batches', 'courses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:100',
            'start_date' => 'required|date_format_dmy',
            'end_date' => [
                'required',
                'date_format_dmy',
                function ($attribute, $value, $fail) use ($request) {
                    $start = DateHelper::convertDateFormat($request->start_date);
                    $end = DateHelper::convertDateFormat($value);
                    if ($start && $end && strtotime($end) <= strtotime($start)) {
                        $fail('The ' . $attribute . ' must be a date after start date.');
                    }
                }
            ],
            'capacity' => 'required|integer|min:1',
            'code_prefix' => 'nullable|string|max:10',
            'status' => 'required|integer|in:0,1',
            'semester' => 'required|integer|between:1,14',
            'next_sembatch_id' => 'nullable|exists:sembatches,sembatches_id',
            'edmingle_course_id' => 'nullable|string',
            'edmingle_batch_id' => 'nullable|string',
            'edmingle_course_name' => 'nullable|string',
            'edmingle_batch_name' => 'nullable|string',
        ]);

        $validated['start_date'] = DateHelper::convertDateFormat($validated['start_date']);
        $validated['end_date'] = DateHelper::convertDateFormat($validated['end_date']);

        SemesterBatch::create($validated);

        return redirect()->route('semester-batches.index')->with('success', 'Semester batch created successfully.');
    }

    public function edit(SemesterBatch $semesterBatch)
    {
        $batches = SemesterBatch::where('sembatches_id', '!=', $semesterBatch->sembatches_id)
            ->orderBy('start_date', 'desc')
            ->get();
            
        // Fetch courses from Edmingle API
        $edmingleService = new \App\Services\EdmingleService();
        $courses = $edmingleService->getCourses();
        
        return view('semester-batches.edit', compact('semesterBatch', 'batches', 'courses'));
    }

    public function update(Request $request, SemesterBatch $semesterBatch)
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:100',
            'start_date' => 'required|date_format_dmy',
            'end_date' => [
                'required',
                'date_format_dmy',
                function ($attribute, $value, $fail) use ($request) {
                    $start = DateHelper::convertDateFormat($request->start_date);
                    $end = DateHelper::convertDateFormat($value);
                    if ($start && $end && strtotime($end) <= strtotime($start)) {
                        $fail('The ' . $attribute . ' must be a date after start date.');
                    }
                }
            ],
            'capacity' => 'required|integer|min:1',
            'code_prefix' => 'nullable|string|max:10',
            'status' => 'required|integer|in:0,1',
            'semester' => 'required|integer|between:1,14',
            'next_sembatch_id' => 'nullable|exists:sembatches,sembatches_id',
            'edmingle_course_id' => 'nullable|string',
            'edmingle_batch_id' => 'nullable|string',
            'edmingle_course_name' => 'nullable|string',
            'edmingle_batch_name' => 'nullable|string',
        ]);

        $validated['start_date'] = DateHelper::convertDateFormat($validated['start_date']);
        $validated['end_date'] = DateHelper::convertDateFormat($validated['end_date']);

        $semesterBatch->update($validated);

        return redirect()->route('semester-batches.index')->with('success', 'Semester batch updated successfully.');
    }

    public function destroy(SemesterBatch $semesterBatch)
    {
        // Check if semester batch is in use
        $studentsCount = $semesterBatch->students()->count();
        $uploadBatchesCount = $semesterBatch->uploadBatches()->count();
        
        if ($studentsCount > 0 || $uploadBatchesCount > 0) {
            return redirect()->route('semester-batches.index')
                ->with('error', 'Cannot delete semester batch that has students or upload batches.');
        }

        $semesterBatch->delete();

        return redirect()->route('semester-batches.index')->with('success', 'Semester batch deleted successfully.');
    }
}
