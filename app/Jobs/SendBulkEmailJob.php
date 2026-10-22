<?php

namespace App\Jobs;

use App\Models\Student;
use App\Models\SemesterBatch;
use App\Services\ExternalEmailService;
use App\Helpers\MailTemplateHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBulkEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $studentId;
    protected $mailTemplateIdentifier;
    protected $targetBatchId;

    /**
     * Create a new job instance.
     */
    public function __construct($studentId, $mailTemplateIdentifier, $targetBatchId = null)
    {
        $this->studentId = $studentId;
        $this->mailTemplateIdentifier = $mailTemplateIdentifier;
        $this->targetBatchId = $targetBatchId;
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

            $sem2_fee = '';
            $sem2_sem3_fee = '';
            $sem5_fee = '';
            $sem5_sem6_fee = '';
            $payment_link_opt1 = '';
            $payment_link_opt2 = '';

            $encodedId = base64_encode($student->student_id);

            // Determine target semester based on template or batch
            $targetSemester = null;
            if (strpos($this->mailTemplateIdentifier, 'sem2') !== false) {
                $targetSemester = 2;
            } elseif (strpos($this->mailTemplateIdentifier, 'sem5') !== false) {
                $targetSemester = 5;
            }

            $batch = null;
            if ($this->targetBatchId) {
                $batch = SemesterBatch::find($this->targetBatchId);
            } else {
                $batch = $student->semesterBatches()->wherePivot('status', 0)->first();
            }

            if (empty($targetSemester) && $batch) {
                $targetSemester = $batch->semester;
            }

            $batchName = $batch ? ($batch->display_name ?: 'Semester ' . $batch->sembatches_id) : 'None';

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

                if ($plan34 && $plan35) {
                    $sem2_fee = number_format($plan34->amount, 2, '.', '');
                    $sem2_sem3_fee = number_format($plan35->amount, 2, '.', '');
                    $payment_link_opt1 = route('registration.payment', [$encodedId, $plan34->fee_plan_id]);
                    $payment_link_opt2 = route('registration.payment', [$encodedId, $plan35->fee_plan_id]);
                }
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

                if ($plan36 && $plan37) {
                    $sem5_fee = number_format($plan36->amount, 2, '.', '');
                    $sem5_sem6_fee = number_format($plan37->amount, 2, '.', '');
                    $payment_link_opt1 = route('registration.payment', [$encodedId, $plan36->fee_plan_id]);
                    $payment_link_opt2 = route('registration.payment', [$encodedId, $plan37->fee_plan_id]);
                }
            }

            $variables = [
                'guardian_name' => $guardianName,
                'student_name' => $studentName,
                'student_fname' => $student->fname,
                'batch_name' => $batchName,
                'previous_semester' => $targetSemester == 5 ? 'Semester-04 (Jan 2026 to June 2026)' : 'Semester-01 (July 2026 to Dec 2026)',
                'new_semester' => $batchName,
                'current_semester' => $targetSemester == 5 ? 'Semester-04 (Jan 2026 to June 2026)' : 'Semester-01 (July 2026 to Dec 2026)',
                'next_semester' => $batchName,
                'migration_date' => now()->format('d/m/Y'),
                'enrolment_no' => $student->enrolment_no ?: 'N/A',
                'sem2_fee' => $sem2_fee,
                'sem2_sem3_fee' => $sem2_sem3_fee,
                'sem5_fee' => $sem5_fee,
                'sem5_sem6_fee' => $sem5_sem6_fee,
                'payment_link_opt1' => $payment_link_opt1,
                'payment_link_opt2' => $payment_link_opt2,
            ];

            $emailData = MailTemplateHelper::render($this->mailTemplateIdentifier, $variables);

            if (!$emailData) {
                Log::error("Failed to render mail template '{$this->mailTemplateIdentifier}' for student {$this->studentId}");
                return;
            }

            $subject = $emailData['subject'];
            $message = $emailData['message'];

            $to = [$student->email => $studentName];
            $emailService->sendSmtpMail($to, $subject, $message);
            Log::info("Bulk email sent via job using template {$this->mailTemplateIdentifier} to student {$student->student_id} ({$student->email})");
        } catch (\Exception $e) {
            Log::error('Send Bulk Email Job Error: ' . $e->getMessage());
        }
    }
}
