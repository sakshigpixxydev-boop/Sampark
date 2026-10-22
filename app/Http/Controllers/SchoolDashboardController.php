<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchoolDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get the school ID(s) associated with this user
        $schoolIds = $user->getAssignedSchoolIds();
        
        // If user has no schools assigned, use empty array
        if (empty($schoolIds)) {
            $schoolIds = [0]; // Non-existent ID to return empty results
        }
        
        // Calculate overview metrics (only for school's students)
        $totalStudents = Student::whereIn('school_id', $schoolIds)->count();
        
        // Get pending payments (only for school's students)
        $pendingPaymentsQuery = Invoice::with(['student', 'school'])
            ->whereIn('status', ['ISSUED', 'PARTIAL'])
            ->whereIn('school_id', $schoolIds)
            ->orderBy('invoice_id', 'desc');
        
        $pendingPayments = $pendingPaymentsQuery->limit(10)->get();
        
        // Get new inquiries with payment status filter (only school's students)
        $newInquiriesQuery = Student::with(['school', 'invoices.payments'])
            ->whereIn('school_id', $schoolIds)
            ->orderBy('student_id', 'desc');
        
        if ($request->has('payment_status') && $request->payment_status) {
            $status = $request->payment_status;
            $newInquiriesQuery->whereHas('invoices.payments', function($query) use ($status) {
                $query->where('status', $status);
            });
        }
        
        $newInquiries = $newInquiriesQuery->limit(10)->get();
        
        return view('school.dashboard', compact(
            'totalStudents',
            'pendingPayments',
            'newInquiries'
        ));
    }
}
