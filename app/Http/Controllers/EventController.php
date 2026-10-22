<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\School;
use App\Models\District;
use App\Models\State;
use App\Models\Taluko;
use App\Models\SysUser;
use App\Models\UserRead;
use App\Models\GlobalTemplate;
use App\Models\Student;
use App\Models\Guardian;
use App\Services\ExternalEmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\DateHelper;

class EventController extends Controller
{
    /**
     * Display a listing of events.
     */
    public function index(Request $request)
    {
        if (!Auth::user()->hasPermission(10401)) {
            abort(403);
        }

        $query = Event::with(['school', 'state', 'district', 'taluka']);

        // Filter by school if provided
        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        // Area filtering
        if ($request->filled('state_id')) {
            $query->where('state_id', $request->state_id);
        }
        if ($request->filled('dist_id')) {
            $query->where('dist_id', $request->dist_id);
        }
        if ($request->filled('taluka_id')) {
            $query->where('taluka_id', $request->taluka_id);
        }

        // Role-based visibility
        if (Auth::user()->hasRole('School') || Auth::user()->hasRole('Initiator') || Auth::user()->hasRole('Student')) {
            $user = Auth::user();
            $query->where(function($q) use ($user) {
                // 1. Events for assigned schools
                $schoolIds = $user->getAssignedSchoolIds();
                if (!empty($schoolIds)) {
                    $q->whereIn('school_id', $schoolIds);
                }

                // 2. Area-wise events (where school matches user's area or explicitly set area matches)
                // For Students, we use their student record areas
                if ($user->hasRole('Student')) {
                    $student = Student::where('email', $user->email)->first() 
                            ?? Student::where('lmsuser', $user->username)->first();
                    if ($student) {
                        $q->orWhere(function($subq) use ($student) {
                            $subq->where('state_id', $student->state_id);
                            if ($student->dist_id) $subq->where(function($dq) use ($student) { $dq->whereNull('dist_id')->orWhere('dist_id', $student->dist_id); });
                            if ($student->takula_id) $subq->where(function($tq) use ($student) { $tq->whereNull('taluka_id')->orWhere('taluka_id', $student->takula_id); });
                        });
                    }
                }
                
                // For School users, we use their assigned schools' areas
                $assignedSchools = School::whereIn('school_id', $schoolIds)->get();
                if ($assignedSchools->isNotEmpty()) {
                    $talukaIds = $assignedSchools->pluck('takula_id')->filter()->unique();
                    if ($talukaIds->isNotEmpty()) {
                        $q->orWhereIn('taluka_id', $talukaIds);
                        
                        $distIds = Taluko::whereIn('takula_id', $talukaIds)->pluck('dist_id')->unique();
                        if ($distIds->isNotEmpty()) {
                            $q->orWhereIn('dist_id', $distIds);
                            
                            $stateIds = District::whereIn('dist_id', $distIds)->pluck('state_id')->unique();
                            if ($stateIds->isNotEmpty()) {
                                $q->orWhereIn('state_id', $stateIds);
                            }
                        }
                    }
                }
            });
        }

        // Search by title or notes
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $events = $query->orderBy('event_date', 'desc')->paginate(15);
        
        $schoolsQuery = School::query()->orderBy('schoolname');
        if (Auth::user()->hasRole('School') || Auth::user()->hasRole('Initiator')) {
            $assignedIds = Auth::user()->getAssignedSchoolIds();
            $schoolsQuery->whereIn('school_id', $assignedIds);
        }
        $schools = $schoolsQuery->get();

        $statesQuery = State::query()->orderBy('sname');
        $districtsQuery = District::query()->orderBy('dname');
        $talukasQuery = Taluko::query()->orderBy('tname');

        if (Auth::user()->hasRole('School') || Auth::user()->hasRole('Initiator') || Auth::user()->hasRole('Student')) {
            $userSchools = School::whereIn('school_id', Auth::user()->getAssignedSchoolIds())->get();
            $allowedTalukaIds = $userSchools->pluck('takula_id')->filter()->unique();
            $allowedDistIds = Taluko::whereIn('takula_id', $allowedTalukaIds)->pluck('dist_id')->unique();
            $allowedStateIds = District::whereIn('dist_id', $allowedDistIds)->pluck('state_id')->unique();
            
            $statesQuery->whereIn('state_id', $allowedStateIds);
            $districtsQuery->whereIn('dist_id', $allowedDistIds);
            $talukasQuery->whereIn('takula_id', $allowedTalukaIds);
        }

        $states = $statesQuery->get();
        $districts = $districtsQuery->get();
        $talukas = $talukasQuery->get();

        return view('events.index', compact('events', 'schools', 'states', 'districts', 'talukas'));
    }

