<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Services\AuditLoggerService;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    protected AuditLoggerService $auditLogger;

    public function __construct(AuditLoggerService $auditLogger)
    {
        $this->auditLogger = $auditLogger;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $isRestricted = $user->hasRole('Initiator') || $user->hasRole('School');
        $schoolIds = $isRestricted ? $user->getAssignedSchoolIds() : [];
        $studentIds = $isRestricted ? $user->getAssignedStudentIds() : [];

        $query = AuditLog::with('user')->orderBy('created_at', 'desc');

        // Apply data isolation restrictions if restricted
        if ($isRestricted) {
            $query->where(function($q) use ($studentIds, $schoolIds) {
                // Student logs
                $q->where(function($q2) use ($studentIds) {
                    $q2->where('auditable_type', 'App\Models\Student')
                      ->whereIn('auditable_id', $studentIds);
                })
                // School logs
                ->orWhere(function($q2) use ($schoolIds) {
                    $q2->where('auditable_type', 'App\Models\School')
                      ->whereIn('auditable_id', $schoolIds);
                })
                // PaymentLog records associated with students/schools
                ->orWhere(function($q2) use ($schoolIds) {
                    $q2->where('auditable_type', 'App\Models\PaymentLog')
                      ->whereIn('auditable_id', function($sub) use ($schoolIds) {
                          $sub->select('payment_id')->from('paymentlog')
                             ->join('invoices', 'paymentlog.invoice_id', '=', 'invoices.invoice_id')
                             ->whereIn('invoices.school_id', $schoolIds);
                      });
                })
                // Action logs created by users assigned to those schools
                ->orWhereIn('user_id', function($sub) use ($schoolIds) {
                    $sub->select('sysuser_id')->from('sysuser')
                       ->join('userschool', 'sysuser.sysuser_id', '=', 'userschool.sysuser_id')
                       ->whereIn('userschool.school_id', $schoolIds);
                });
            });
        }

        // Filter by event type
        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by model type (auditable_type)
        if ($request->filled('auditable_type')) {
            $query->where('auditable_type', $request->auditable_type);
        }

        // Filter by specific student
        if ($request->filled('student_id')) {
            $query->where('auditable_type', 'App\Models\Student')
                  ->where('auditable_id', $request->student_id);
        }

        // Search by student name/email/phone (Student-wise search)
        if ($request->filled('student_search')) {
            $studentSearch = $request->student_search;
            $searchStudentQuery = \App\Models\Student::query();
            if ($isRestricted) {
                $searchStudentQuery->whereIn('student_id', $studentIds);
            }
            $searchedStudentIds = $searchStudentQuery->where(function($q) use ($studentSearch) {
                $q->where('fname', 'like', "%{$studentSearch}%")
                  ->orWhere('lname', 'like', "%{$studentSearch}%")
                  ->orWhere('email', 'like', "%{$studentSearch}%")
                  ->orWhere('contact_no', 'like', "%{$studentSearch}%");
            })->pluck('student_id');

            $query->where(function($q) use ($searchedStudentIds, $studentSearch) {
                $q->where(function($q2) use ($searchedStudentIds) {
                    $q2->where('auditable_type', 'App\Models\Student')
                      ->whereIn('auditable_id', $searchedStudentIds);
                })
                ->orWhere('description', 'like', "%{$studentSearch}%")
                ->orWhere('metadata', 'like', "%{$studentSearch}%");
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search in description
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $logs = $query->paginate(50)->withQueryString();

        // Get unique event types for filter
        $eventTypesQuery = AuditLog::select('event_type')->distinct();
        if ($isRestricted) {
            $eventTypesQuery->whereIn('user_id', function($sub) use ($schoolIds) {
                $sub->select('sysuser_id')->from('sysuser')
                   ->join('userschool', 'sysuser.sysuser_id', '=', 'userschool.sysuser_id')
                   ->whereIn('userschool.school_id', $schoolIds);
            });
        }
        $eventTypes = $eventTypesQuery->orderBy('event_type')->pluck('event_type');

        // Get unique model types for filter
        $modelTypesQuery = AuditLog::select('auditable_type')
            ->distinct()
            ->whereNotNull('auditable_type');
        if ($isRestricted) {
            $modelTypesQuery->where(function($q) use ($studentIds, $schoolIds) {
                $q->where(function($q2) use ($studentIds) {
                    $q2->where('auditable_type', 'App\Models\Student')
                      ->whereIn('auditable_id', $studentIds);
                })->orWhere(function($q2) use ($schoolIds) {
                    $q2->where('auditable_type', 'App\Models\School')
                      ->whereIn('auditable_id', $schoolIds);
                });
            });
        }
        $modelTypes = $modelTypesQuery->orderBy('auditable_type')->pluck('auditable_type');

        // Get list of users who did actions
        $usersQuery = \App\Models\SysUser::query();
        if ($isRestricted) {
            $usersQuery->whereIn('sysuser_id', function($sub) use ($schoolIds) {
                $sub->select('sysuser_id')->from('sysuser')
                   ->join('userschool', 'sysuser.sysuser_id', '=', 'userschool.sysuser_id')
                   ->whereIn('userschool.school_id', $schoolIds);
            });
        }
        $users = $usersQuery->orderBy('fname')->get();

        // Get list of students for selection
        $studentsQuery = \App\Models\Student::query();
        if ($isRestricted) {
            $studentsQuery->whereIn('student_id', $studentIds);
        }
        $students = $studentsQuery->orderBy('fname')->get();

        return view('audit-logs.index', compact('logs', 'eventTypes', 'modelTypes', 'users', 'students'));
    }

    /**
     * Display the specified audit log
     */
    public function show(AuditLog $auditLog)
    {
        $auditLog->load('user');
        return view('audit-logs.show', compact('auditLog'));
    }

    /**
     * Get audit logs for a specific model
     */
    public function forModel(Request $request)
    {
        $request->validate([
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
        ]);

        $logs = AuditLog::forModel($request->model_type, $request->model_id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'logs' => $logs,
        ]);
    }

    /**
     * Export audit logs
     */
    public function export(Request $request)
    {
        // This would implement CSV/Excel export functionality
        // For now, return a simple response
        return response()->json([
            'message' => 'Export functionality to be implemented',
        ]);
    }
}
