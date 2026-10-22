<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\SchoolType;
use App\Models\EducationalBoard;
use App\Models\Country;
use App\Models\State;
use App\Models\District;
use App\Models\Taluko;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SchoolController extends Controller
{
    public function index(Request $request)
    {
        $query = School::with(['schoolType', 'educationalBoard', 'taluka.district.state']);

        // Apply filters
        if ($request->filled('schooltype_id')) {
            $query->where('schooltype_id', $request->schooltype_id);
        }

        if ($request->filled('eduboard_id')) {
            $query->where('eduboard_id', $request->eduboard_id);
        }

        // Handle Role-based restrictions
        if (Auth::user()->hasRole('Initiator') || Auth::user()->hasRole('School')) {
            $assignedSchoolIds = Auth::user()->getAssignedSchoolIds();
            $query->whereIn('school_id', $assignedSchoolIds);
        } else {
            // Admin-level users: apply status filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            } elseif (!Auth::user()->hasPermission(10032)) {
                // Default to approved schools for those without approval permission
                $query->where('status', 1);
            }
        }

        if ($request->filled('inactive')) {
            $query->where('inactive', $request->inactive);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('schoolname', 'like', "%{$search}%")
                    ->orWhere('adminname', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'schoolname');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $schools = $query->paginate(15)->withQueryString();

        // Get filter options
        $schoolTypes = SchoolType::orderBy('schooltypename')->get();
        $educationalBoards = EducationalBoard::orderBy('boardname')->get();

        return view('schools.index', compact('schools', 'schoolTypes', 'educationalBoards'));
    }

    public function create()
    {
        // Check if user has write permission (10032)
        if (!Auth::user()->hasPermission(10032)) {
            abort(403, 'You don\'t have permission to create schools.');
        }

        $schoolTypes = SchoolType::orderBy('schooltypename')->get();
        $educationalBoards = EducationalBoard::orderBy('boardname')->get();
        $countries = Country::orderBy('cname')->get();

        return view('schools.create', compact('schoolTypes', 'educationalBoards', 'countries'));
    }

    public function store(Request $request)
    {
        // Check if user has write permission (10032)
        if (!Auth::user()->hasPermission(10032)) {
            abort(403, 'You don\'t have permission to create schools.');
        }

        $validated = $request->validate([
            'schoolname' => 'required|string|max:1000',
            'schooltype_id' => 'required|exists:schooltype,schooltype_id',
            'eduboard_id' => 'required|exists:ms_eductionalboard,eduboard_id',
            'adminname' => 'required|string|max:200',
            'admincontact' => 'required|digits:10',
            'takula_id' => 'required|exists:ms_taluko,takula_id',
            'logourl' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'address1' => 'nullable|string|max:500',
            'address2' => 'nullable|string|max:500',
            'city_village' => 'nullable|string|max:100',
            'pincode' => 'nullable|digits:6',
            'phone' => 'nullable|string|min:7|max:15',
            'mobile' => 'nullable|digits:10',
            'email' => 'nullable|email|max:100',
            'website' => 'nullable|url|max:200',
            'establishedyear' => 'nullable|integer|min:1800|max:' . date('Y'),
            'inactive' => 'boolean',
            'status' => 'required|in:0,1',
        ]);

        // Handle logo upload
        if ($request->hasFile('logourl')) {
            $logoPath = $request->file('logourl')->store('school-logos', 'public');
            $validated['logourl'] = $logoPath;
        }

        // Set defaults
        $validated['inactive'] = $request->has('inactive') ? 1 : 0;
        $validated['createdby'] = Auth::id();
        $validated['created_at'] = now();

        School::create($validated);

        return redirect()->route('schools.index')->with('success', 'School created successfully.');
    }

    public function edit(School $school)
    {
        // Check if user has write permission (10032)
        if (!Auth::user()->hasPermission(10032)) {
            abort(403, 'You don\'t have permission to edit schools.');
        }

        $schoolTypes = SchoolType::orderBy('schooltypename')->get();
        $educationalBoards = EducationalBoard::orderBy('boardname')->get();
        $countries = Country::orderBy('cname')->get();

        // Get the location hierarchy for the school
        $selectedTaluka = $school->taluka;
        $selectedDistrict = $selectedTaluka ? $selectedTaluka->district : null;
        $selectedState = $selectedDistrict ? $selectedDistrict->state : null;
        $selectedCountry = $selectedState ? $selectedState->country : null;

        // Get related location data
        $states = $selectedCountry ? State::where('country_id', $selectedCountry->country_id)->orderBy('sname')->get() : collect();
        $districts = $selectedState ? District::where('state_id', $selectedState->state_id)->orderBy('dname')->get() : collect();
        $talukos = $selectedDistrict ? Taluko::where('dist_id', $selectedDistrict->dist_id)->orderBy('tname')->get() : collect();

        return view('schools.edit', compact(
            'school',
            'schoolTypes',
            'educationalBoards',
            'countries',
            'states',
            'districts',
            'talukos',
            'selectedCountry',
            'selectedState',
            'selectedDistrict',
            'selectedTaluka'
        ));
    }

    public function update(Request $request, School $school)
    {
        // Check if user has write permission (10032)
        if (!Auth::user()->hasPermission(10032)) {
            abort(403, 'You don\'t have permission to update schools.');
        }

        $validated = $request->validate([
            'schoolname' => 'required|string|max:1000',
            'schooltype_id' => 'required|exists:schooltype,schooltype_id',
            'eduboard_id' => 'required|exists:ms_eductionalboard,eduboard_id',
            'adminname' => 'required|string|max:200',
            'admincontact' => 'required|digits:10',
            'takula_id' => 'required|exists:ms_taluko,takula_id',
            'logourl' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'address1' => 'nullable|string|max:500',
            'address2' => 'nullable|string|max:500',
            'city_village' => 'nullable|string|max:100',
            'pincode' => 'nullable|digits:6',
            'phone' => 'nullable|string|min:7|max:15',
            'mobile' => 'nullable|digits:10',
            'email' => 'nullable|email|max:100',
            'website' => 'nullable|url|max:200',
            'establishedyear' => 'nullable|integer|min:1800|max:' . date('Y'),
            'inactive' => 'boolean',
            'status' => 'required|in:0,1',
        ]);

        // Handle logo upload
        if ($request->hasFile('logourl')) {
            // Delete old logo if exists
            if ($school->logourl && Storage::disk('public')->exists($school->logourl)) {
                Storage::disk('public')->delete($school->logourl);
            }

            $logoPath = $request->file('logourl')->store('school-logos', 'public');
            $validated['logourl'] = $logoPath;
        }

        // Set inactive status
        $validated['inactive'] = $request->has('inactive') ? 1 : 0;
        $validated['updated_at'] = now();

        $school->update($validated);

        return redirect()->route('schools.index')->with('success', 'School updated successfully.');
    }

    /**
     * Show school approval form
     */
    public function showApprovalForm($id)
    {
        // Check if user has permission
        if (!Auth::user()->hasPermission(10032)) {
            abort(403, 'You don\'t have permission to approve schools.');
        }

        $school = School::with(['schoolType', 'educationalBoard', 'lead', 'initiator'])->findOrFail($id);

        // Only allow approving schools with lead status
        if ($school->status != 0) {
            return redirect()->route('schools.index')
                ->with('error', 'This school has already been approved or is not in lead status.');
        }

        $standards = \App\Models\Standard::orderBy('std_id')->get();

        return view('schools.approve', compact('school', 'standards'));
    }

    /**
     * Approve school and set fee structure
     */
    public function approve(Request $request, $id)
    {
        // Check if user has permission
        if (!Auth::user()->hasPermission(10032)) {
            abort(403, 'You don\'t have permission to approve schools.');
        }

        $school = School::findOrFail($id);

        // Only allow approving schools with lead status
        if ($school->status != 0) {
            return redirect()->route('schools.index')
                ->with('error', 'This school has already been approved or is not in lead status.');
        }

        $validated = $request->validate([
            'fee_plans' => 'required|array|min:1',
            'fee_plans.*.standard_id' => 'required|exists:ms_standard,std_id',
            'fee_plans.*.amount' => 'required|numeric|min:0',
        ]);

        \DB::beginTransaction();
        try {
            // Update school status to final
            $school->update(['status' => 1]);

            // Get current active semester batch
            $currentBatch = \App\Models\SemesterBatch::active()->first();
            if (!$currentBatch) {
                // Fallback to latest if no active batch
                $currentBatch = \App\Models\SemesterBatch::orderBy('start_date', 'desc')->first();
            }

            if (!$currentBatch) {
                throw new \Exception('No semester batches found in the system. Please create one before approving schools.');
            }

            // Create fee plans for each standard
            foreach ($validated['fee_plans'] as $feePlan) {
                \App\Models\FeePlan::create([
                    'school_id' => $school->school_id,
                    'std_id' => $feePlan['standard_id'],
                    'amount' => $feePlan['amount'],
                    'fee_name' => 'Standard Fee', // Default name
                    'valid_from' => now(),
                    'sembatches_id' => $currentBatch->sembatches_id,
                ]);
            }

            \DB::commit();

            return redirect()->route('schools.index')
                ->with('success', 'School approved successfully with fee structure!');

        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to approve school: ' . $e->getMessage());
        }
    }
}

