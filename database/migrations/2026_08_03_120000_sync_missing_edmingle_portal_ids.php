<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Student;
use App\Services\EdmingleService;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up(): void
    {
        try {
            // withLeads() drops the excludeLeads global scope: on a fresh install
            // this migration runs before the is_lead column is added.
            $students = Student::withLeads()
                ->where('application_status', 'Enrolled')
                ->whereNull('portal_id')
                ->get();

            if ($students->isEmpty()) {
                return;
            }

            $edmingleService = new EdmingleService();

            foreach ($students as $student) {
                try {
                    $portalId = $edmingleService->getStudentByEmail($student->email);
                    if (!$portalId) {
                        $portalId = $edmingleService->createStudent($student);
                    }
                    
                    if ($portalId) {
                        $student->update(['portal_id' => $portalId]);
                        
                        $feePlan = \App\Models\FeePlan::where('fee_name', $student->subscription)
                            ->where(function($q) use ($student) {
                                $q->where('school_id', $student->school_id)
                                  ->orWhereNull('school_id');
                            })
                            ->orderByRaw('school_id DESC')
                            ->first();

                        if ($feePlan && $feePlan->semesterBatches()->exists()) {
                            $enrolledCourses = $edmingleService->getStudentEnrolledCourses($portalId);
                            foreach ($feePlan->semesterBatches as $batch) {
                                if ($batch->edmingle_course_id) {
                                    $courseId = $batch->edmingle_course_id;
                                    $batchId = $batch->edmingle_batch_id;

                                    if (!in_array((int)$courseId, $enrolledCourses)) {
                                        $edmingleService->enrollStudent($portalId, $courseId, $batchId);
                                    } else if ($batchId) {
                                        try {
                                            $edmingleService->addStudentToBatch($portalId, $batchId);
                                        } catch (\Exception $e) {
                                            Log::warning("Edmingle sync warning for student {$student->student_id} on batch {$batchId}: " . $e->getMessage());
                                        }
                                    }
                                }
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    Log::error("Failed to sync student {$student->student_id} on migration run: " . $e->getMessage());
                }
            }
        } catch (\Throwable $globalException) {
            Log::error("Global exception in migration run: " . $globalException->getMessage());
        }
    }

    public function down(): void
    {
        // No rollback required
    }
};
