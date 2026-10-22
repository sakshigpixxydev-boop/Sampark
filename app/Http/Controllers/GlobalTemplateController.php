<?php

namespace App\Http\Controllers;

use App\Models\GlobalTemplate;
use App\Models\School;
use App\Models\District;
use App\Models\State;
use App\Models\Taluko;
use App\Models\SysUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class GlobalTemplateController extends Controller
{
    /**
     * Display a listing of templates.
     */
    public function index()
    {
        $templates = GlobalTemplate::orderBy('created_at', 'desc')->paginate(15);
        return view('students.announcements.templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new template.
     */
    public function create()
    {
        $schoolsQuery = School::query()->orderBy('schoolname');
        $statesQuery = State::query()->orderBy('sname');

        if (Auth::check() && !Auth::user()->hasRole('Admin')) {
            $userSchools = School::whereIn('school_id', Auth::user()->getAssignedSchoolIds())->get();
            $schoolsQuery->whereIn('school_id', $userSchools->pluck('school_id'));
            
            $allowedTalukaIds = $userSchools->pluck('takula_id')->filter()->unique();
            $allowedDistIds = Taluko::whereIn('takula_id', $allowedTalukaIds)->pluck('dist_id')->unique();
            $allowedStateIds = District::whereIn('dist_id', $allowedDistIds)->pluck('state_id')->unique();
            
            $statesQuery->whereIn('state_id', $allowedStateIds);
        }

        $schools = $schoolsQuery->get();
        $states = $statesQuery->get();
        
        $districts = collect();
        if (old('state_id')) {
            $districts = District::where('state_id', old('state_id'))->orderBy('dname')->get();
        }
        
        $talukos = collect();
        if (old('dist_id')) {
            $talukos = Taluko::where('dist_id', old('dist_id'))->orderBy('tname')->get();
        }

        $languages = DB::table('language')->select('lg_id', 'languagename')->orderBy('languagename')->get();
        $initiators = SysUser::whereHas('role', function($q) {
            $q->where('rolename', 'Initiator');
        })->get();

        return view('students.announcements.templates.create', compact('schools', 'states', 'districts', 'talukos', 'languages', 'initiators'));
    }

    /**
     * Store a newly created template.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:Email,WhatsApp,SMS,Event',
            'is_active' => 'boolean',
        ]);

        $filters = [
            'school_id' => $request->school_id,
            'state_id' => $request->state_id,
            'dist_id' => $request->dist_id,
            'taluka_id' => $request->taluka_id,
            'send_to' => $request->send_to,
            'communication_type' => $request->communication_type,
            'language' => $request->language,
            'initiator_contact' => $request->initiator_contact,
        ];

        GlobalTemplate::create([
            'title' => $request->title,
            'slug' => \Illuminate\Support\Str::slug($request->title) . '-' . uniqid(),
            'content' => $request->input('content'),
            'type' => $request->type,
            'filters' => array_filter($filters), // Only store non-null filters
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('students.announcements.templates.index')
            ->with('success', 'Template created successfully.');
    }

    /**
     * Show the form for editing the specified template.
     */
    public function edit($id)
    {
        $template = GlobalTemplate::findOrFail($id);
        $filters = $template->filters ?? [];
        
        $schoolsQuery = School::query()->orderBy('schoolname');
        $statesQuery = State::query()->orderBy('sname');

        if (Auth::check() && !Auth::user()->hasRole('Admin')) {
            $userSchools = School::whereIn('school_id', Auth::user()->getAssignedSchoolIds())->get();
            $schoolsQuery->whereIn('school_id', $userSchools->pluck('school_id'));
            
            $allowedTalukaIds = $userSchools->pluck('takula_id')->filter()->unique();
            $allowedDistIds = Taluko::whereIn('takula_id', $allowedTalukaIds)->pluck('dist_id')->unique();
            $allowedStateIds = District::whereIn('dist_id', $allowedDistIds)->pluck('state_id')->unique();
            
            $statesQuery->whereIn('state_id', $allowedStateIds);
        }

        $schools = $schoolsQuery->get();
        $states = $statesQuery->get();
        
        $districts = collect();
        if (isset($filters['state_id'])) {
            $districts = District::where('state_id', $filters['state_id'])->orderBy('dname')->get();
        }
        
        $talukos = collect();
        if (isset($filters['dist_id'])) {
            $talukos = Taluko::where('dist_id', $filters['dist_id'])->orderBy('tname')->get();
        }

        $languages = DB::table('language')->select('lg_id', 'languagename')->orderBy('languagename')->get();
        $initiators = SysUser::whereHas('role', function($q) {
            $q->where('rolename', 'Initiator');
        })->get();

        return view('students.announcements.templates.edit', compact('template', 'schools', 'states', 'districts', 'talukos', 'languages', 'initiators'));
    }

    /**
     * Update the specified template.
     */
    public function update(Request $request, $id)
    {
        $template = GlobalTemplate::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:Email,WhatsApp,SMS,Event',
        ]);

        $filters = [
            'school_id' => $request->school_id,
            'state_id' => $request->state_id,
            'dist_id' => $request->dist_id,
            'taluka_id' => $request->taluka_id,
            'send_to' => $request->send_to,
            'communication_type' => $request->communication_type,
            'language' => $request->language,
            'initiator_contact' => $request->initiator_contact,
        ];

        $template->update([
            'title' => $request->title,
            'content' => $request->input('content'),
            'type' => $request->type,
            'filters' => array_filter($filters),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('students.announcements.templates.index')
            ->with('success', 'Template updated successfully.');
    }

    /**
     * Remove the specified template.
     */
    public function destroy($id)
    {
        $template = GlobalTemplate::findOrFail($id);
        $template->delete();

        return redirect()->route('students.announcements.templates.index')
            ->with('success', 'Template deleted successfully.');
    }

    /**
     * API endpoint to get template details.
     */
    public function getTemplate($id)
    {
        try {
            $template = GlobalTemplate::findOrFail($id);
            return response()->json([
                'title' => $template->title,
                'content' => $template->content,
                'type' => $template->type,
                'filters' => $template->filters
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Template not found'], 404);
        }
    }
}
