<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialImage;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Helpers\DateHelper;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->hasPermission(10601)) {
            abort(403);
        }

        $query = Material::query();

        // Target visibility logic for Students
        if (Auth::user()->hasRole('Student')) {
            $student = Auth::user()->students()->first();
            if ($student) {
                $query->where(function($q) use ($student) {
                    $q->whereNull('filters')
                      ->orWhereJsonContains('filters->school_ids', $student->school_id);
                });
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('material_type')) {
            $query->where('material_type', $request->material_type);
        }

        $materials = $query->with('images')->orderBy('name')->paginate(15);

        return view('materials.index', compact('materials'));
    }

    public function create()
    {
        if (!Auth::user()->hasPermission(10602)) {
            abort(403);
        }

        $schools = School::orderBy('schoolname')->get();

        return view('materials.create', compact('schools'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermission(10602)) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discounted_price' => 'nullable|numeric|min:0|lt:price',
            'discount_start_date' => 'nullable|date_format_dmy',
            'discount_end_date' => [
                'nullable',
                'date_format_dmy',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->filled('discount_start_date')) {
                        $from = DateHelper::convertDateFormat($request->discount_start_date);
                        $to = DateHelper::convertDateFormat($value);
                        if ($from && $to && $to < $from) {
                            $fail('The Discount End Date must be after or equal to Discount Start Date.');
                        }
                    }
                }
            ],
            'material_type' => 'required|in:0,1',
            'inactive' => 'boolean',
            'std_ids' => 'nullable|array',
            'school_ids' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ], [
            'discounted_price.lt' => 'The discounted price must be less than the original price.'
        ]);

        $validated['discount_start_date'] = DateHelper::convertDateFormat($validated['discount_start_date']);
        $validated['discount_end_date'] = DateHelper::convertDateFormat($validated['discount_end_date']);

        $validated['inactive'] = $request->has('inactive') ? 1 : 0;
        
        $filters = [];
        if ($request->filled('school_ids')) $filters['school_ids'] = $request->school_ids;
        $validated['filters'] = !empty($filters) ? $filters : null;

        $material = Material::create($validated);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('materials', 'public');
                $material->images()->create(['image_path' => $path]);
            }
        }

        return redirect()->route('materials.index')->with('success', 'Material created successfully.');
    }

    public function edit(Material $material)
    {
        if (!Auth::user()->hasPermission(10602)) {
            abort(403);
        }

        $schools = School::orderBy('schoolname')->get();
        $material->load('images');

        return view('materials.edit', compact('material', 'schools'));
    }

    public function update(Request $request, Material $material)
    {
        if (!Auth::user()->hasPermission(10602)) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discounted_price' => 'nullable|numeric|min:0|lt:price',
            'discount_start_date' => 'nullable|date_format_dmy',
            'discount_end_date' => [
                'nullable',
                'date_format_dmy',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->filled('discount_start_date')) {
                        $from = DateHelper::convertDateFormat($request->discount_start_date);
                        $to = DateHelper::convertDateFormat($value);
                        if ($from && $to && $to < $from) {
                            $fail('The Discount End Date must be after or equal to Discount Start Date.');
                        }
                    }
                }
            ],
            'material_type' => 'required|in:0,1',
            'inactive' => 'boolean',
            'std_ids' => 'nullable|array',
            'school_ids' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ], [
            'discounted_price.lt' => 'The discounted price must be less than the original price.'
        ]);

        $validated['discount_start_date'] = DateHelper::convertDateFormat($validated['discount_start_date']);
        $validated['discount_end_date'] = DateHelper::convertDateFormat($validated['discount_end_date']);

        $validated['inactive'] = $request->has('inactive') ? 1 : 0;

        $filters = [];
        if ($request->filled('school_ids')) $filters['school_ids'] = $request->school_ids;
        $validated['filters'] = !empty($filters) ? $filters : null;

        $material->update($validated);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('materials', 'public');
                $material->images()->create(['image_path' => $path]);
            }
        }

        return redirect()->route('materials.index')->with('success', 'Material updated successfully.');
    }

    public function destroy(Material $material)
    {
        if (!Auth::user()->hasPermission(10603)) {
            abort(403);
        }

        if ($material->requests()->count() > 0) {
            return redirect()->route('materials.index')->with('error', 'Cannot delete material with existing requests.');
        }

        // Delete images from storage
        foreach ($material->images as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }

        $material->delete();

        return redirect()->route('materials.index')->with('success', 'Material deleted successfully.');
    }

    public function deleteImage(MaterialImage $image)
    {
        if (!Auth::user()->hasPermission(10602)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Deletion failed'], 500);
        }
    }
}
