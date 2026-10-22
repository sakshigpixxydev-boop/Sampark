<?php

namespace App\Http\Controllers;

use App\Models\DiscountRule;
use App\Models\School;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Helpers\DateHelper;

class DiscountRuleController extends Controller
{
    public function index()
    {
        $discountRules = DiscountRule::with(['school', 'student'])
            ->orderBy('valid_from', 'desc')
            ->paginate(15);
        
        return view('discount-rules.index', compact('discountRules'));
    }
    
    public function create()
    {
        $schools = School::where('inactive', 0)->where('status', 1)->orderBy('schoolname')->get();
        $students = Student::orderBy('fname')->get();
        
        return view('discount-rules.create', compact('schools', 'students'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'scope' => 'required|in:SCHOOL,STUDENT',
            'discount_type' => 'required|in:FLAT,PERCENT',
            'discount_value' => 'required|numeric|min:0',
            'applies_to' => 'required|in:ALL,TUITION',
            'valid_from' => 'required|date_format_dmy',
            'valid_to' => [
                'nullable',
                'date_format_dmy',
                function ($attribute, $value, $fail) use ($request) {
                    $start = DateHelper::convertDateFormat($request->valid_from);
                    $end = DateHelper::convertDateFormat($value);
                    if ($start && $end && strtotime($end) < strtotime($start)) {
                        $fail('The ' . $attribute . ' must be a date after or equal to valid from.');
                    }
                }
            ],
            'active' => 'boolean',
            'school_id' => 'required_if:scope,SCHOOL|nullable|exists:school,school_id',
            'student_id' => 'required_if:scope,STUDENT|nullable|exists:student,student_id',
        ]);

        $validated['valid_from'] = DateHelper::convertDateFormat($validated['valid_from']);
        if (isset($validated['valid_to'])) {
            $validated['valid_to'] = DateHelper::convertDateFormat($validated['valid_to']);
        }
        
        // Additional validation for PERCENT type
        if ($validated['discount_type'] === 'PERCENT' && $validated['discount_value'] > 100) {
            return back()->withErrors(['discount_value' => 'Percentage discount cannot exceed 100%.'])->withInput();
        }
        
        // Ensure correct scope assignment
        if ($validated['scope'] === 'SCHOOL') {
            $validated['student_id'] = null;
        } else {
            $validated['school_id'] = null;
        }
        
        // Set active to true if not provided
        $validated['active'] = $request->has('active') ? true : false;
        
        DiscountRule::create($validated);
        
        return redirect()->route('discount-rules.index')->with('success', 'Discount rule created successfully.');
    }
    
    public function edit(DiscountRule $discountRule)
    {
        $schools = School::where('inactive', 0)->where('status', 1)->orderBy('schoolname')->get();
        $students = Student::orderBy('fname')->get();
        
        return view('discount-rules.edit', compact('discountRule', 'schools', 'students'));
    }
    
    public function update(Request $request, DiscountRule $discountRule)
    {
        $validated = $request->validate([
            'scope' => 'required|in:SCHOOL,STUDENT',
            'discount_type' => 'required|in:FLAT,PERCENT',
            'discount_value' => 'required|numeric|min:0',
            'applies_to' => 'required|in:ALL,TUITION',
            'valid_from' => 'required|date_format_dmy',
            'valid_to' => [
                'nullable',
                'date_format_dmy',
                function ($attribute, $value, $fail) use ($request) {
                    $start = DateHelper::convertDateFormat($request->valid_from);
                    $end = DateHelper::convertDateFormat($value);
                    if ($start && $end && strtotime($end) < strtotime($start)) {
                        $fail('The ' . $attribute . ' must be a date after or equal to valid from.');
                    }
                }
            ],
            'active' => 'boolean',
            'school_id' => 'required_if:scope,SCHOOL|nullable|exists:school,school_id',
            'student_id' => 'required_if:scope,STUDENT|nullable|exists:student,student_id',
        ]);

        $validated['valid_from'] = DateHelper::convertDateFormat($validated['valid_from']);
        if (isset($validated['valid_to'])) {
            $validated['valid_to'] = DateHelper::convertDateFormat($validated['valid_to']);
        }
        
        // Additional validation for PERCENT type
        if ($validated['discount_type'] === 'PERCENT' && $validated['discount_value'] > 100) {
            return back()->withErrors(['discount_value' => 'Percentage discount cannot exceed 100%.'])->withInput();
        }
        
        // Ensure correct scope assignment
        if ($validated['scope'] === 'SCHOOL') {
            $validated['student_id'] = null;
        } else {
            $validated['school_id'] = null;
        }
        
        // Set active to true if not provided
        $validated['active'] = $request->has('active') ? true : false;
        
        $discountRule->update($validated);
        
        return redirect()->route('discount-rules.index')->with('success', 'Discount rule updated successfully.');
    }
    
    public function destroy(DiscountRule $discountRule)
    {
        $discountRule->delete();
        
        return redirect()->route('discount-rules.index')->with('success', 'Discount rule deleted successfully.');
    }
}
