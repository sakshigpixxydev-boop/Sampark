<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentGrowthRecord;
use App\Models\School;
use App\Models\GrowthCriterion;
use App\Http\Traits\HandlesErrors;
use Illuminate\Http\Request;
use App\Helpers\DateHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentGrowthController extends Controller
{
    use HandlesErrors;

    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $isInitiatorUser = ($user->role->rolename === 'Initiator');
            
            // Access control: Read Growth Card (10501)
            if (!$user->hasPermission(10501)) {
                abort(403, 'You don\'t have permission to access Growth Card Module.');
            }

            $query = StudentGrowthRecord::with(['student', 'school', 'creator']);

            // Role restrictions
            if ($isInitiatorUser) {
                $assignedStudentIds = $user->getAssignedStudentIds();
                $query->whereIn('student_id', $assignedStudentIds);
            }

            // Filtering
            if ($request->filled('school_id')) {
                $query->where('school_id', $request->school_id);
            }
            if ($request->filled('student_id')) {
                $query->where('student_id', $request->student_id);
            }

            $records = $query->orderBy('entry_date', 'desc')->paginate(15)->withQueryString();

            // Options for filters
            if ($isInitiatorUser) {
                $schoolIds = $user->getAssignedSchoolIds();
                $schools = School::whereIn('school_id', $schoolIds)->where('status', 1)->orderBy('schoolname')->get();
            } else {
                $schools = School::where('status', 1)->orderBy('schoolname')->get();
            }

            return view('student-growth.index', compact('records', 'schools'));
        } catch (\Exception $e) {
            return $this->handleError($e, 'Failed to load growth records.');
        }
    }

    public function create(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Access control: Write Growth Card (10502)
            if (!$user->hasPermission(10502)) {
                abort(403, 'You don\'t have permission to enter growth marks.');
            }

            $isInitiatorUser = ($user->role->rolename === 'Initiator');

            // Get assigned schools
            if ($isInitiatorUser) {
                $schoolIds = $user->getAssignedSchoolIds();
                $schools = School::whereIn('school_id', $schoolIds)->where('status', 1)->orderBy('schoolname')->get();
            } else {
                $schools = School::where('status', 1)->orderBy('schoolname')->get();
            }

            $selectedSchoolId = $request->get('school_id');
            $students = [];
            if ($selectedSchoolId) {
                $studentsQuery = Student::where('school_id', $selectedSchoolId);
                if ($isInitiatorUser) {
                    $assignedStudentIds = $user->getAssignedStudentIds();
                    $studentsQuery->whereIn('student_id', $assignedStudentIds);
                }
                $students = $studentsQuery->orderBy('fname')->get();
            }

            $selectedStudentId = $request->get('student_id');
            $student = $selectedStudentId ? Student::allCohorts()->find($selectedStudentId) : null;

            $criteria = GrowthCriterion::where('status', 1)->orderBy('display_order')->get();

            return view('student-growth.create', compact('schools', 'students', 'student', 'selectedSchoolId', 'selectedStudentId', 'criteria'));
        } catch (\Exception $e) {
            return $this->handleError($e, 'Failed to load entry form.');
        }
    }

    public function store(Request $request)
    {
        // Access control: Write Growth Card (10502)
        if (!Auth::user()->hasPermission(10502)) {
            abort(403, 'You don\'t have permission to enter growth marks.');
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:student,student_id',
            'school_id' => 'required|exists:school,school_id',
            'entry_date' => 'required|date_format_dmy',
            'criteria' => 'required|array',
            'criteria.*' => 'required|numeric|min:0|max:10',
            'educator_strength' => 'nullable|string',
            'educator_growth_area' => 'nullable|string',
            'educator_next_step' => 'nullable|string',
            'guardian_feedback' => 'nullable|string',
            'student_self_reflection' => 'nullable|string',
        ]);

        if (isset($validated['entry_date'])) {
            $validated['entry_date'] = DateHelper::convertDateFormat($validated['entry_date']);
        }


        try {
            $validated['created_by'] = Auth::id();
            $validated['scores'] = $request->criteria;
            $validated['reflections'] = [
                'educator_strength' => $request->educator_strength,
                'educator_growth_area' => $request->educator_growth_area,
                'educator_next_step' => $request->educator_next_step,
                'guardian_feedback' => $request->guardian_feedback,
                'student_self_reflection' => $request->student_self_reflection,
            ];
            
            // Map scores to old columns for backwards compatibility if they match by name
            $activeCriteria = GrowthCriterion::whereIn('criterion_id', array_keys($request->criteria))->get();
            $mapping = [
                'Discipline' => 'discipline',
                'Leadership' => 'leadership',
                'Communication' => 'communication',
                'Teamwork' => 'teamwork',
                'Participation' => 'participation',
                'Behaviour' => 'behaviour'
            ];
            
            foreach ($activeCriteria as $criterion) {
                if (isset($mapping[$criterion->criterion_name])) {
                    $col = $mapping[$criterion->criterion_name];
                    $validated[$col] = $request->criteria[$criterion->criterion_id];
                }
            }
            
            $record = StudentGrowthRecord::create($validated);


            return redirect()->route('student-growth.show', $record->growth_id)
                ->with('success', 'Growth record saved successfully.');
        } catch (\Exception $e) {
            return $this->handleError($e, 'Failed to save growth record.');
        }
    }

    public function edit(StudentGrowthRecord $student_growth)
    {
        $record = $student_growth;
        try {
            $user = Auth::user();
            
            // Access control: Write Growth Card (10502)
            if (!$user->hasPermission(10502)) {
                abort(403, 'You don\'t have permission to edit growth marks.');
            }

            // Role restriction for Initiator
            if ($user->role->rolename === 'Initiator') {
                $assignedStudentIds = $user->getAssignedStudentIds();
                if (!in_array($record->student_id, $assignedStudentIds)) {
                    abort(403, 'Unauthorized access to edit student growth data.');
                }
            }

            $record->load(['student', 'school']);
            $criteria = GrowthCriterion::where('status', 1)->orderBy('display_order')->get();

            return view('student-growth.edit', compact('record', 'criteria'));
        } catch (\Exception $e) {
            return $this->handleError($e, 'Failed to load edit form.');
        }
    }

    public function update(Request $request, StudentGrowthRecord $student_growth)
    {
        $record = $student_growth;
        
        // Access control: Write Growth Card (10502)
        if (!Auth::user()->hasPermission(10502)) {
            abort(403, 'You don\'t have permission to update growth marks.');
        }

        // Role restriction
        if (Auth::user()->role->rolename === 'Initiator') {
            $assignedStudentIds = Auth::user()->getAssignedStudentIds();
            if (!in_array($record->student_id, $assignedStudentIds)) {
                abort(403, 'Unauthorized access to update student growth data.');
            }
        }

        $validated = $request->validate([
            'entry_date' => 'required|date_format_dmy',
            'criteria' => 'required|array',
            'criteria.*' => 'required|numeric|min:0|max:10',
            'educator_strength' => 'nullable|string',
            'educator_growth_area' => 'nullable|string',
            'educator_next_step' => 'nullable|string',
            'guardian_feedback' => 'nullable|string',
            'student_self_reflection' => 'nullable|string',
        ]);

        if (isset($validated['entry_date'])) {
            $validated['entry_date'] = DateHelper::convertDateFormat($validated['entry_date']);
        }

        try {
            $validated['scores'] = $request->criteria;
            $validated['reflections'] = [
                'educator_strength' => $request->educator_strength,
                'educator_growth_area' => $request->educator_growth_area,
                'educator_next_step' => $request->educator_next_step,
                'guardian_feedback' => $request->guardian_feedback,
                'student_self_reflection' => $request->student_self_reflection,
            ];
            
            // Map scores to old columns for backwards compatibility if they match by name
            $activeCriteria = GrowthCriterion::whereIn('criterion_id', array_keys($request->criteria))->get();
            $mapping = [
                'Discipline' => 'discipline',
                'Leadership' => 'leadership',
                'Communication' => 'communication',
                'Teamwork' => 'teamwork',
                'Participation' => 'participation',
                'Behaviour' => 'behaviour'
            ];
            
            foreach ($activeCriteria as $criterion) {
                if (isset($mapping[$criterion->criterion_name])) {
                    $col = $mapping[$criterion->criterion_name];
                    $validated[$col] = $request->criteria[$criterion->criterion_id];
                }
            }
            
            $record->update($validated);

            return redirect()->route('student-growth.show', $record->growth_id)
                ->with('success', 'Growth record updated successfully.');
        } catch (\Exception $e) {
            return $this->handleError($e, 'Failed to update growth record.');
        }
    }

    public function destroy(StudentGrowthRecord $student_growth)
    {
        $record = $student_growth;
        
        // Access control: Write Growth Card (10502) - Assuming write permission implies delete
        if (!Auth::user()->hasPermission(10502)) {
            abort(403, 'You don\'t have permission to delete growth marks.');
        }

        // Role restriction
        if (Auth::user()->role->rolename === 'Initiator') {
            $assignedStudentIds = Auth::user()->getAssignedStudentIds();
            if (!in_array($record->student_id, $assignedStudentIds)) {
                abort(403, 'Unauthorized access to delete student growth data.');
            }
        }

        try {
            $record->delete();
            return redirect()->route('student-growth.index')
                ->with('success', 'Growth record deleted successfully.');
        } catch (\Exception $e) {
            return $this->handleError($e, 'Failed to delete growth record.');
        }
    }


    public function show(StudentGrowthRecord $student_growth)
    {
        $record = $student_growth;
        try {
            $user = Auth::user();
            if (!$user->hasPermission(10501)) {
                abort(403, 'You don\'t have permission to view Growth Card.');
            }

            // Role restriction
            if ($user->role->rolename === 'Initiator') {
                $assignedStudentIds = $user->getAssignedStudentIds();
                if (!in_array($record->student_id, $assignedStudentIds)) {
                    abort(403, 'Unauthorized access to student growth data.');
                }
            }

            $record->load(['student', 'school', 'creator']);
            
            $criteria = GrowthCriterion::orderBy('display_order')->get();
            
            return view('student-growth.show', compact('record', 'criteria'));
        } catch (\Exception $e) {
            return $this->handleError($e, 'Failed to load growth card.');
        }
    }

    public function studentGrowth(Student $student)
    {
        try {
            $user = Auth::user();
            if (!$user->hasPermission(10501)) {
                abort(403, 'You don\'t have permission to view Growth Card.');
            }

            // Role restriction
            if ($user->role->rolename === 'Initiator') {
                $assignedStudentIds = $user->getAssignedStudentIds();
                if (!in_array($student->student_id, $assignedStudentIds)) {
                    abort(403, 'Unauthorized access to student growth data.');
                }
            }

            $student->load('school');
            $records = StudentGrowthRecord::with(['school'])
                ->where('student_id', $student->student_id)
                ->orderBy('entry_date', 'desc')
                ->get();

            $criteria = GrowthCriterion::orderBy('display_order')->get();

            return view('student-growth.student-growth', compact('student', 'records', 'criteria'));
        } catch (\Exception $e) {
            return $this->handleError($e, 'Failed to load student growth overview.');
        }
    }
}
