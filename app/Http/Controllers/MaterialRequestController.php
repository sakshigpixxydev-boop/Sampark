<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MaterialRequestController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->hasPermission(10611)) {
            abort(403);
        }

        $query = MaterialRequest::with(['school', 'material']);

        // School users can only see their own requests
        if (Auth::user()->hasRole('School')) {
            $schoolIds = Auth::user()->getAssignedSchoolIds();
            $query->whereIn('school_id', $schoolIds);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        $requests = $query->latest()->paginate(15);
        $schools = School::orderBy('schoolname')->get();

        return view('material-requests.index', compact('requests', 'schools'));
    }

    public function create()
    {
        if (!Auth::user()->hasPermission(10612)) {
            abort(403);
        }

        $materialQuery = Material::where('inactive', false)->with('images');
        
        if (Auth::user()->hasRole('School')) {
            $schoolIds = Auth::user()->getAssignedSchoolIds();
            $schools = School::whereIn('school_id', $schoolIds)->get();

            // Filter materials based on assigned schools
            $materialQuery->where(function($q) use ($schoolIds) {
                $q->whereNull('filters');
                foreach ($schoolIds as $schoolId) {
                    $q->orWhereJsonContains('filters->school_ids', (string)$schoolId);
                }
            });
        } else {
            $schools = School::orderBy('schoolname')->get();
        }

        if ($schools->isEmpty()) {
            return redirect()->route('material-requests.index')->with('error', 'No schools found for your account.');
        }

        $materials = $materialQuery->get();

        return view('material-requests.create', compact('materials', 'schools'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermission(10612)) {
            abort(403);
        }

        $validated = $request->validate([
            'school_id' => 'required|exists:school,school_id',
            'material_id' => 'required|exists:materials,id',
            'quantity' => 'required|integer|min:1',
            'delivery_type' => 'required|in:digital,printable',
        ]);

        $material = Material::findOrFail($validated['material_id']);
        
        // Use active_price (calculated based on discounts) to calculate total
        $validated['total_amount'] = $material->active_price * $validated['quantity'];
        $validated['status'] = 'pending';

        MaterialRequest::create($validated);

        return redirect()->route('material-requests.index')->with('success', 'Material request submitted successfully.');
    }

    public function show(MaterialRequest $materialRequest)
    {
        if (!Auth::user()->hasPermission(10611)) {
            abort(403);
        }

        // Integrity check for School role
        if (Auth::user()->hasRole('School')) {
            $schoolIds = Auth::user()->getAssignedSchoolIds();
            if (!in_array($materialRequest->school_id, $schoolIds)) {
                abort(403);
            }
        }

        return view('material-requests.show', compact('materialRequest'));
    }

    public function updateStatus(Request $request, MaterialRequest $materialRequest)
    {
        if (!Auth::user()->hasPermission(10613)) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,dispatched,completed',
            'admin_remarks' => 'nullable|string',
        ]);

        $materialRequest->update($validated);

        return redirect()->route('material-requests.index')->with('success', 'Request status updated successfully.');
    }
}
