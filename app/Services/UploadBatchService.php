<?php

namespace App\Services;

use App\Models\UploadBatch;
use App\Models\PendingStudent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UploadBatchService
{
    /**
     * Create a new upload batch with pending students
     */
    public function createBatch($students, $semesterBatchId, $uploadedBy, $totals, $paymentDetails = [])
    {
        return DB::transaction(function () use ($students, $semesterBatchId, $uploadedBy, $totals, $paymentDetails) {
            // Generate unique batch code
            $batchCode = $this->generateBatchCode();

            // Create upload batch record
            $uploadBatch = UploadBatch::create([
                'batch_code' => $batchCode,
                'sembatches_id' => $semesterBatchId,
                'school_id' => $paymentDetails['school_id'] ?? null,
                'uploaded_by' => $uploadedBy,
                'upload_date' => now(),
                'total_students' => $totals['student_count'],
                'fee_per_student' => $totals['fee_per_student'],
                'total_fees' => $totals['total_fees'],
                'status' => 'pending',
                'payment_verified' => false,
                'payment_type' => $paymentDetails['payment_type'] ?? null,
                'offline_payment_method' => $paymentDetails['offline_payment_method'] ?? null,
                'payment_document_path' => $paymentDetails['payment_document_path'] ?? null,
            ]);

            // Create pending student records
            foreach ($students as $student) {
                PendingStudent::create([
                    'batch_id' => $uploadBatch->batch_id,
                    'student_data' => $student['data'],
                    'calculated_fee' => $student['calculated_fee'],
                    'validation_errors' => !empty($student['errors']) ? $student['errors'] : null,
                ]);
            }

            // Initiator details are no longer stored in a separate table as they are fetched dynamically from the user profile
            // during the bulk upload process or student registration.

            return $uploadBatch;
        });
    }

    /**
     * Generate a unique batch code
     */
    private function generateBatchCode()
    {
        do {
            // Format: BATCH-YYYYMMDD-XXXX (e.g., BATCH-20251110-A1B2)
            $code = 'BATCH-' . date('Ymd') . '-' . strtoupper(Str::random(4));
        } while (UploadBatch::where('batch_code', $code)->exists());

        return $code;
    }

    /**
     * Get batch with pending students
     */
    public function getBatchWithStudents($batchId)
    {
        return UploadBatch::with(['pendingStudents', 'semesterBatch', 'uploader'])
            ->findOrFail($batchId);
    }

    /**
     * Update batch status
     */
    public function updateBatchStatus($batchId, $status, $notes = null)
    {
        $batch = UploadBatch::findOrFail($batchId);
        $batch->status = $status;

        if ($notes) {
            $batch->notes = $notes;
        }

        $batch->save();

        return $batch;
    }
}
