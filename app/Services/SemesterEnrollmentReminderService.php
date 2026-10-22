<?php

namespace App\Services;

use App\Models\SemesterBatch;
use App\Models\Student;
use Illuminate\Support\Facades\Log;

class SemesterEnrollmentReminderService
{
    /**
     * Send semester enrollment reminder email to student
     */
    public function sendReminder(Student $student, SemesterBatch $currentBatch, SemesterBatch $nextBatch)
    {
        try {
            $emailService = new ExternalEmailService();
            
            $variables = [
                'fname' => strtoupper(trim($student->fname)),
                'current_semester' => $currentBatch->display_name ?? 'N/A',
                'next_semester' => $nextBatch->display_name ?? 'N/A',
                'start_date' => $nextBatch->start_date ? $nextBatch->start_date->format('d M Y') : 'N/A',
                'end_date' => $nextBatch->end_date ? $nextBatch->end_date->format('d M Y') : 'N/A',
                'fees' => number_format($nextBatch->fees, 2),
                'enroll_link' => url('/registrations'),
            ];

            $emailData = \App\Helpers\MailTemplateHelper::render('semester_enrollment_reminder', $variables, $student->selected_language);

            $subject = $emailData ? $emailData['subject'] : 'Next Semester Enrollment Reminder - ' . $nextBatch->display_name;
            $message = $emailData ? $emailData['message'] : '';

            $to = [$student->email => $student->fname . ' ' . $student->lname];
            $emailService->sendSmtpMail($to, $subject, $message);
            
            Log::info("Semester enrollment reminder sent to student {$student->student_id} for next semester {$nextBatch->sembatches_id}");
            return true;
        } catch (\Exception $e) {
            Log::error('Semester Enrollment Reminder Email Error: ' . $e->getMessage());
            return false;
        }
    }
}
