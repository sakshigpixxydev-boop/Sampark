<?php

namespace App\Http\Controllers;

use App\Models\GrowthCriterion;
use App\Http\Traits\HandlesErrors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GrowthCriteriaController extends Controller
{
    use HandlesErrors;

    public function index()
    {
        try {
            if (!Auth::user()->hasPermission(10504)) {
                abort(403, 'You don\'t have permission to manage growth criteria.');
            }

            $criteria = GrowthCriterion::orderBy('display_order', 'asc')->get();
            return view('growth-criteria.index', compact('criteria'));
        } catch (\Exception $e) {
            return $this->handleError($e, 'Failed to load growth criteria.');
        }
    }

    public function create()
    {
        if (!Auth::user()->hasPermission(10504)) {
            abort(403, 'You don\'t have permission to manage growth criteria.');
        }
        return view('growth-criteria.create');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermission(10504)) {
            abort(403, 'You don\'t have permission to manage growth criteria.');
        }

        $validated = $request->validate([
            'criterion_name' => 'required|string|max:255',
            'min_score' => 'required|numeric|min:0',
            'max_score' => 'required|numeric|gt:min_score',
            'status' => 'required|boolean',
            'display_order' => 'required|integer',
        ]);

        try {
            GrowthCriterion::create($validated);
            return redirect()->route('growth-criteria.index')->with('success', 'Criterion created successfully.');
        } catch (\Exception $e) {
            return $this->handleError($e, 'Failed to create criterion.');
        }
    }

    public function edit(GrowthCriterion $growth_criterion)
    {
        if (!Auth::user()->hasPermission(10504)) {
            abort(403, 'You don\'t have permission to manage growth criteria.');
        }
        return view('growth-criteria.edit', ['criterion' => $growth_criterion]);
    }

    public function update(Request $request, GrowthCriterion $growth_criterion)
    {
        if (!Auth::user()->hasPermission(10504)) {
            abort(403, 'You don\'t have permission to manage growth criteria.');
        }

        $validated = $request->validate([
            'criterion_name' => 'required|string|max:255',
            'min_score' => 'required|numeric|min:0',
            'max_score' => 'required|numeric|gt:min_score',
            'status' => 'required|boolean',
            'display_order' => 'required|integer',
        ]);

        try {
            $growth_criterion->update($validated);
            return redirect()->route('growth-criteria.index')->with('success', 'Criterion updated successfully.');
        } catch (\Exception $e) {
            return $this->handleError($e, 'Failed to update criterion.');
        }
    }

    public function destroy(GrowthCriterion $growth_criterion)
    {
        if (!Auth::user()->hasPermission(10504)) {
            abort(403, 'You don\'t have permission to manage growth criteria.');
        }

        try {
            $growth_criterion->delete();
            return redirect()->route('growth-criteria.index')->with('success', 'Criterion deleted successfully.');
        } catch (\Exception $e) {
            return $this->handleError($e, 'Failed to delete criterion. It might be in use.');
        }
    }
}
