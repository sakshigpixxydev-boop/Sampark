<?php

namespace App\Jobs;

use App\Models\SemesterBatch;
use App\Models\Student;
use App\Notifications\SemesterEnrollmentReminderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CheckSemesterEnrollments implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Find semester batches that have ended (end_date < today)
        $completedBatches = SemesterBatch::where('end_date', '<', now())
            ->where('status', 0) // Active batches
            ->whereNotNull('next_sembatch_id')
            ->get();

        foreach ($completedBatches as $currentBatch) {
            $nextBatch = SemesterBatch::find($currentBatch->next_sembatch_id);
            
            if (!$nextBatch) {
                continue;
            }

            // Get students enrolled in the current batch who have completed it
            $students = $currentBatch->students()
                ->wherePivot('status', 1) // 1 = Completed
                ->wherePivot('completed_at', '!=', null)
                ->get();

            foreach ($students as $student) {
                // Check if student is already enrolled in the next batch
                $isEnrolledInNext = $student->semesterBatches()
                    ->where('sembatches.sembatches_id', $nextBatch->sembatches_id)
                    ->exists();

                if (!$isEnrolledInNext) {
                    // Automatically assign student to next batch
                    $this->assignStudentToBatch($student, $nextBatch);
                    Log::info("Student {$student->student_id} automatically assigned to next batch {$nextBatch->sembatches_id}");
                } else {
                    // Student already enrolled in next batch, skip
                    Log::info("Student {$student->student_id} already enrolled in next batch {$nextBatch->sembatches_id}");
                }
            }
        }
    }

    /**
     * Assign student to a semester batch with Edmingle course/batch if available
     */
    private function assignStudentToBatch(Student $student, SemesterBatch $batch)
    {
        // Attach student to the semester batch
        $student->semesterBatches()->attach($batch->sembatches_id, [
            'status' => 0, // 0 = Active
            'assigned_at' => now(),
        ]);

        // Note: Edmingle enrollment is handled during the registration/approval process
        // This job only manages semester batch assignments in the local system
        // The actual Edmingle course/batch enrollment happens in AdminRegistrationController
        Log::info("Student {$student->student_id} assigned to semester batch {$batch->sembatches_id} in local system");
    }
}
