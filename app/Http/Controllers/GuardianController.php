<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use App\Models\Student;
use App\Models\Relation;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\GuardianMessage;
use App\Models\GuardianMessageQueue;

class GuardianController extends Controller
{
    /**
     * Display a listing of guardians.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isSchoolUser = ($user->role?->rolename === 'School');
        $isInitiatorUser = ($user->role?->rolename === 'Initiator');
        
        $assignedSchoolIds = $isSchoolUser ? $user->getAssignedSchoolIds() : ($isInitiatorUser ? $user->getAssignedSchoolIds() : []);
        $assignedStudentIds = $isInitiatorUser ? $user->getAssignedStudentIds() : [];

        $query = $this->applyGuardianFilters($request);
        $guardians = $query->orderBy('guardian_id', 'desc')->paginate(15)->appends($request->all());

        if ($request->ajax()) {
            return view('guardians.partials.guardian_grid', compact('guardians'))->render();
        }
        
        // Get filter options
        if ($isSchoolUser || $isInitiatorUser) {
            $schools = School::select('school_id', 'schoolname')
                ->whereIn('school_id', $assignedSchoolIds)
                ->orderBy('schoolname')
                ->get();
            $studentsQuery = Student::select('student_id', 'fname', 'mname', 'lname');
            if ($isSchoolUser) {
                $studentsQuery->whereIn('school_id', $assignedSchoolIds);
            } else {
                $studentsQuery->whereIn('student_id', $assignedStudentIds);
            }
            $students = $studentsQuery->orderBy('fname')->get();
        } else {
            $schools = School::select('school_id', 'schoolname')
                ->orderBy('schoolname')
                ->get();
            $students = Student::select('student_id', 'fname', 'mname', 'lname')
                ->orderBy('fname')
                ->get();
        }
        $relations = Relation::orderBy('relation')->get();
        
        return view('guardians.index', compact('guardians', 'schools', 'students', 'relations'));
    }

    /**
     * Apply common guardian filters based on auth and request.
     */
    private function applyGuardianFilters(Request $request)
    {
        $query = Guardian::with(['student', 'relation']);

        $user = Auth::user();
        $isSchoolUser = ($user->role?->rolename === 'School');
        $isInitiatorUser = ($user->role?->rolename === 'Initiator');

        if ($isSchoolUser) {
            $assignedSchoolIds = $user->getAssignedSchoolIds();
            if (empty($assignedSchoolIds)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereHas('student', function($q) use ($assignedSchoolIds) {
                    $q->whereIn('school_id', $assignedSchoolIds);
                });
            }
        } elseif ($isInitiatorUser) {
            $assignedStudentIds = $user->getAssignedStudentIds();
            if (empty($assignedStudentIds)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('student_id', $assignedStudentIds);
            }
        }
        
        // Filter by school if provided
        if ($request->filled('school_id')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('school_id', $request->school_id);
            });
        }

        // Filter by student if provided
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }
        
        // Filter by relation if provided
        if ($request->filled('relation_id')) {
            $query->where('relation_id', $request->relation_id);
        }
        
        // Search by name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('fname', 'like', "%{$search}%")
                  ->orWhere('mname', 'like', "%{$search}%")
                  ->orWhere('lname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    /**
     * Show the form for creating a new guardian.
     */
    public function create(Request $request)
    {
        // Check if user has write permission for students (10042) - guardians are part of student management
        if (!Auth::user()->hasPermission(10042)) {
            abort(403, 'You don\'t have permission to create guardians.');
        }
        
        $students = Student::select('student_id', 'fname', 'mname', 'lname')
            ->orderBy('fname')
            ->get();
        $relations = Relation::orderBy('relation')->get();
        
        // Pre-select student if provided in query string
        $selectedStudentId = $request->query('student_id');
        
        return view('guardians.create', compact('students', 'relations', 'selectedStudentId'));
    }

    /**
     * Store a newly created guardian in storage.
     */
    public function store(Request $request)
    {
        // Check if user has write permission for students (10042) - guardians are part of student management
        if (!Auth::user()->hasPermission(10042)) {
            abort(403, 'You don\'t have permission to create guardians.');
        }
        
        try {
            $validated = $request->validate([
                'student_id' => 'required|exists:student,student_id',
                'relation_id' => 'required|exists:ms_relation,relation_id',
                'fname' => 'required|string|max:100',
                'mname' => 'required|string|max:100',
                'lname' => 'required|string|max:100',
                'gender' => 'nullable|in:M,F,O',
                'phone' => 'nullable|digits:10',
                'email' => 'nullable|email|max:255',
                'education' => 'nullable|string',
                'work_experience' => 'nullable|string|max:255',
                'how_to_help' => 'nullable|string',
            ]);
            
            $validated['createdby'] = Auth::user()->username ?? 'system';
            $validated['created_at'] = now();
            
            Guardian::create($validated);
            
            return redirect()
                ->route('guardians.index')
                ->with('success', 'Guardian added successfully.');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Failed to create guardian: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Failed to create guardian. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified guardian.
     */
    public function edit(Guardian $guardian)
    {
        // Check if user has write permission for students (10042) - guardians are part of student management
        if (!Auth::user()->hasPermission(10042)) {
            abort(403, 'You don\'t have permission to edit guardians.');
        }
        
        $students = Student::select('student_id', 'fname', 'mname', 'lname')
            ->orderBy('fname')
            ->get();
        $relations = Relation::orderBy('relation')->get();
        
        return view('guardians.edit', compact('guardian', 'students', 'relations'));
    }

    /**
     * Update the specified guardian in storage.
     */
    public function update(Request $request, Guardian $guardian)
    {
        // Check if user has write permission for students (10042) - guardians are part of student management
        if (!Auth::user()->hasPermission(10042)) {
            abort(403, 'You don\'t have permission to update guardians.');
        }
        
        $validated = $request->validate([
            'student_id' => 'required|exists:student,student_id',
            'relation_id' => 'required|exists:ms_relation,relation_id',
            'fname' => 'required|string|max:100',
            'mname' => 'required|string|max:100',
            'lname' => 'required|string|max:100',
            'gender' => 'nullable|in:M,F,O',
            'phone' => 'nullable|digits:10',
            'email' => 'nullable|email|max:255',
            'education' => 'nullable|string',
            'work_experience' => 'nullable|string|max:255',
            'how_to_help' => 'nullable|string',
        ]);
        
        $guardian->update($validated);
        
        return redirect()
            ->route('guardians.index')
            ->with('success', 'Guardian updated successfully.');
    }

    /**
     * Remove the specified guardian from storage.
     */
    public function destroy(Guardian $guardian)
    {
        $guardian->delete();
        
        return redirect()
            ->route('guardians.index')
            ->with('success', 'Guardian deleted successfully.');
    }

    /**
     * Initiate bulk message creation.
     */
    public function initiateBulkMessage(Request $request)
    {
        if (!Auth::user()->hasPermission(10211)) {
            abort(403, 'You don\'t have permission to create guardian messages.');
        }

        $selectedIds = $request->input('selected_guardians', []);
        
        if ($request->boolean('select_all_pages')) {
            $query = $this->applyGuardianFilters($request);
            if ($request->filled('excluded_guardians')) {
                $query->whereNotIn('guardian_id', $request->excluded_guardians);
            }
            $selectedIds = $query->pluck('guardian_id')->toArray();
        }

        if (empty($selectedIds)) {
            return back()->with('error', 'Please select at least one guardian.');
        }

        // Create a draft message
        $message = GuardianMessage::create([
            'subject' => 'Draft',
            'message' => '',
            'status' => 'READY',
        ]);

        // Store selected IDs in session for the next step
        session(['selected_guardians_' . $message->id => $selectedIds]);

        return redirect()->route('guardians.message.create', $message->id);
    }

    /**
     * Show the form for adding title and message.
     */
    public function createMessage($id)
    {
        if (!Auth::user()->hasPermission(10211)) {
            abort(403, 'You don\'t have permission to create guardian messages.');
        }

        $message = GuardianMessage::findOrFail($id);

        $selectedIds = session('selected_guardians_' . $message->id);
        if (!$selectedIds) {
            return redirect()->route('guardians.index')->with('error', 'Session expired or invalid message initiation.');
        }

        $guardiansCount = count($selectedIds);

        return view('guardians.message_create', compact('message', 'guardiansCount'));
    }

    /**
     * Store the message and create queue entries.
     */
    public function storeMessage(Request $request, $id)
    {
        if (!Auth::user()->hasPermission(10211)) {
            abort(403, 'You don\'t have permission to create guardian messages.');
        }

        $message = GuardianMessage::findOrFail($id);

        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $selectedIds = session('selected_guardians_' . $message->id);
        if (!$selectedIds) {
            return redirect()->route('guardians.index')->with('error', 'Session expired.');
        }

        DB::beginTransaction();
        try {
            $message->update([
                'subject' => $request->subject,
                'message' => $request->message,
                'status' => 'READY',
            ]);

            $queueData = [];
            $now = now();
            foreach ($selectedIds as $guardianId) {
                $queueData[] = [
                    'user_id' => $guardianId,
                    'guardian_message_id' => $message->id,
                    'status' => 'PENDING',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            GuardianMessageQueue::insert($queueData);

            DB::commit();
            
            // Clear session
            session()->forget('selected_guardians_' . $message->id);

            return redirect()->route('guardians.messages.index')->with('success', 'Bulk message created and queued for sending.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to create guardian message queue: ' . $e->getMessage());
            return back()->with('error', 'Failed to create message queue. Please try again.');
        }
    }

    /**
     * Display a listing of guardian messages.
     */
    public function messagesIndex()
    {
        if (!Auth::user()->hasPermission(10213)) {
            abort(403, 'You don\'t have permission to view guardian messages.');
        }

        $messages = GuardianMessage::withCount(['queue', 'queue as sent_count' => function($query) {
            $query->where('status', 'SENT');
        }])->orderBy('created_at', 'desc')->paginate(15);

        return view('guardians.messages_index', compact('messages'));
    }

    /**
     * Display the specified guardian message.
     */
    public function messagesShow($id)
    {
        if (!Auth::user()->hasPermission(10213)) {
            abort(403, 'You don\'t have permission to view guardian messages.');
        }

        $message = GuardianMessage::with(['queue.guardian'])->findOrFail($id);
        
        return view('guardians.messages_show', compact('message'));
    }
}
