<?php

namespace App\Http\Controllers;

use App\Models\GlobalTemplate;
use App\Models\Student;
use App\Models\School;
use App\Models\District;
use App\Models\StudentAnnouncement;
use App\Models\StudentAnnouncementQueue;
use App\Models\SysUser;
use App\Models\UserRead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentAnnouncementController extends Controller
{
    /**
     * Display a listing of announcement history.
     */
    public function index()
    {
        if (!Auth::user()->hasPermission(10222)) {
            abort(403);
        }

        $announcements = StudentAnnouncement::withCount(['queue', 'queue as sent_count' => function($query) {
            $query->where('status', 'SENT');
        }])->orderBy('created_at', 'desc')->paginate(15);

        return view('students.announcements.index', compact('announcements'));
    }

    /**
     * Show the form for creating a new announcement.
     */
    public function create(Request $request)
    {
        if (!Auth::user()->hasPermission(10221)) {
            abort(403);
        }

        $templates = GlobalTemplate::where('is_active', true)->where('type', '!=', 'Event')->get();
        $schools = School::orderBy('schoolname')->get();
        $districts = District::orderBy('dname')->get();
        
        // Get all languages from the table
        $languages = DB::table('language')->select('lg_id', 'languagename')->orderBy('languagename')->get();
        
        // Get initiators (SysUsers with role Initiator)
        $initiators = SysUser::whereHas('role', function($q) {
            $q->where('rolename', 'Initiator');
        })->get();

        $selectedTemplate = null;
        if ($request->filled('template_id')) {
            $selectedTemplate = GlobalTemplate::find($request->template_id, ['*']);
            
            // Populate form with template defaults
            if ($selectedTemplate) {
                // Prioritize template filters if not manually set
                if ($selectedTemplate->filters && !$request->anyFilled(['school_id', 'dist_id', 'language', 'initiator_contact'])) {
                    $request->merge($selectedTemplate->filters);
                }
                
                // Prioritize template type if not manually set
                if (!$request->has('communication_type')) {
                    $request->merge(['communication_type' => $selectedTemplate->type === 'Both' ? 'Email' : $selectedTemplate->type]);
                }
            }
        }

        $reusedAnnouncement = null;
        if ($request->filled('reused_id')) {
            $reusedAnnouncement = StudentAnnouncement::find($request->reused_id);
            if ($reusedAnnouncement && !$selectedTemplate) {
                // Merge communication type from reused announcement if not manually set and no template
                if (!$request->has('communication_type')) {
                    $request->merge(['communication_type' => $reusedAnnouncement->communication_type]);
                }
                // Merge filters from reused announcement if not manually set and no template
                if ($reusedAnnouncement->filters && !$request->anyFilled(['school_id', 'dist_id', 'language', 'initiator_contact'])) {
                    $request->merge($reusedAnnouncement->filters);
                }
            }
        }

        // Student filtering logic for the grid
        $query = $this->applyStudentFilters($request);
        $students = $query->paginate(15)->appends($request->all());

        if ($request->ajax()) {
            return view('students.announcements.partials.student_grid', compact('students'))->render();
        }

        return view('students.announcements.create', compact(
            'templates', 'schools', 'districts', 'languages', 'initiators', 'selectedTemplate', 'students', 'reusedAnnouncement'
        ));
    }

    /**
     * Apply common student filters.
     */
    private function applyStudentFilters(Request $request)
    {
        $query = Student::query();

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }
        if ($request->filled('dist_id')) {
            $query->where('dist_id', $request->dist_id);
        }
        if ($request->filled('language')) {
            $query->where('materiallanguage', $request->language);
        }
        if ($request->filled('initiator_contact')) {
            $query->where('initiator_contact', $request->initiator_contact);
        }

        return $query;
    }

    /**
     * Store a newly created announcement and queue it.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->hasPermission(10221)) {
            abort(403);
        }

        $request->validate([
            'template_id' => 'nullable|exists:global_templates,id',
            'communication_type' => 'required|in:Email,WhatsApp,SMS',
            'subject' => 'required_if:communication_type,Email|string|max:255',
            'message' => 'required|string',
            'selected_students' => 'required_without:select_all_pages|array',
            'select_all_pages' => 'nullable|boolean',
            'attachment' => 'nullable|file|max:10240', // 10MB limit
        ]);

        $studentIds = $request->selected_students;
        if ($request->select_all_pages) {
            $query = $this->applyStudentFilters($request);
            if ($request->filled('excluded_students')) {
                $query->whereNotIn('student_id', $request->excluded_students);
            }
            $studentIds = $query->pluck('student_id')->toArray();
        }

        if (empty($studentIds)) {
            return back()->with('error', 'No students selected or found with current filters.')->withInput();
        }

        DB::beginTransaction();
        try {
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('announcements/attachments', 'public');
            }

            $announcement = StudentAnnouncement::create([
                'template_id' => $request->template_id,
                'filters' => [
                    'school_id' => $request->school_id,
                    'dist_id' => $request->dist_id,
                    'language' => $request->language,
                    'initiator_contact' => $request->initiator_contact,
                ],
                'subject' => $request->subject,
                'message' => $request->message,
                'communication_type' => $request->communication_type,
                'status' => 'READY',
                'attachment' => $attachmentPath,
                'created_by' => Auth::user()->username,
            ]);

            $queueData = [];
            $now = now();
            foreach ($studentIds as $studentId) {
                $queueData[] = [
                    'announcement_id' => $announcement->id,
                    'student_id' => $studentId,
                    'status' => 'PENDING',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            StudentAnnouncementQueue::insert($queueData);

            DB::commit();

            return redirect()->route('students.announcements.index')
                ->with('success', 'Announcement scheduled successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to schedule announcement: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Reuse a saved announcement configuration.
     */
    public function reuse($id)
    {
        if (!Auth::user()->hasPermission(10221)) {
            abort(403);
        }

        $announcement = StudentAnnouncement::findOrFail($id);
        
        // Pass the filters back to the create page
        return redirect()->route('students.announcements.create', [
            'template_id' => $announcement->template_id,
            'school_id' => $announcement->filters['school_id'] ?? null,
            'dist_id' => $announcement->filters['dist_id'] ?? null,
            'language' => $announcement->filters['language'] ?? null,
            'initiator_contact' => $announcement->filters['initiator_contact'] ?? null,
            'communication_type' => $announcement->communication_type,
            'reused_id' => $announcement->id
        ]);
    }
    
    /**
     * Show details of an announcement.
     */
    public function show($id)
    {
        if (!Auth::user()->hasPermission(10222)) {
            abort(403);
        }

        $announcement = StudentAnnouncement::with(['queue.student', 'template'])->findOrFail($id);
        
        // Resolve human-readable names for filters
        $filterNames = [];
        if (!empty($announcement->filters)) {
            $keyMap = [
                'school_id' => 'School',
                'dist_id' => 'District',
                'language' => 'Language',
                'initiator_contact' => 'Initiator'
            ];
            foreach ($announcement->filters as $key => $value) {
                if (!$value) continue;
                
                $resolvedValue = $value;
                if ($key === 'school_id') {
                    $resolvedValue = School::find($value)?->schoolname ?? $value;
                } elseif ($key === 'dist_id') {
                    $resolvedValue = District::find($value)?->dname ?? $value;
                } elseif ($key === 'language') {
                    $resolvedValue = DB::table('language')->where('lg_id', $value)->value('languagename') ?? $value;
                }
                
                $label = $keyMap[$key] ?? ucfirst(str_replace('_', ' ', $key));
                $filterNames[$label] = $resolvedValue;
            }
        }
        
        // Mark as read
        UserRead::firstOrCreate([
            'sysuser_id' => Auth::id(),
            'readable_id' => $id,
            'readable_type' => 'Announcement',
        ]);

        return view('students.announcements.show', compact('announcement', 'filterNames'));
    }
}
