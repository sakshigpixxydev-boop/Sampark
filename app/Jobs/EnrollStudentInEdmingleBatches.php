<?php

namespace App\Jobs;

use App\Models\Student;
use App\Models\FeePlan;
use App\Models\Role;
use App\Models\SysUser;
use App\Services\EdmingleService;
use App\Services\ExternalEmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EnrollStudentInEdmingleBatches implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $studentId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($studentId)
    {
        $this->studentId = $studentId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $student = Student::find($this->studentId);
        if (!$student) {
            Log::error("Job EnrollStudentInEdmingleBatches failed: Student {$this->studentId} not found.");
            return;
        }

        DB::beginTransaction();
        try {
            $feePlan = FeePlan::where('fee_name', $student->subscription)
                ->where(function($q) use ($student) {
                    $q->where('school_id', $student->school_id)
                      ->orWhereNull('school_id');
                })
                ->orderByRaw('school_id DESC')
                ->first();
            if (!$feePlan) {
                // Try to find a fallback fee plan matching student's school or first active plan
                $feePlan = FeePlan::where('is_active_on_form', 1)
                    ->where(function($q) use ($student) {
                        $q->where('school_id', $student->school_id)
                          ->orWhereNull('school_id');
                    })
                    ->first() ?: FeePlan::where(function($q) use ($student) {
                        $q->where('school_id', $student->school_id)
                          ->orWhereNull('school_id');
                    })->orderByRaw('school_id DESC')->first();
                
                if (!$feePlan) {
                    throw new \Exception('Fee plan not found for subscription: ' . $student->subscription);
                }
                // Update student subscription to the fallback plan name so it remains consistent
                $student->update(['subscription' => $feePlan->fee_name]);
            }

            $edmingleService = new EdmingleService();
            
            // Check if student already exists in Edmingle by email
            $portalId = $edmingleService->getStudentByEmail($student->email);
            
            if (!$portalId) {
                // Create student in Edmingle
                $portalId = $edmingleService->createStudent($student);
                if (!$portalId) {
                    throw new \Exception('Failed to create student on Edmingle.');
                }
            }

            $enrolledCourses = $edmingleService->getStudentEnrolledCourses($portalId);
            $hasEnrolledInAny = false;

            // Enroll student in all semester batches associated with the fee plan
            if ($feePlan->semesterBatches()->exists()) {
                foreach ($feePlan->semesterBatches as $batch) {
                    if ($batch->edmingle_course_id) {
                        $courseId = $batch->edmingle_course_id;
                        $batchId = $batch->edmingle_batch_id;

                        if (in_array((int)$courseId, $enrolledCourses)) {
                            Log::info("Edmingle: Student {$student->email} ({$portalId}) is already enrolled in course {$courseId} on Edmingle.");
                            // If already enrolled in course, ensure student is added to the specific batch
                            if ($batchId) {
                                try {
                                    $edmingleService->addStudentToBatch($portalId, $batchId);
                                } catch (\Exception $e) {
                                    Log::error("Failed to add student {$student->email} to batch {$batchId}: " . $e->getMessage());
                                }
                            }
                        } else {
                            // Enroll student in course/batch on Edmingle
                            $enrolled = $edmingleService->enrollStudent($portalId, $courseId, $batchId);
                            if (!$enrolled) {
                                Log::error("Failed to enroll student {$student->email} in course {$courseId}");
                            } else {
                                $enrolledCourses[] = (int)$courseId;
                                $hasEnrolledInAny = true;
                            }
                        }
                    }

                    // Assign student to semester batch in our database
                    if (!$student->semesterBatches()->where('sembatches.sembatches_id', $batch->sembatches_id)->exists()) {
                        $student->semesterBatches()->attach($batch->sembatches_id, [
                            'status' => 0, // 0 = Active
                            'assigned_at' => now(),
                        ]);
                        Log::info("Student {$student->student_id} assigned to semester batch {$batch->sembatches_id}");
                    }
                }
            }

            $emailPrefix = explode('@', $student->email)[0];
            $lmsUser = $student->lmsuser ?: Str::slug($emailPrefix, '.');
            $lmsPass = $student->lmspswd ?: Str::random(8);
            if (str_starts_with($lmsPass, '$2y$')) {
                $lmsPass = $student->contact_no ?: Str::random(8);
            }

            // Update student with lms user, password, and portal_id
            $student->update([
                'application_status' => 'Enrolled',
                'lmsuser' => $lmsUser,
                'lmspswd' => $lmsPass,
                'portal_id' => $portalId
            ]);

            // Create sysuser record for student login
            $studentRole = Role::where('rolename', 'Student')->first();
            if (!$studentRole) {
                throw new \Exception('Student role not found in system.');
            }

            // Check if sysuser already exists for this student
            $existingUser = SysUser::where('email', $student->email)->first();
            
            if (!$existingUser) {
                // Create new sysuser
                $sysUser = SysUser::create([
                    'role_id' => $studentRole->role_id,
                    'fname' => $student->fname,
                    'mname' => $student->mname,
                    'lname' => $student->lname,
                    'email' => $student->email,
                    'phone' => $student->contact_no,
                    'whatsapp' => $student->whatsapp_no,
                    'username' => $lmsUser,
                    'pswd' => $lmsPass,
                    'inactive' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Link sysuser to student
                $sysUser->students()->attach($student->student_id);
            } else {
                // Update existing sysuser
                $existingUser->update([
                    'username' => $lmsUser,
                    'pswd' => $lmsPass,
                    'inactive' => false,
                    'updated_at' => now(),
                ]);

                // Ensure linkage exists
                if (!$existingUser->students()->where('student_id', $student->student_id)->exists()) {
                    $existingUser->students()->attach($student->student_id);
                }
            }

            $this->sendWelcomeEmail($student);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Enrollment Job Error for Student ' . $this->studentId . ': ' . $e->getMessage());
            
            // Revert status to indicate failure or manual review needed
            $student->update(['application_status' => 'Documents Submitted']);
        }
    }

    private function sendWelcomeEmail($student)
    {
        try {
            $emailService = new ExternalEmailService();
            
            $guardian = $student->guardians()->first();
            $guardianName = $guardian ? ($guardian->fname . ' ' . $guardian->lname) : '';
            $studentName = $student->fname . ' ' . $student->lname;

            $courseValidity = 'N/A';
            $latestBatch = $student->semesterBatches()->orderBy('end_date', 'desc')->first();
            if ($latestBatch && $latestBatch->end_date) {
                $courseValidity = \Carbon\Carbon::parse($latestBatch->end_date)->format('d/m/Y');
            } else {
                $feePlan = \App\Models\FeePlan::where('fee_name', $student->subscription)
                    ->where(function($q) use ($student) {
                        $q->where('school_id', $student->school_id)
                          ->orWhereNull('school_id');
                    })
                    ->orderByRaw('school_id DESC')
                    ->first();
                if ($feePlan && $feePlan->valid_to) {
                    $courseValidity = \Carbon\Carbon::parse($feePlan->valid_to)->format('d/m/Y');
                } else {
                    $courseValidity = now()->addYear()->format('d/m/Y');
                }
            }

            $variables = [
                'guardian_name' => $guardianName,
                'student_name' => $studentName,
                'portal_link' => route('login'),
                'enrolment_no' => $student->enrolment_no,
                'lmspswd' => $student->lmspswd,
                'course_validity' => $courseValidity,
            ];

            $emailData = \App\Helpers\MailTemplateHelper::render('student_welcome', $variables, $student->selected_language);

            $subject = $emailData ? $emailData['subject'] : 'Welcome to Pramukh SANKALP – Portal Access Details';
            $message = $emailData ? $emailData['message'] : '';

            $to = [$student->email => $student->fname . ' ' . $student->lname];
            $emailService->sendSmtpMail($to, $subject, $message);
        } catch (\Exception $e) {
            Log::error('Welcome Email Error: ' . $e->getMessage());
        }
    }
}
