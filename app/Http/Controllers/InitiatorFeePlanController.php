<?php

namespace App\Http\Controllers;

use App\Models\InitiatorFinancialConfig;
use App\Models\InitiatorFinancialSlab;
use Illuminate\Http\Request;

class InitiatorFeePlanController extends Controller
{
    public function index()
    {
        $feePlans = InitiatorFinancialConfig::orderBy('created_at', 'desc')->paginate(15);
        return view('initiator-fee-plans.index', compact('feePlans'));
    }

    public function create()
    {
        return view('initiator-fee-plans.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'student_calculation_type' => 'required|in:0,1',
            'new_school_registration_commission' => 'required|numeric|min:0',
            'school_count_base' => 'required|integer|min:0',
            'school_count_slab_amount' => 'required|numeric|min:0',
            'per_student_commission_first_semester' => 'nullable|required_if:student_calculation_type,0|numeric|min:0|max:100',
            'per_student_commission_repeat_semester' => 'nullable|required_if:student_calculation_type,0|numeric|min:0|max:100|lte:per_student_commission_first_semester',
            'slabs' => 'nullable|array',
            'slabs.*.min_students' => 'required|integer|min:0',
            'slabs.*.commission_percentage' => 'nullable|numeric|min:0|max:100',
            'slabs.*.fixed_amount' => 'nullable|numeric|min:0',
        ], [
            'per_student_commission_repeat_semester.lte' => 'The Repeat Semester (%) commission must be less than or equal to the First Semester (%) commission.',
            'slabs.*.min_students.required' => 'The Minimum Students field is required for each slab.',
            'slabs.*.commission_percentage.numeric' => 'Commission Percentage must be a number.',
            'slabs.*.fixed_amount.numeric' => 'Fixed Amount must be a number.',
        ]);

        $data = $request->only([
            'name',
            'student_calculation_type',
            'new_school_registration_commission',
            'school_count_base',
            'school_count_slab_amount',
            'per_student_commission_first_semester',
            'per_student_commission_repeat_semester',
        ]);

        if ($data['student_calculation_type'] == 1) {
            $data['per_student_commission_first_semester'] = 0;
            $data['per_student_commission_repeat_semester'] = 0;
        }

        $feePlan = InitiatorFinancialConfig::create($data);

        if ($data['student_calculation_type'] == 1 && !empty($validated['slabs'])) {
            foreach ($validated['slabs'] as $slabData) {
                $feePlan->slabs()->create([
                    'min_students' => $slabData['min_students'],
                    'commission_percentage' => $slabData['commission_percentage'] ?? 0,
                    'fixed_amount' => $slabData['fixed_amount'] ?? 0,
                ]);
            }
        }

        return redirect()->route('initiator-fee-plans.index')->with('success', 'Initiator Fee Plan created successfully.');
    }

    public function edit($id)
    {
        $feePlan = InitiatorFinancialConfig::with('slabs')->findOrFail($id);
        return view('initiator-fee-plans.edit', compact('feePlan'));
    }

    public function update(Request $request, $id)
    {
        $feePlan = InitiatorFinancialConfig::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'student_calculation_type' => 'required|in:0,1',
            'new_school_registration_commission' => 'required|numeric|min:0',
            'school_count_base' => 'required|integer|min:0',
            'school_count_slab_amount' => 'required|numeric|min:0',
            'per_student_commission_first_semester' => 'nullable|required_if:student_calculation_type,0|numeric|min:0|max:100',
            'per_student_commission_repeat_semester' => 'nullable|required_if:student_calculation_type,0|numeric|min:0|max:100|lte:per_student_commission_first_semester',
            'slabs' => 'nullable|array',
            'slabs.*.min_students' => 'required|integer|min:0',
            'slabs.*.commission_percentage' => 'nullable|numeric|min:0|max:100',
            'slabs.*.fixed_amount' => 'nullable|numeric|min:0',
        ], [
            'per_student_commission_repeat_semester.lte' => 'The Repeat Semester (%) commission must be less than or equal to the First Semester (%) commission.',
            'slabs.*.min_students.required' => 'The Minimum Students field is required for each slab.',
            'slabs.*.commission_percentage.numeric' => 'Commission Percentage must be a number.',
            'slabs.*.fixed_amount.numeric' => 'Fixed Amount must be a number.',
        ]);

        $data = $request->only([
            'name',
            'student_calculation_type',
            'new_school_registration_commission',
            'school_count_base',
            'school_count_slab_amount',
            'per_student_commission_first_semester',
            'per_student_commission_repeat_semester',
        ]);

        if ($data['student_calculation_type'] == 1) {
            $data['per_student_commission_first_semester'] = 0;
            $data['per_student_commission_repeat_semester'] = 0;
        }

        $feePlan->update($data);

        $feePlan->slabs()->delete();
        if ($data['student_calculation_type'] == 1 && !empty($validated['slabs'])) {
            foreach ($validated['slabs'] as $slabData) {
                $feePlan->slabs()->create([
                    'min_students' => $slabData['min_students'],
                    'commission_percentage' => $slabData['commission_percentage'] ?? 0,
                    'fixed_amount' => $slabData['fixed_amount'] ?? 0,
                ]);
            }
        }

        return redirect()->route('initiator-fee-plans.index')->with('success', 'Initiator Fee Plan updated successfully.');
    }

    public function destroy($id)
    {
        $feePlan = InitiatorFinancialConfig::findOrFail($id);
        $feePlan->delete();

        return redirect()->route('initiator-fee-plans.index')->with('success', 'Initiator Fee Plan deleted successfully.');
    }
}
