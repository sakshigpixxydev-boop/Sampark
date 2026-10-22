<?php

namespace App\Jobs;

use App\Models\Student;
use App\Services\ExternalEmailService;
use App\Helpers\MailTemplateHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBatchShiftEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $studentId;
    protected $previousSemesterName;
    protected $newSemesterName;
    protected $mailTemplateIdentifier;
    protected $targetBatchId;
    protected $isFirst;
    protected $isLast;

    /**
     * Create a new job instance.
     */
    public function __construct($studentId, $previousSemesterName, $newSemesterName, $mailTemplateIdentifier = null, $targetBatchId = null, $isFirst = false, $isLast = false)
    {
        $this->studentId = $studentId;
        $this->previousSemesterName = $previousSemesterName;
        $this->newSemesterName = $newSemesterName;
        $this->mailTemplateIdentifier = $mailTemplateIdentifier;
        $this->targetBatchId = $targetBatchId;
        $this->isFirst = $isFirst;
        $this->isLast = $isLast;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $student = Student::find($this->studentId);
            if (!$student) {
                return;
            }

            $emailService = new ExternalEmailService();
            
            $guardian = $student->guardians()->first();
            $guardianName = $guardian ? ($guardian->fname . ' ' . $guardian->lname) : '';
            $studentName = $student->fname . ' ' . $student->lname;

            // Resolve target semester
            $targetSemester = null;
            if ($this->targetBatchId) {
                $targetBatch = \App\Models\SemesterBatch::find($this->targetBatchId);
                if ($targetBatch) {
                    $targetSemester = $targetBatch->semester;
                }
            } else {
                $targetBatch = $student->semesterBatches()->wherePivot('status', 0)->first();
                if ($targetBatch) {
                    $targetSemester = $targetBatch->semester;
                }
            }

            $sem2_fee = '';
            $sem2_sem3_fee = '';
            $sem5_fee = '';
            $sem5_sem6_fee = '';
            $payment_link_opt1 = '';
            $payment_link_opt2 = '';

            $encodedId = base64_encode($student->student_id);

            if ($targetSemester == 2) {
                $plan34 = \App\Models\FeePlan::where(function($q) use ($student) {
                    $q->where('school_id', $student->school_id)
                      ->orWhereNull('school_id');
                })->where('fee_name', 'like', 'Semester-02%')->where('fee_name', 'not like', '%&%')
                  ->orderByRaw('school_id DESC')
                  ->first();

                $plan35 = \App\Models\FeePlan::where(function($q) use ($student) {
                    $q->where('school_id', $student->school_id)
                      ->orWhereNull('school_id');
                })->where('fee_name', 'like', 'Semester-02 & 03%')
                  ->orderByRaw('school_id DESC')
                  ->first();

                if (!$plan34 || !$plan35) {
                    throw new \Exception("Required fee plans for Semester 2 not found in database.");
                }
                $sem2_fee = number_format($plan34->amount, 2, '.', '');
                $sem2_sem3_fee = number_format($plan35->amount, 2, '.', '');
                $payment_link_opt1 = route('registration.payment', [$encodedId, $plan34->fee_plan_id]);
                $payment_link_opt2 = route('registration.payment', [$encodedId, $plan35->fee_plan_id]);
            } elseif ($targetSemester == 5) {
                $plan36 = \App\Models\FeePlan::where(function($q) use ($student) {
                    $q->where('school_id', $student->school_id)
                      ->orWhereNull('school_id');
                })->where('fee_name', 'like', 'Semester-05%')->where('fee_name', 'not like', '%&%')
                  ->orderByRaw('school_id DESC')
                  ->first();

                $plan37 = \App\Models\FeePlan::where(function($q) use ($student) {
                    $q->where('school_id', $student->school_id)
                      ->orWhereNull('school_id');
                })->where('fee_name', 'like', 'Semester-05 & 06%')
                  ->orderByRaw('school_id DESC')
                  ->first();

                if (!$plan36 || !$plan37) {
                    throw new \Exception("Required fee plans for Semester 5 not found in database.");
                }
                $sem5_fee = number_format($plan36->amount, 2, '.', '');
                $sem5_sem6_fee = number_format($plan37->amount, 2, '.', '');
                $payment_link_opt1 = route('registration.payment', [$encodedId, $plan36->fee_plan_id]);
                $payment_link_opt2 = route('registration.payment', [$encodedId, $plan37->fee_plan_id]);
            }

            // Determine template identifier
            $templateIdentifier = $this->mailTemplateIdentifier;

            if (empty($templateIdentifier)) {
                $isPaidForTargetSem = $this->checkIfPaidForSemester($student, $targetSemester);

                if ($targetSemester == 2) {
                    $templateIdentifier = $isPaidForTargetSem ? 'auto_enrolment_sem2' : 'payment_required_sem2';
                } elseif ($targetSemester == 5) {
                    $templateIdentifier = $isPaidForTargetSem ? 'auto_enrolment_sem5' : 'payment_required_sem5';
                } else {
                    $templateIdentifier = 'student_batch_shift';
                }
            }

            $variables = [
                'guardian_name' => $guardianName,
                'student_name' => $studentName,
                'student_fname' => $student->fname,
                'previous_semester' => $this->previousSemesterName,
                'new_semester' => $this->newSemesterName,
                'batch_name' => $this->newSemesterName,
                'current_semester' => $this->previousSemesterName,
                'next_semester' => $this->newSemesterName,
                'migration_date' => now()->format('d/m/Y'),
                'enrolment_no' => $student->enrolment_no ?: 'N/A',
                'sem2_fee' => $sem2_fee,
                'sem2_sem3_fee' => $sem2_sem3_fee,
                'sem5_fee' => $sem5_fee,
                'sem5_sem6_fee' => $sem5_sem6_fee,
                'payment_link_opt1' => $payment_link_opt1,
                'payment_link_opt2' => $payment_link_opt2,
            ];

            $emailData = MailTemplateHelper::render($templateIdentifier, $variables);

            $subject = $emailData ? $emailData['subject'] : 'Student Semester Batch Shifted – Pramukh SANKALP';
            $message = $emailData ? $emailData['message'] : '';

            $to = [$student->email => $studentName];
            $emailService->sendSmtpMail($to, $subject, $message);
            
            if ($this->isFirst || $this->isLast) {
                $trackingSubject = ($this->isFirst ? '[STARTING TRACKING] ' : '[ENDING TRACKING] ') . $subject;
                $trackingTo = [
                    'chiragkanjariya0712+sankalpmigration@gmail.com' => 'Tracking Admin 1',
                    'chiragm.closedigit@gmail.com' => 'Tracking Admin 2'
                ];
                $emailService->sendSmtpMail($trackingTo, $trackingSubject, $message);
            }
            
            Log::info("Migration email sent via job to student {$student->student_id} ({$student->email}) using template {$templateIdentifier}");
        } catch (\Exception $e) {
            Log::error('Batch Shift Email Job Error: ' . $e->getMessage());
        }
    }

    /**
     * Check if a student has already paid for a target semester.
     */
    private function checkIfPaidForSemester(Student $student, ?int $semester): bool
    {
        if (empty($semester)) {
            return false;
        }

        // 1. Check if their current active subscription plan covers this semester
        if (!empty($student->subscription) && $student->fees_status === 'Paid') {
            $sub = $student->subscription;
            if ($semester == 2) {
                if (
                    strpos($sub, 'Semester-01 & 02') !== false ||
                    strpos($sub, 'Semester-02') !== false ||
                    strpos($sub, 'Sem-02') !== false ||
                    strpos($sub, 'Sem 02') !== false
                ) {
                    return true;
                }
            } elseif ($semester == 5) {
                if (
                    strpos($sub, 'Semester-04 & 05') !== false ||
                    strpos($sub, 'Semester-05') !== false ||
                    strpos($sub, 'Sem-05') !== false ||
                    strpos($sub, 'Sem 05') !== false
                ) {
                    return true;
                }
            }
        }

        // 2. Check if they have any paid invoice containing an item for this semester
        $paidInvoiceExists = \App\Models\Invoice::where('student_id', $student->student_id)
            ->where('status', 'PAID')
            ->whereHas('items', function ($q) use ($semester) {
                $term = sprintf('%02d', $semester);
                $q->where(function($sub) use ($term) {
                    $sub->where('description', 'like', "%Semester-{$term}%")
                        ->orWhere('description', 'like', "%Sem {$term}%")
                        ->orWhere('description', 'like', "%Sem-{$term}%")
                        ->orWhere('description', 'like', "% & {$term}%"); // e.g. Semester-01 & 02
                });
            })
            ->exists();

        return $paidInvoiceExists;
    }
}
