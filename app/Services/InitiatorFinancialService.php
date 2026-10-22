<?php

namespace App\Services;

use App\Models\SysUser;
use App\Models\School;
use App\Models\Student;
use App\Models\Invoice;
use App\Models\PaymentLog;
use Illuminate\Support\Facades\DB;

class InitiatorFinancialService
{
    /**
     * Calculate total earnings for an initiator
     */
    public function calculateEarnings(SysUser $user)
    {
        $totalPaidToInitiator = $user->initiatorPayments()->sum('amount');

        if (!$user->isPaidInitiator()) {
            return [
                'total' => 0,
                'total_paid' => $totalPaidToInitiator,
                'balance' => -$totalPaidToInitiator,
                'total_revenue' => 0,
                'calculation_type_label' => 'Standard',
                'breakdown' => []
            ];
        }

        $configs = $user->financialConfigs;
        if ($configs->isEmpty()) {
            return [
                'total' => 0,
                'total_paid' => $totalPaidToInitiator,
                'balance' => -$totalPaidToInitiator,
                'total_revenue' => 0,
                'calculation_type_label' => 'Standard',
                'breakdown' => []
            ];
        }

        // 1. School Registration Commission
        $assignedSchoolIds = $user->getAssignedSchoolIds();
        $schoolCount = count($assignedSchoolIds);
        $schoolEarnings = 0;

        // 2. Student Commissions & Revenue Base
        $assignedStudentIds = $user->getAssignedStudentIds();
        $studentEarnings = 0;
        $totalRevenue = 0;
        $totalStudents = count($assignedStudentIds);

        // Fetch all invoices for these students
        $invoices = Invoice::whereIn('student_id', $assignedStudentIds)->get();
        // Filter invoices with 'PAID' status for commission calculation
        $paidInvoices = $invoices->where('status', 'PAID');
        // Count unique students who have fully paid at least one invoice
        $paidStudentIds = $paidInvoices->pluck('student_id')->unique()->toArray();
        $totalPaidStudents = count($paidStudentIds);

        foreach ($invoices as $invoice) {
            // Revenue is based on paid amount (Status '1' is AUTHORIZED/SUCCESS)
            $paidAmount = $invoice->payments()->where('status', '1')->sum('amount');
            $totalRevenue += $paidAmount;
        }

        $slabEarnings = 0;
        $slabDetailsList = [];
        $applicableSlabs = [];

        foreach ($configs as $config) {
            // Calculate school earnings for this config
            if ($schoolCount > 0) {
                $baseCount = $config->school_count_base;
                $baseComm = $config->new_school_registration_commission;
                $slabAmount = $config->school_count_slab_amount;

                if ($schoolCount <= $baseCount) {
                    $schoolEarnings += $schoolCount * $baseComm;
                } else {
                    $schoolEarnings += ($baseCount * $baseComm) + (($schoolCount - $baseCount) * $slabAmount);
                }
            }

            $calculationType = $config->student_calculation_type;

            // Student commission based on semester type
            if ($calculationType == \App\Models\InitiatorFinancialConfig::TYPE_PER_STUDENT) {
                foreach ($invoices as $invoice) {
                    $student = $invoice->student;
                    // Only calculate commission if the invoice is fully PAID
                    if ($invoice->status == 'PAID' && $student) {
                        $semCount = DB::table('student_sembatches')->where('student_id', $student->student_id)->count();
                        $isRepeat = $semCount > 1;

                        $perc = $isRepeat ? $config->per_student_commission_repeat_semester : $config->per_student_commission_first_semester;

                        // commission on invoice total due (which is fully paid)
                        $studentEarnings += ($invoice->total_due * $perc / 100);
                    }
                }
            }

            // Student Count Slabs
            if ($calculationType == \App\Models\InitiatorFinancialConfig::TYPE_SLAB_BASED) {
                // Find applicable slab based on totalPaidStudents count
                $applicableSlab = null;
                $maxMinStudents = -1;
                foreach ($config->slabs as $slab) {
                    if ($totalPaidStudents >= $slab->min_students && $slab->min_students > $maxMinStudents) {
                        $applicableSlab = $slab;
                        $maxMinStudents = $slab->min_students;
                    }
                }

                if ($applicableSlab) {
                    $slabDesc = [];
                    // 1. Percentage of total invoice due for fully PAID invoices
                    if ($applicableSlab->commission_percentage > 0) {
                        $totalPaidInvoiceDue = $paidInvoices->sum('total_due') ?? 0;
                        $slabEarnings += ($totalPaidInvoiceDue * $applicableSlab->commission_percentage / 100);
                        $slabDesc[] = $applicableSlab->commission_percentage . "% of Paid Invoices Revenue";
                    }

                    // 2. Fixed amount per fully PAID student
                    if ($applicableSlab->fixed_amount > 0) {
                        $slabEarnings += ($totalPaidStudents * $applicableSlab->fixed_amount);
                        $slabDesc[] = "₹" . number_format($applicableSlab->fixed_amount, 2) . " per paid student";
                    }
                    $slabDetailsList[] = ($config->name ?? 'Plan') . ': ' . implode(" + ", $slabDesc);
                    $applicableSlabs[] = [
                        'min_students' => $applicableSlab->min_students,
                        'percentage' => $applicableSlab->commission_percentage,
                        'fixed_amount' => $applicableSlab->fixed_amount,
                    ];
                }
            }
        }

        $total = $schoolEarnings + $studentEarnings + $slabEarnings;
        $balance = $total - $totalPaidToInitiator;

        $calculationTypeLabel = $configs->map(function($config) {
            return $config->student_calculation_type == \App\Models\InitiatorFinancialConfig::TYPE_SLAB_BASED ? 'Slab-Based' : 'Per-Student';
        })->unique()->implode(', ');

        return [
            'total' => $total,
            'total_paid' => $totalPaidToInitiator,
            'balance' => $balance,
            'total_revenue' => $totalRevenue,
            'calculation_type_label' => $calculationTypeLabel,
            'breakdown' => [
                'schools' => [
                    'count' => $schoolCount,
                    'earnings' => $schoolEarnings
                ],
                'students' => [
                    'count' => $totalStudents,
                    'paid_count' => $totalPaidStudents,
                    'earnings' => $studentEarnings
                ],
                'slabs' => [
                    'earnings' => $slabEarnings,
                    'paid_count' => $totalPaidStudents,
                    'details' => !empty($slabDetailsList) ? implode(" | ", $slabDetailsList) : 'No applicable slab',
                    'slab_details' => !empty($applicableSlabs) ? $applicableSlabs[0] : null
                ]
            ]
        ];
    }

    /**
     * Get detailed earnings breakdown including schools and students
     */
    public function getDetailedEarnings(SysUser $user)
    {
        if (!$user->isPaidInitiator()) {
            $summary = $this->calculateEarnings($user);
            return array_merge($summary, [
                'details' => [
                    'schools' => collect(),
                    'invoices' => collect(),
                    'payments' => $user->initiatorPayments()->orderBy('payment_date', 'desc')->get()
                ]
            ]);
        }

        $summary = $this->calculateEarnings($user);
        
        $assignedSchoolIds = $user->getAssignedSchoolIds();
        $assignedStudentIds = $user->getAssignedStudentIds();

        // 1. Detailed Schools
        $schools = School::whereIn('school_id', $assignedSchoolIds)
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. Detailed Students & Invoices
        $invoices = Invoice::with(['student', 'student.semesterBatches'])
            ->whereIn('student_id', $assignedStudentIds)
            ->orderBy('created_at', 'desc')
            ->get();

        return array_merge($summary, [
            'details' => [
                'schools' => $schools,
                'invoices' => $invoices,
                'payments' => $user->initiatorPayments()->orderBy('payment_date', 'desc')->get()
            ]
        ]);
    }
}