    /**
     * Show the form for creating a new event.
     */
    public function create(Request $request)
    {
        if (!Auth::user()->hasPermission(10402)) {
            abort(403);
        }

        $templates = GlobalTemplate::where('type', 'Event')->where('is_active', true)->get();
        $schoolsQuery = School::query()->orderBy('schoolname');
        
        $statesQuery = State::query()->orderBy('sname');

        if (Auth::user()->hasRole('School') || Auth::user()->hasRole('Initiator') || Auth::user()->hasRole('Student')) {
            $userSchools = School::whereIn('school_id', Auth::user()->getAssignedSchoolIds())->get();
            $schoolsQuery->whereIn('school_id', $userSchools->pluck('school_id'));
            
            $allowedTalukaIds = $userSchools->pluck('takula_id')->filter()->unique();
            $allowedDistIds = Taluko::whereIn('takula_id', $allowedTalukaIds)->pluck('dist_id')->unique();
            $allowedStateIds = District::whereIn('dist_id', $allowedDistIds)->pluck('state_id')->unique();
            
            $statesQuery->whereIn('state_id', $allowedStateIds);
        }
        
        $schools = $schoolsQuery->get();
        $states = $statesQuery->get();

        $reusedEvent = new Event();
        $selectedTemplate = null;

        if ($request->filled('reused_id')) {
            $event = Event::find($request->reused_id);
            if ($event) {
                $reusedEvent->fill($event->toArray());
            }
        } elseif ($request->filled('template_id')) {
            $selectedTemplate = GlobalTemplate::where('type', 'Event')->find($request->template_id);
            if ($selectedTemplate) {
                $reusedEvent->title = $selectedTemplate->title;
                $reusedEvent->notes = $selectedTemplate->content;
                if ($selectedTemplate->filters) {
                    $reusedEvent->fill($selectedTemplate->filters);
                }
            }
        }

        $districts = collect();
        if ($reusedEvent->state_id) {
            $districts = District::where('state_id', $reusedEvent->state_id)->orderBy('dname')->get();
        }

        $talukos = collect();
        if ($reusedEvent->dist_id) {
            $talukos = Taluko::where('dist_id', $reusedEvent->dist_id)->orderBy('tname')->get();
        }

        return view('events.create', compact('schools', 'templates', 'selectedTemplate', 'reusedEvent', 'states', 'districts', 'talukos'));
    }

    /**
     * Reuse an existing event.
     */
    public function reuse(Event $event)
    {
        if (!Auth::user()->hasPermission(10402)) {
            abort(403);
        }

        return redirect()->route('events.create', [
            'reused_id' => $event->event_id,
            'school_id' => $event->school_id
        ]);
    }

    /**
     * Store a newly created event in storage.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->hasPermission(10402)) {
            abort(403);
        }

        $validated = $request->validate([
            'school_id' => 'nullable|exists:school,school_id',
            'title' => 'required|string|max:255',
            'event_date' => 'required|date_format_dmy',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'feedback' => 'nullable|string',
            'reminder_date' => 'nullable|date_format_dmy',
            'state_id' => 'nullable|exists:ms_state,state_id',
            'dist_id' => 'nullable|exists:ms_dist,dist_id',
            'taluka_id' => 'nullable|exists:ms_taluko,takula_id',
            'send_to' => 'required|in:All,Schools,Students,Guardians',
            'communication_type' => 'required|in:Email,Notification,Both',
        ]);

        if (isset($validated['event_date'])) {
            $validated['event_date'] = DateHelper::convertDateFormat($validated['event_date']);
        }
        if (isset($validated['reminder_date'])) {
            $validated['reminder_date'] = DateHelper::convertDateFormat($validated['reminder_date']);
        }

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('events', 'public');
            $validated['photo'] = $path;
        }

        $validated['createdby'] = Auth::user()->username ?? 'system';
        $validated['created_at'] = now();

        $event = Event::create($validated);

        // Handle Notifications
        if ($event->communication_type !== 'Notification') {
            $this->sendEventEmails($event);
        }

        return redirect()->route('events.index')->with('success', 'Event created successfully.');
    }

    /**
     * Send event emails based on filters.
     */
    protected function sendEventEmails(Event $event)
    {
        $emails = [];
        $target = $event->send_to;

        // Query students/schools based on area filters
        $studentQuery = Student::query();
        $schoolQuery = School::query();

        if ($event->school_id) {
            $studentQuery->where('school_id', $event->school_id);
            $schoolQuery->where('school_id', $event->school_id);
        }
        if ($event->state_id) {
            $studentQuery->where('state_id', $event->state_id);
            // Schools don't have state_id directly, join via taluka/district if needed, 
            // but usually a school belongs to a taluka which belongs to a district which belongs to a state.
            // Simplified: filter schools that belong to the state.
            $schoolQuery->whereIn('takula_id', function($q) use ($event) {
                $q->select('takula_id')->from('ms_taluko')
                  ->whereIn('dist_id', function($sq) use ($event) {
                      $sq->select('dist_id')->from('ms_dist')->where('state_id', $event->state_id);
                  });
            });
        }
        if ($event->dist_id) {
            $studentQuery->where('dist_id', $event->dist_id);
            $schoolQuery->whereIn('takula_id', function($q) use ($event) {
                $q->select('takula_id')->from('ms_taluko')->where('dist_id', $event->dist_id);
            });
        }
        if ($event->taluka_id) {
            $studentQuery->where('takula_id', $event->taluka_id);
            $schoolQuery->where('takula_id', $event->taluka_id);
        }

        if ($target === 'All' || $target === 'Students') {
            $emails = array_merge($emails, $studentQuery->whereNotNull('email')->pluck('email', 'fname')->toArray());
        }

        if ($target === 'All' || $target === 'Guardians') {
            $studentIds = $studentQuery->pluck('student_id');
            $guardianEmails = Guardian::whereIn('student_id', $studentIds)
                ->whereNotNull('email')
                ->pluck('email', 'guardianname')
                ->toArray();
            $emails = array_merge($emails, $guardianEmails);
        }

        if ($target === 'All' || $target === 'Schools') {
            $schoolEmails = $schoolQuery->whereNotNull('email')->pluck('email', 'schoolname')->toArray();
            $emails = array_merge($emails, $schoolEmails);
        }

        if (!empty($emails)) {
            $emailService = new ExternalEmailService();
            $subject = "New Event: " . $event->title;
            $message = "<h3>" . $event->title . "</h3>" .
                       "<p><strong>Date:</strong> " . $event->event_date->format('d M Y') . "</p>" .
                       "<div>" . $event->notes . "</div>";
            
            // Send in chunks to avoid timeout
            $chunks = array_chunk($emails, 50, true);
            foreach ($chunks as $chunk) {
                $emailService->sendSmtpMail($chunk, $subject, $message);
            }
        }
    }

