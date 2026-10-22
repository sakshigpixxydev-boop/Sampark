<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\School;
use App\Models\SysUser;
use App\Models\Invoice;
use App\Models\PaymentLog;
use App\Models\Ticket;
use App\Models\Standard;
use App\Models\District;
use App\Models\UploadBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function __construct(
        private readonly \App\Services\RegistrationChannelStats $registrationStats
    ) {
    }

    /** Students who came in through a school link or a bulk import batch. */
    public const COHORT_SCHOOL = 'school';

    /** Students who registered themselves on the public form. */
    public const COHORT_REGULAR = 'regular';

    /**
     * Narrow a query over the student table to the header's cohort selection.
     *
     * Eloquent Student queries already carry this through the model's global
     * scope; this exists for raw builders and joins, which the scope cannot
     * reach.
     */
    private function applyCohort($query, ?string $cohort, string $table = 'student'): void
    {
        \App\Support\ViewCohort::apply($query, $cohort, $table);
    }

    /**
     * Student ids in the cohort, as a subquery for joins that only carry an id.
     */
    private function cohortStudentIds(?string $cohort): \Closure
    {
        return function ($sub) use ($cohort) {
            $sub->select('student_id')->from('student')->where('student.is_lead', 0);
            $this->applyCohort($sub, $cohort);
        };
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('Admin');
        $isRestricted = $user->hasRole('Initiator') || $user->hasRole('School');
        
        $schoolIds = $isRestricted ? $user->getAssignedSchoolIds() : [];
        $studentIds = $isRestricted ? $user->getAssignedStudentIds() : [];

        // Driven by the header dropdown, not by this page.
        $cohort = \App\Support\ViewCohort::current();
        $cohortStudentIds = $this->cohortStudentIds($cohort);

        // Registration overview tabs: same component, one array per channel.
        $registrationScope = $user->restrictedSchoolIds();
        $registrationChannels = [
            \App\Services\RegistrationChannelStats::CHANNEL_SCHOOL => $this->registrationStats
                ->forChannel(\App\Services\RegistrationChannelStats::CHANNEL_SCHOOL, $registrationScope),
            \App\Services\RegistrationChannelStats::CHANNEL_REGULAR => $this->registrationStats
                ->forChannel(\App\Services\RegistrationChannelStats::CHANNEL_REGULAR, $registrationScope),
        ];

        // Common Filters
        $filterStandard = $request->sem_standard;
        $filterSchool = $request->sem_school;
        $filterDistrict = $request->sem_district;

        // Total Students
        $totalStudentsQuery = Student::query();
        $this->applyCohort($totalStudentsQuery, $cohort);
        if ($isRestricted) {
            $totalStudentsQuery->whereIn('student_id', $studentIds);
        }
        $totalStudents = $totalStudentsQuery->count();

        // Active Students (Unique daily login student users today)
        $activeStudentsTodayQuery = DB::table('audit_logs')
            ->join('userstudent', 'audit_logs.user_id', '=', 'userstudent.sysuser_id')
            ->where('audit_logs.event_type', 'LOGIN_SUCCESS')
            ->whereDate('audit_logs.created_at', today());

        if ($cohort !== null) {
            $activeStudentsTodayQuery->whereIn('userstudent.student_id', $cohortStudentIds);
        }
        if ($isRestricted) {
            $activeStudentsTodayQuery->whereIn('userstudent.student_id', $studentIds);
        }
        $activeStudents = $activeStudentsTodayQuery->distinct('userstudent.student_id')->count();

        // Pending Students (assuming 'Documents Submitted' application_status)
        $pendingStudentsQuery = Student::where('application_status', 'Documents Submitted');
        $this->applyCohort($pendingStudentsQuery, $cohort);
        if ($isRestricted) {
            $pendingStudentsQuery->whereIn('student_id', $studentIds);
        }
        $pendingStudents = $pendingStudentsQuery->count();

        // Approved Today Students (Students approved/enrolled today)
        $approvedTodayQuery = Student::whereIn('application_status', ['Enrolled', 'Approved'])
            ->whereDate('updated_at', today());
        $this->applyCohort($approvedTodayQuery, $cohort);
        if ($isRestricted) {
            $approvedTodayQuery->whereIn('student_id', $studentIds);
        }
        $approvedToday = $approvedTodayQuery->count();

        // Total Schools
        $totalSchoolsQuery = School::query();
        if ($isRestricted) {
            $totalSchoolsQuery->whereIn('school_id', $schoolIds);
        }
        $totalSchools = $totalSchoolsQuery->count();

        // Today's Payment
        $todaysPaymentQuery = PaymentLog::whereDate('processdatetime', today())->whereIn('status', ['1']);
        if ($cohort !== null) {
            $todaysPaymentQuery->whereHas('invoice.student', fn ($q) => $this->applyCohort($q, $cohort));
        }
        if ($isRestricted) {
            $todaysPaymentQuery->whereHas('invoice', function($q) use ($schoolIds) {
                $q->whereIn('school_id', $schoolIds);
            });
        }
        $todaysPayment = $todaysPaymentQuery->sum('amount');

        // Pending Fees Calculation:
        // 1. Pending Invoices (ISSUED, PARTIAL, DRAFT)
        $pendingInvoicesQuery = Invoice::whereIn('status', ['ISSUED', 'PARTIAL', 'DRAFT']);
        if ($cohort !== null) {
            $pendingInvoicesQuery->whereHas('student', fn ($q) => $this->applyCohort($q, $cohort));
        }
        if ($isRestricted) {
            $pendingInvoicesQuery->whereIn('school_id', $schoolIds);
        }
        $pendingInvoicesSum = (float) $pendingInvoicesQuery->sum('total_due');

        // 2. Pending Student Fees (for students whose fees_status is 'Pending')
        $pendingStudentsFeeQuery = Student::where('fees_status', 'Pending');
        $this->applyCohort($pendingStudentsFeeQuery, $cohort);
        if ($isRestricted) {
            $pendingStudentsFeeQuery->whereIn('student_id', $studentIds);
        }
        $pendingStudentsList = $pendingStudentsFeeQuery->get();
        $pendingStudentsFeeSum = 0.0;
        foreach ($pendingStudentsList as $st) {
            $feePlan = \App\Models\FeePlan::where('fee_name', $st->subscription)->first();
            $pendingStudentsFeeSum += $feePlan ? (float) $feePlan->amount : 4000.0; // Fallback to 4000
        }

        // 3. Pending Upload Batches Fees
        // Upload batches only exist for the school cohort.
        $pendingBatchesFeesQuery = UploadBatch::where('status', 'pending');
        if ($cohort === self::COHORT_REGULAR) {
            $pendingBatchesFeesQuery->whereRaw('1 = 0');
        }
        if ($isRestricted) {
            $pendingBatchesFeesQuery->whereIn('school_id', $schoolIds);
        }
        $pendingBatchesFeesSum = (float) $pendingBatchesFeesQuery->sum('total_fees');

        // Total Pending Fees
        $pendingFees = $pendingInvoicesSum + $pendingStudentsFeeSum + $pendingBatchesFeesSum;

        // Completed Fees for Pie Chart
        $completedFeesQuery = Invoice::where('status', 'PAID');
        if ($cohort !== null) {
            $completedFeesQuery->whereHas('student', fn ($q) => $this->applyCohort($q, $cohort));
        }
        if ($isRestricted) {
            $completedFeesQuery->whereIn('school_id', $schoolIds);
        }
        $completedFees = (float) $completedFeesQuery->sum('total_due');

        $feesPieData = [
            'pending' => (float) $pendingFees,
            'completed' => (float) $completedFees,
        ];

        // Standard Wise Students Count (Filtered)
        // Raw query: the Student model's excludeLeads global scope does not apply here.
        $semWiseDataQuery = DB::table('student')
            ->join('ms_standard', 'student.std_id', '=', 'ms_standard.std_id')
            ->where('student.is_lead', 0)
            ->select('ms_standard.stdname as name', DB::raw('count(student.student_id) as value'));

        $this->applyCohort($semWiseDataQuery, $cohort);
        if ($isRestricted) {
            $semWiseDataQuery->whereIn('student.student_id', $studentIds);
        }
        
        if ($request->has('sem_standard') && $request->sem_standard) {
            $semWiseDataQuery->where('student.std_id', $request->sem_standard);
        }
        if ($request->has('sem_school') && $request->sem_school) {
            $semWiseDataQuery->where('student.school_id', $request->sem_school);
        }
        if ($request->has('sem_district') && $request->sem_district) {
            $semWiseDataQuery->where('student.dist_id', $request->sem_district);
        }

        $semWiseData = $semWiseDataQuery->groupBy('ms_standard.stdname', 'ms_standard.std_id')
            ->orderBy('ms_standard.std_id')
            ->get();

        // Recent Payments
        $recentPaymentsQuery = PaymentLog::with(['invoice.student', 'invoice.school'])->orderBy('payment_id', 'desc')->limit(10);
        if ($cohort !== null) {
            $recentPaymentsQuery->whereHas('invoice.student', fn ($q) => $this->applyCohort($q, $cohort));
        }
        if ($isRestricted) {
            $recentPaymentsQuery->whereHas('invoice', function($q) use ($schoolIds) {
                $q->whereIn('school_id', $schoolIds);
            });
        }
        $recentPayments = $recentPaymentsQuery->get();

        // Recent Activity Logs
        $recentLogsQuery = \App\Models\AuditLog::with(['user', 'auditable'])->orderBy('created_at', 'desc')->limit(10);
        if ($isRestricted) {
            $recentLogsQuery->where(function($q) use ($studentIds, $schoolIds) {
                $q->where(function($q2) use ($studentIds) {
                    $q2->where('auditable_type', 'App\Models\Student')
                      ->whereIn('auditable_id', $studentIds);
                })
                ->orWhere(function($q2) use ($schoolIds) {
                    $q2->where('auditable_type', 'App\Models\School')
                      ->whereIn('auditable_id', $schoolIds);
                })
                ->orWhere(function($q2) use ($schoolIds) {
                    $q2->where('auditable_type', 'App\Models\PaymentLog')
                      ->whereIn('auditable_id', function($sub) use ($schoolIds) {
                          $sub->select('payment_id')->from('paymentlog')
                             ->join('invoices', 'paymentlog.invoice_id', '=', 'invoices.invoice_id')
                             ->whereIn('invoices.school_id', $schoolIds);
                      });
                })
                ->orWhereIn('user_id', function($sub) use ($schoolIds) {
                    $sub->select('sysuser_id')->from('sysuser')
                       ->join('userschool', 'sysuser.sysuser_id', '=', 'userschool.sysuser_id')
                       ->whereIn('userschool.school_id', $schoolIds);
                });
            });
        }
        $recentLogs = $recentLogsQuery->get();

        // Recent Tickets
        $recentTickets = Ticket::with(['school', 'category'])->forUser($user)->orderBy('ticket_id', 'desc')->limit(5)->get();

        // Filters data
        $schools = School::where('status', 1)->orderBy('schoolname')->get();
        if ($isRestricted) {
            $schools = $schools->whereIn('school_id', $schoolIds);
        }
        $standards = Standard::all();
        $districts = District::all();

        // Pending Registrations for Review
        $pendingReviewsQuery = Student::where('application_status', 'Documents Submitted');
        $this->applyCohort($pendingReviewsQuery, $cohort);
        if ($isRestricted) {
            $pendingReviewsQuery->whereIn('student_id', $studentIds);
        }
        $pendingReviews = $pendingReviewsQuery->count();

        // Accountant Batch Metrics (guarded by permission 10081)
        $pendingBatches = 0;
        $approvedToday = 0;
        $totalApproved = 0;
        $recentBatches = collect();

        // Batches belong to the school cohort; the regular tab shows none.
        if ($user->canAccessModule(10081) && $cohort !== self::COHORT_REGULAR) {
            $pendingBatches = UploadBatch::where('status', 'pending')->count();
            $totalApproved = UploadBatch::where('status', 'approved')->count();
            $recentBatches = UploadBatch::with(['semesterBatch.standard', 'uploader'])
                ->orderBy('upload_date', 'desc')
                ->limit(5)
                ->get();
        }

        // --- Dynamic Chart Data & Percentage Changes ---
        $days = (int) $request->get('days', 30);
        $days = in_array($days, [7, 30, 90, 365]) ? $days : 30;

        // Helper function for percentage
        $calcPercent = function($current, $previous) {
            if ($previous == 0 && $current == 0) return null;
            if ($previous == 0) return ['type' => 'increase', 'value' => 100];
            $change = (($current - $previous) / $previous) * 100;
            return [
                'type' => $change >= 0 ? 'increase' : 'decrease',
                'value' => abs(round($change, 1))
            ];
        };

        // Weekly Performance Data (Last 7 Days vs Prev 7 Days)
        $regCurrent = Student::where('created_at', '>=', today()->subDays(6));
        $regPrev = Student::whereBetween('created_at', [today()->subDays(13), today()->subDays(7)->endOfDay()]);
        
        // Active Students Trend (Unique daily login student users)
        $activeCurrentQuery = DB::table('audit_logs')
            ->join('userstudent', 'audit_logs.user_id', '=', 'userstudent.sysuser_id')
            ->where('audit_logs.event_type', 'LOGIN_SUCCESS')
            ->where('audit_logs.created_at', '>=', today()->subDays(6));
            
        $activePrevQuery = DB::table('audit_logs')
            ->join('userstudent', 'audit_logs.user_id', '=', 'userstudent.sysuser_id')
            ->where('audit_logs.event_type', 'LOGIN_SUCCESS')
            ->whereBetween('audit_logs.created_at', [today()->subDays(13), today()->subDays(7)->endOfDay()]);

        $pendingRevCurrent = Student::where('application_status', 'Documents Submitted')->where('updated_at', '>=', today()->subDays(6));
        $pendingRevPrev = Student::where('application_status', 'Documents Submitted')->whereBetween('updated_at', [today()->subDays(13), today()->subDays(7)->endOfDay()]);

        $revCurrent = PaymentLog::whereDate('processdatetime', today())->where('status', '1');
        $revPrev = PaymentLog::whereDate('processdatetime', today()->subDay())->where('status', '1');

        if ($cohort !== null) {
            foreach ([$regCurrent, $regPrev, $pendingRevCurrent, $pendingRevPrev] as $q) {
                $this->applyCohort($q, $cohort);
            }
            $activeCurrentQuery->whereIn('userstudent.student_id', $cohortStudentIds);
            $activePrevQuery->whereIn('userstudent.student_id', $cohortStudentIds);
            $revCurrent->whereHas('invoice.student', fn ($q) => $this->applyCohort($q, $cohort));
            $revPrev->whereHas('invoice.student', fn ($q) => $this->applyCohort($q, $cohort));
        }

        if ($isRestricted) {
            $regCurrent->whereIn('student_id', $studentIds); $regPrev->whereIn('student_id', $studentIds);
            $activeCurrentQuery->whereIn('userstudent.student_id', $studentIds);
            $activePrevQuery->whereIn('userstudent.student_id', $studentIds);
            $pendingRevCurrent->whereIn('student_id', $studentIds); $pendingRevPrev->whereIn('student_id', $studentIds);
            $revCurrent->whereHas('invoice', function($q) use ($schoolIds) { $q->whereIn('school_id', $schoolIds); });
            $revPrev->whereHas('invoice', function($q) use ($schoolIds) { $q->whereIn('school_id', $schoolIds); });
        }

        $regTrend = $calcPercent($regCurrent->count(), $regPrev->count());
        $activeTrend = $calcPercent(
            $activeCurrentQuery->distinct('userstudent.student_id')->count(),
            $activePrevQuery->distinct('userstudent.student_id')->count()
        );
        $pendingRevTrend = $calcPercent($pendingRevCurrent->count(), $pendingRevPrev->count());
        $revTrend = $calcPercent($revCurrent->sum('amount'), $revPrev->sum('amount'));

        // Sparklines Data generation (Last 7 Days)
        $sparklineDays = collect();
        for ($i = 6; $i >= 0; $i--) {
            $sparklineDays->push(today()->subDays($i)->format('Y-m-d'));
        }

        // Sparkline 1: Registrations
        $sparklineRegQuery = Student::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', today()->subDays(6))
            ->groupBy('date');
        $this->applyCohort($sparklineRegQuery, $cohort);
        if ($isRestricted) {
            $sparklineRegQuery->whereIn('student_id', $studentIds);
        }
        $sparklineRegData = $sparklineRegQuery->pluck('count', 'date');
        $sparkline1Data = $sparklineDays->map(function ($date) use ($sparklineRegData) {
            return $sparklineRegData->get($date, 0);
        })->toArray();

        // Sparkline 2: Active Students (Daily logins)
        $sparklineActiveQuery = DB::table('audit_logs')
            ->join('userstudent', 'audit_logs.user_id', '=', 'userstudent.sysuser_id')
            ->select(DB::raw('DATE(audit_logs.created_at) as date'), DB::raw('count(distinct userstudent.student_id) as count'))
            ->where('audit_logs.event_type', 'LOGIN_SUCCESS')
            ->where('audit_logs.created_at', '>=', today()->subDays(6))
            ->groupBy('date');
        if ($cohort !== null) {
            $sparklineActiveQuery->whereIn('userstudent.student_id', $cohortStudentIds);
        }
        if ($isRestricted) {
            $sparklineActiveQuery->whereIn('userstudent.student_id', $studentIds);
        }
        $sparklineActiveData = $sparklineActiveQuery->pluck('count', 'date');
        $sparkline2Data = $sparklineDays->map(function ($date) use ($sparklineActiveData) {
            return $sparklineActiveData->get($date, 0);
        })->toArray();

        // Sparkline 3: Pending Reviews
        $sparklinePendingQuery = Student::select(DB::raw('DATE(updated_at) as date'), DB::raw('count(*) as count'))
            ->where('application_status', 'Documents Submitted')
            ->where('updated_at', '>=', today()->subDays(6))
            ->groupBy('date');
        $this->applyCohort($sparklinePendingQuery, $cohort);
        if ($isRestricted) {
            $sparklinePendingQuery->whereIn('student_id', $studentIds);
        }
        $sparklinePendingData = $sparklinePendingQuery->pluck('count', 'date');
        $sparkline3Data = $sparklineDays->map(function ($date) use ($sparklinePendingData) {
            return $sparklinePendingData->get($date, 0);
        })->toArray();

        // Sparkline 4: Daily Revenue
        $sparklineRevQuery = PaymentLog::select(DB::raw('DATE(processdatetime) as date'), DB::raw('sum(amount) as total'))
            ->where('status', '1')
            ->where('processdatetime', '>=', today()->subDays(6))
            ->groupBy('date');
        if ($cohort !== null) {
            $sparklineRevQuery->whereHas('invoice.student', fn ($q) => $this->applyCohort($q, $cohort));
        }
        if ($isRestricted) {
            $sparklineRevQuery->whereHas('invoice', function($q) use ($schoolIds) {
                $q->whereIn('school_id', $schoolIds);
            });
        }
        $sparklineRevData = $sparklineRevQuery->pluck('total', 'date');
        $sparkline4Data = $sparklineDays->map(function ($date) use ($sparklineRevData) {
            return (float) $sparklineRevData->get($date, 0.0);
        })->toArray();

        // Chart 1: Daily Active Users (Last $days)
        $lastXDays = collect();
        for ($i = $days - 1; $i >= 0; $i--) {
            $lastXDays->push(today()->subDays($i)->format('Y-m-d'));
        }
        
        $activeUsersQuery = DB::table('audit_logs')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(distinct user_id) as count'))
            ->where('event_type', 'LOGIN_SUCCESS')
            ->where('created_at', '>=', today()->subDays($days - 1));
        if ($cohort !== null) {
            // Restrict to the sysusers behind this cohort's students.
            $activeUsersQuery->whereIn('user_id', function ($sub) use ($cohortStudentIds) {
                $sub->select('sysuser_id')->from('userstudent')
                    ->whereIn('student_id', $cohortStudentIds);
            });
        }
        if ($isRestricted) {
            $restrictedUserIds = DB::table('userstudent')
                ->whereIn('student_id', $studentIds)
                ->pluck('sysuser_id')
                ->toArray();
            $restrictedUserIds[] = $user->sysuser_id;
            $activeUsersQuery->whereIn('user_id', $restrictedUserIds);
        }
        $activeUsersQuery->groupBy('date');
        $activeUsersData = $activeUsersQuery->pluck('count', 'date');
        
        $dailyActiveUsers = $lastXDays->map(function ($date) use ($activeUsersData) {
            return $activeUsersData->get($date, 0);
        });
        $dailyActiveUsersLabels = $lastXDays->map(function ($date) use ($days) { 
            return Carbon::parse($date)->format($days > 30 ? 'M d' : 'd M'); 
        });
        $dailyActiveUsersAvg = round($dailyActiveUsers->avg() ?? 0, 0);

        // Chart 2: Registered, Pending, Enrolled
        $registrationsQuery = Student::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', today()->subDays($days - 1))
            ->groupBy('date');
        $this->applyCohort($registrationsQuery, $cohort);
        if ($isRestricted) { $registrationsQuery->whereIn('student_id', $studentIds); }
        $registrationsData = $registrationsQuery->pluck('count', 'date');
        $enrollmentsXDays = $lastXDays->map(function ($date) use ($registrationsData) { return $registrationsData->get($date, 0); });

        $pendingQuery = Student::select(DB::raw('DATE(updated_at) as date'), DB::raw('count(*) as count'))
            ->where(function($q) {
                $q->whereNotIn('application_status', ['Approved', 'Done', 'Enrolled', 'Rejected'])
                  ->orWhereNull('application_status');
            })
            ->where('updated_at', '>=', today()->subDays($days - 1))
            ->groupBy('date');
        $this->applyCohort($pendingQuery, $cohort);
        if ($isRestricted) { $pendingQuery->whereIn('student_id', $studentIds); }
        $pendingData = $pendingQuery->pluck('count', 'date');
        $pendingXDays = $lastXDays->map(function ($date) use ($pendingData) { return $pendingData->get($date, 0); });

        $enrollmentAppQuery = Student::select(DB::raw('DATE(updated_at) as date'), DB::raw('count(*) as count'))
            ->whereIn('application_status', ['Enrolled', 'Approved', 'Done'])
            ->where('updated_at', '>=', today()->subDays($days - 1))
            ->groupBy('date');
        $this->applyCohort($enrollmentAppQuery, $cohort);
        if ($isRestricted) { $enrollmentAppQuery->whereIn('student_id', $studentIds); }
        $enrollmentAppData = $enrollmentAppQuery->pluck('count', 'date');
        $approvalsXDays = $lastXDays->map(function ($date) use ($enrollmentAppData) { return $enrollmentAppData->get($date, 0); });
        
        $enrollmentsLabels = $dailyActiveUsersLabels;
        $enrollmentsTotalXDays = $enrollmentsXDays->sum();

        // Chart 3: Daily Registrations Chart
        $dailyRegistrations = $enrollmentsXDays;
        $dailyRegistrationsLabels = $dailyActiveUsersLabels;
        $dailyRegistrationsAvg = round($dailyRegistrations->avg() ?? 0, 0);

        return view('admin.dashboard', compact(
            'cohort',
            'registrationChannels',
            'totalStudents',
            'activeStudents',
            'pendingStudents',
            'pendingReviews',
            'totalSchools',
            'todaysPayment',
            'pendingFees',
            'feesPieData',
            'semWiseData',
            'recentPayments',
            'recentLogs',
            'recentTickets',
            'schools',
            'standards',
            'districts',
            'isAdmin',
            'pendingBatches',
            'approvedToday',
            'totalApproved',
            'recentBatches',
            'days',
            'regTrend',
            'activeTrend',
            'pendingRevTrend',
            'revTrend',
            'dailyActiveUsers',
            'dailyActiveUsersLabels',
            'dailyActiveUsersAvg',
            'dailyRegistrations',
            'dailyRegistrationsLabels',
            'dailyRegistrationsAvg',
            'enrollmentsXDays',
            'pendingXDays',
            'approvalsXDays',
            'enrollmentsLabels',
            'enrollmentsTotalXDays',
            'sparkline1Data',
            'sparkline2Data',
            'sparkline3Data',
            'sparkline4Data'
        ));
    }
}
