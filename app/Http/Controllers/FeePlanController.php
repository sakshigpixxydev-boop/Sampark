<?php

namespace App\Http\Controllers;

use App\Models\FeePlan;
use App\Models\School;
use App\Models\Standard;
use App\Models\SemesterBatch;
use App\Helpers\DateHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeePlanController extends Controller
{
    public function index()
    {
        $query = FeePlan::with(['semesterBatches'])
            ->where('plan_type', 'student')
            ->orderBy('valid_from', 'desc');
        
        $feePlans = $query->paginate(15);
        
        return view('fee-plans.index', compact('feePlans'));
    }
    
    public function create()
    {
        $semesterBatches = SemesterBatch::orderBy('semester')->get();
        return view('fee-plans.create', compact('semesterBatches'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fee_name' => 'required|string|max:200',
            'amount' => 'required|numeric|min:0',
            'strike_through_amount' => 'nullable|numeric|min:0',
            'plan_auto_close_date' => 'nullable|date_format_dmy',
            'sembatches_id' => 'required|array|min:1',
            'sembatches_id.*' => 'exists:sembatches,sembatches_id',
            'is_active_on_form' => 'nullable|boolean',
        ]);
        
        if (isset($validated['plan_auto_close_date'])) {
            $validated['plan_auto_close_date'] = DateHelper::convertDateFormat($validated['plan_auto_close_date']);
        }
        
        $validated['is_active_on_form'] = $request->has('is_active_on_form') ? 1 : 0;
        $validated['plan_type'] = 'student';
        $validated['school_id'] = null;
        
        // Extract semester batch IDs for pivot table
        $semesterBatchIds = $validated['sembatches_id'];
        unset($validated['sembatches_id']);
        
        // Calculate valid_from and valid_to from selected semester batches
        $selectedBatches = SemesterBatch::whereIn('sembatches_id', $semesterBatchIds)->get();
        if ($selectedBatches->isNotEmpty()) {
            $validated['valid_from'] = $selectedBatches->min('start_date');
            $validated['valid_to'] = $selectedBatches->max('end_date');
        }
        
        $feePlan = FeePlan::create($validated);
        
        // Sync semester batches with pivot table
        if (!empty($semesterBatchIds)) {
            $feePlan->semesterBatches()->sync($semesterBatchIds);
        }
        
        return redirect()->route('fee-plans.index')->with('success', 'Fee plan created successfully.');
    }
    
    public function edit(FeePlan $feePlan)
    {
        $semesterBatches = SemesterBatch::orderBy('semester')->get();
        
        // Load semester batches relationship for the fee plan
        $feePlan->load('semesterBatches');
        
        return view('fee-plans.edit', compact('feePlan', 'semesterBatches'));
    }
    
    public function update(Request $request, FeePlan $feePlan)
    {
        $validated = $request->validate([
            'fee_name' => 'required|string|max:200',
            'amount' => 'required|numeric|min:0',
            'strike_through_amount' => 'nullable|numeric|min:0',
            'plan_auto_close_date' => 'nullable|date_format_dmy',
            'sembatches_id' => 'required|array|min:1',
            'sembatches_id.*' => 'exists:sembatches,sembatches_id',
            'is_active_on_form' => 'nullable|boolean',
        ]);
        
        if (isset($validated['plan_auto_close_date'])) {
            $validated['plan_auto_close_date'] = DateHelper::convertDateFormat($validated['plan_auto_close_date']);
        }
        
        $validated['is_active_on_form'] = $request->has('is_active_on_form') ? 1 : 0;
        $validated['school_id'] = null;
        
        // Extract semester batch IDs for pivot table
        $semesterBatchIds = $validated['sembatches_id'];
        unset($validated['sembatches_id']);
        
        // Calculate valid_from and valid_to from selected semester batches
        $selectedBatches = SemesterBatch::whereIn('sembatches_id', $semesterBatchIds)->get();
        if ($selectedBatches->isNotEmpty()) {
            $validated['valid_from'] = $selectedBatches->min('start_date');
            $validated['valid_to'] = $selectedBatches->max('end_date');
        }
        
        $feePlan->update($validated);
        
        // Sync semester batches with pivot table
        $feePlan->semesterBatches()->sync($semesterBatchIds);
        
        return redirect()->route('fee-plans.index')->with('success', 'Fee plan updated successfully.');
    }
    
    public function destroy(FeePlan $feePlan)
    {
        $feePlan->delete();
        
        return redirect()->route('fee-plans.index')->with('success', 'Fee plan deleted successfully.');
    }
}
