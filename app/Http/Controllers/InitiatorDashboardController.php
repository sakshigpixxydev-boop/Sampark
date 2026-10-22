<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\School;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InitiatorDashboardController extends Controller
{
    protected $financialService;

    public function __construct(\App\Services\InitiatorFinancialService $financialService)
    {
        $this->financialService = $financialService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        // Get assigned school IDs
        $assignedSchoolIds = $user->getAssignedSchoolIds();

        // Get assigned student IDs
        $assignedStudentIds = $user->getAssignedStudentIds();

        // Calculate overview metrics (only for assigned scope)
        $totalStudents = count($assignedStudentIds);
        $totalSchools = count($assignedSchoolIds);

        // Get pending payments with optional school filter (only assigned schools)
        $pendingPaymentsQuery = Invoice::with(['student', 'school'])
            ->whereIn('status', ['ISSUED', 'PARTIAL'])
            ->where(function ($query) use ($assignedSchoolIds, $assignedStudentIds) {
                $query->whereIn('school_id', $assignedSchoolIds)
                    ->orWhereIn('student_id', $assignedStudentIds);
            })
            ->orderBy('invoice_id', 'desc');

        if ($request->has('school_filter') && $request->school_filter) {
            $pendingPaymentsQuery->where('school_id', $request->school_filter);
        }

        $pendingPayments = $pendingPaymentsQuery->limit(10)->get();

        // Get new inquiries with payment status filter (only assigned students)
        $newInquiriesQuery = Student::with(['school', 'invoices.payments'])
            ->whereIn('student_id', $assignedStudentIds)
            ->orderBy('student_id', 'desc');

        if ($request->has('payment_status') && $request->payment_status) {
            $status = $request->payment_status;
            $newInquiriesQuery->whereHas('invoices.payments', function ($query) use ($status) {
                $query->where('status', $status);
            });
        }

        $newInquiries = $newInquiriesQuery->limit(10)->get();

        // Get assigned schools for filter dropdown (only approved ones)
        $schools = School::whereIn('school_id', $assignedSchoolIds)->where('status', 1)->orderBy('schoolname')->get();

        // Calculate earnings for paid initiators
        $earnings = [
            'total' => 0,
            'breakdown' => []
        ];
        if ($user->isPaidInitiator()) {
            $earnings = $this->financialService->calculateEarnings($user);
        }

        return view('initiator.dashboard', compact(
            'totalStudents',
            'totalSchools',
            'pendingPayments',
            'newInquiries',
            'schools',
            'earnings'
        ));
    }

    public function earnings(\Illuminate\Http\Request $request)
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        if (!$user->isPaidInitiator()) {
            return redirect()->route('initiator.dashboard')->with('error', 'Only paid initiators can view this page.');
        }

        $earnings = $this->financialService->getDetailedEarnings($user);

        return view('initiator.earnings', compact('earnings'));
    }
}