    /**
     * Display the specified event.
     */
    public function show(Event $event)
    {
        if (!Auth::user()->hasPermission(10401)) {
            abort(403);
        }

        // Mark as read
        UserRead::firstOrCreate([
            'sysuser_id' => Auth::id(),
            'readable_id' => $event->event_id,
            'readable_type' => 'Event',
        ]);

        return view('events.show', compact('event'));
    }

    /**
     * Show the form for editing the specified event.
     */
    public function edit(Event $event)
    {
        if (!Auth::user()->hasPermission(10402)) {
            abort(403);
        }

        $schoolsQuery = School::query()->orderBy('schoolname');
        $statesQuery = State::query()->orderBy('sname');

        if (Auth::user()->hasRole('School') || Auth::user()->hasRole('Initiator') || Auth::user()->hasRole('Student')) {
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
        if ($event->state_id) {
            $districts = District::where('state_id', $event->state_id)->orderBy('dname')->get();
        }

        $talukos = collect();
        if ($event->dist_id) {
            $talukos = Taluko::where('dist_id', $event->dist_id)->orderBy('tname')->get();
        }

        return view('events.edit', compact('event', 'schools', 'states', 'districts', 'talukos'));
    }

    /**
     * Update the specified event in storage.
     */
    public function update(Request $request, Event $event)
    {
        if (!Auth::user()->hasPermission(10402)) {
            abort(403);
        }

        $validated = $request->validate([
            'school_id' => 'nullable|exists:school,school_id',
            'title' => 'required|string|max:255',
            'event_date' => 'required|date_format_dmy',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'feedback' => 'nullable|string',
            'reminder_date' => 'nullable|date_format_dmy',
            'state_id' => 'nullable|exists:ms_state,state_id',
            'dist_id' => 'nullable|exists:ms_dist,dist_id',
            'taluka_id' => 'nullable|exists:ms_taluko,takula_id',
            'send_to' => 'required|in:All,Schools,Students,Guardians',
            'communication_type' => 'required|in:Email,Notification,Both',
        ]);

        if (isset($validated['event_date'])) {
            $validated['event_date'] = DateHelper::convertDateFormat($validated['event_date']);
        }
        if (isset($validated['reminder_date'])) {
            $validated['reminder_date'] = DateHelper::convertDateFormat($validated['reminder_date']);
        }

        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($event->photo && Storage::disk('public')->exists($event->photo)) {
                Storage::disk('public')->delete($event->photo);
            }
            $path = $request->file('photo')->store('events', 'public');
            $validated['photo'] = $path;
        }

        $validated['updated_at'] = now();

        $event->update($validated);

        return redirect()->route('events.index')->with('success', 'Event updated successfully.');
    }

    /**
     * Remove the specified event from storage.
     */
    public function destroy(Event $event)
    {
        if (!Auth::user()->hasPermission(10403)) {
            abort(403);
        }

        // Delete photo
        if ($event->photo && Storage::disk('public')->exists($event->photo)) {
            Storage::disk('public')->delete($event->photo);
        }

        $event->delete();

        return redirect()->route('events.index')->with('success', 'Event deleted successfully.');
    }
}
