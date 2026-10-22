<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadBatch extends Model
{
    protected $table = 'upload_batch';
    protected $primaryKey = 'batch_id';
    public $timestamps = false;

    protected $fillable = [
        'batch_code',
        'sembatches_id',
        'school_id',
        'uploaded_by',
        'upload_date',
        'total_students',
        'fee_per_student',
        'total_fees',
        'status',
        'payment_verified',
        'payment_type',
        'offline_payment_method',
        'bank_name',
        'payment_document_path',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
        'transaction_id',
        'payment_date',
        'verified_by',
        'verified_at',
        'notes',
        'paid_amount', // Added paid_amount
        'fee_plan_id',
    ];

    protected $casts = [
        'upload_date' => 'datetime',
        'payment_date' => 'datetime',
        'verified_at' => 'datetime',
        'fee_per_student' => 'decimal:2',
        'total_fees' => 'decimal:2',
        'paid_amount' => 'decimal:2', // Added cast
        'payment_verified' => 'boolean',
    ];

    // Relationships
    public function semesterBatch()
    {
        return $this->belongsTo(SemesterBatch::class, 'sembatches_id');
    }

    public function feePlan()
    {
        return $this->belongsTo(FeePlan::class, 'fee_plan_id');
    }

    public function uploader()
    {
        return $this->belongsTo(SysUser::class, 'uploaded_by');
    }

    public function verifier()
    {
        return $this->belongsTo(SysUser::class, 'verified_by');
    }

    public function paymentMode()
    {
        return $this->belongsTo(PaymentMode::class, 'payment_mode_id');
    }

    public function pendingStudents()
    {
        return $this->hasMany(PendingStudent::class, 'batch_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    /**
     * Dynamic Initiator Details (to replace the dropped batch_initiators table)
     * Fetches details dynamically from the uploader's profile and associated school.
     */
    public function getInitiatorAttribute()
    {
        $uploader = $this->uploader;
        if (!$uploader || !$uploader->hasRole('Initiator')) {
            return null;
        }

        $school = $this->school;
        
        // Calculate basic financial metrics for the view if possible
        $totalPaidToInitiator = $uploader->initiatorPayments()->sum('amount');
        
        return (object) [
            'first_name' => $uploader->fname,
            'middle_name' => $uploader->mname,
            'last_name' => $uploader->lname,
            'initiator_code' => $uploader->refcode ?: $uploader->username,
            'contact_no' => $uploader->phone,
            'email' => $uploader->email,
            'enroll_school_name' => $school->schoolname ?? 'N/A',
            'school_address' => trim(($school->address1 ?? '') . ' ' . ($school->address2 ?? '')),
            'total_enroll_student' => $this->total_students,
            'honorarium' => 0, // Calculated dynamically by InitiatorFinancialService in real time
            'payment_pending' => 0, 
            'payment_received' => $totalPaidToInitiator,
        ];
    }

    // Methods
    public function approve($paymentModeId, $transactionId, $paymentDate, $verifiedBy, $bankName = null)
    {
        try {
            if (\Illuminate\Support\Facades\Hash::driver() instanceof \Illuminate\Hashing\BcryptHasher) {
                \Illuminate\Support\Facades\Hash::driver()->setRounds(4);
            }
        } catch (\Throwable $e) {}

        $this->status = 'approved';
        $this->payment_verified = true;
        $this->transaction_id = $transactionId;
        $this->payment_date = $paymentDate;
        $this->bank_name = $bankName;
        $this->verified_by = $verifiedBy;
        $this->verified_at = now();
        // Assume full payment if approved via this method, unless paid_amount is already tracked
        if ($this->paid_amount < $this->total_fees) {
            $this->paid_amount = $this->total_fees;
        }
        $this->save();

        $batchPrefix = $this->semesterBatch->code_prefix ?? ($this->semesterBatch->standard->stdname ?? 'BATCH');
        $batchPrefix = str_replace(' ', '', $batchPrefix); // Remove spaces for code/username
        
        $studentsToInsert = [];
        $emailsToSend = [];
        
        // Get valid columns from Student model to ensure clean insert
        $studentModel = new Student();
        $validColumns = $studentModel->getFillable();

        // Convert pending students to registered students
        // Only process those who are not yet registered
        $pendingStudents = $this->pendingStudents()->notRegistered()->get();
        
        $studentModel = new Student();
        $validColumns = $studentModel->getFillable();

        foreach ($pendingStudents as $pendingStudent) {
            $studentData = $pendingStudent->student_data;
            while (is_string($studentData)) {
                $decoded = json_decode($studentData, true);
                if (json_last_error() !== JSON_ERROR_NONE || $decoded === $studentData) break;
                $studentData = $decoded;
            }
            if (!is_array($studentData)) $studentData = [];

            // Format: [First Name]_[Batch Middle Fix]_[Import ID]
            $fname = strtolower(preg_replace('/\s+/', '', $studentData['fname'] ?? 'student'));
            $importId = $pendingStudent->pending_id;
            $username = "{$fname}_{$batchPrefix}_{$importId}";

            // Password: Mobile Number
            $mobileNo = $studentData['contact_no'] ?? '1234567890';

            $studentData['lmsuser'] = $username;
            $studentData['lmspswd'] = $mobileNo;
            
            // Filter and pad data to ensure identical keys for bulk insert
            $cleanData = [];
            foreach ($validColumns as $col) {
                $cleanData[$col] = $studentData[$col] ?? null;
            }
            $studentsToInsert[] = $studentData; // Pass full data
            
            // Mark pending student as registered
            $pendingStudent->is_registered = true;
            $pendingStudent->save();
        }
        
        // Individual processing for Students and Invoices
        foreach ($studentsToInsert as $studentData) {
            $student = $this->createStudentWithLinks($studentData, 'Generated from Batch: ' . $this->batch_code, 'PAID', 0, $paymentModeId);
            if ($student) {
                \App\Jobs\EnrollStudentInEdmingleBatches::dispatch($student->student_id);
            }
        }
    }

    public function approvePartial($amountPaid, $selectedStudentIds, $transactionId, $paymentDate, $verifiedBy, $bankName = null)
    {
        try {
            if (\Illuminate\Support\Facades\Hash::driver() instanceof \Illuminate\Hashing\BcryptHasher) {
                \Illuminate\Support\Facades\Hash::driver()->setRounds(4);
            }
        } catch (\Throwable $e) {}

        // Update paid amount
        $this->paid_amount += $amountPaid;
        
        // Update payment details if not already set (or append?)
        if (!$this->transaction_id) {
            $this->transaction_id = $transactionId;
        } else {
             // For multiple payments, maybe we append txn id? Or keep the first one. 
             // Requirement says: "Partial payment can be made multiple times."
             $this->transaction_id .= ', ' . $transactionId;
        }
        
        $this->bank_name = $bankName; // Update bank name to latest
        $this->payment_date = $paymentDate; // Update date to latest
        $this->verified_by = $verifiedBy;
        $this->verified_at = now();
        
        // Check if fully paid (allow small margin of error for float)
        if ($this->paid_amount >= ($this->total_fees - 1)) {
           // Treated as full approval
           $this->approve(null, $this->transaction_id, $paymentDate, $verifiedBy, $bankName);
           return;
        }
        
        $this->save();

        $batchPrefix = $this->semesterBatch->code_prefix ?? ($this->semesterBatch->standard->stdname ?? 'BATCH');
        $batchPrefix = str_replace(' ', '', $batchPrefix);
        
        $studentsToInsert = [];
        $emailsToSend = [];
        $studentModel = new Student();
        $validColumns = $studentModel->getFillable();

        // Process ONLY selected students
        $pendingStudents = $this->pendingStudents()
                                ->whereIn('pending_id', $selectedStudentIds)
                                ->notRegistered()
                                ->get();
                                
        $studentModel = new Student();
        $validColumns = $studentModel->getFillable();

        foreach ($pendingStudents as $pendingStudent) {
            $studentData = $pendingStudent->student_data;
            while (is_string($studentData)) {
                $decoded = json_decode($studentData, true);
                if (json_last_error() !== JSON_ERROR_NONE || $decoded === $studentData) break;
                $studentData = $decoded;
            }
            if (!is_array($studentData)) $studentData = [];

            $fname = strtolower(preg_replace('/\s+/', '', $studentData['fname'] ?? 'student'));
            $importId = $pendingStudent->pending_id;
            $username = "{$fname}_{$batchPrefix}_{$importId}";

            // Password: Mobile Number
            $mobileNo = $studentData['contact_no'] ?? '1234567890';

            $studentData['lmsuser'] = $username;
            $studentData['lmspswd'] = $mobileNo;
            
            // Filter and pad data to ensure identical keys for bulk insert
            $cleanData = [];
            foreach ($validColumns as $col) {
                $cleanData[$col] = $studentData[$col] ?? null;
            }
            $studentsToInsert[] = $studentData; // Pass full data
            
            // Mark as registered
            $pendingStudent->is_registered = true;
            $pendingStudent->save();
        }
        
        $paymentAmountPerStudent = count($studentsToInsert) > 0 
            ? number_format($amountPaid / count($studentsToInsert), 2, '.', '') 
            : 0;

        // Individual processing for Students and Invoices
        foreach ($studentsToInsert as $studentData) {
            $student = $this->createStudentWithLinks(
                $studentData, 
                'Generated from Partial Batch: ' . $this->batch_code, 
                'PARTIAL', 
                $paymentAmountPerStudent, 
                $this->getPaymentModeId()
            );
            if ($student) {
                \App\Jobs\EnrollStudentInEdmingleBatches::dispatch($student->student_id);
            }
        }
    }

    /**
     * Approve online payment (Razorpay) - students enrolled immediately
     */
    public function approveOnlinePayment($razorpayOrderId, $razorpayPaymentId, $razorpaySignature)
    {
        try {
            if (\Illuminate\Support\Facades\Hash::driver() instanceof \Illuminate\Hashing\BcryptHasher) {
                \Illuminate\Support\Facades\Hash::driver()->setRounds(4);
            }
        } catch (\Throwable $e) {}

        return \Illuminate\Support\Facades\DB::transaction(function () use ($razorpayOrderId, $razorpayPaymentId, $razorpaySignature) {
            $this->status = 'approved';
            $this->payment_verified = true;
            $this->payment_type = 'online';
            $this->razorpay_order_id = $razorpayOrderId;
            $this->razorpay_payment_id = $razorpayPaymentId;
            $this->razorpay_signature = $razorpaySignature;
            $this->payment_date = now();
            $this->verified_at = now();
            $this->save();

            $batchPrefix = $this->semesterBatch->code_prefix ?? ($this->semesterBatch->standard->stdname ?? 'BATCH');
            $batchPrefix = str_replace(' ', '', $batchPrefix);
            
            $studentsToInsert = [];
            $emailsToProcess = []; // Store emails to identify records later
            
            // Get valid columns
            $studentModel = new Student();
            $validColumns = $studentModel->getFillable();

            // Prepare data for bulk insert
            // Individual processing for Students and Invoices
            $studentModel = new Student();
            $validColumns = $studentModel->getFillable();

            foreach ($this->pendingStudents as $pendingStudent) {
                $studentData = $pendingStudent->student_data;
                while (is_string($studentData)) {
                    $decoded = json_decode($studentData, true);
                    if (json_last_error() !== JSON_ERROR_NONE || $decoded === $studentData) break;
                    $studentData = $decoded;
                }
                if (!is_array($studentData)) $studentData = [];

                // Generate credentials upfront
                $fname = strtolower(preg_replace('/\s+/', '', $studentData['fname'] ?? 'student'));
                $importId = $pendingStudent->pending_id;
                $username = "{$fname}_{$batchPrefix}_{$importId}";
                $mobileNo = $studentData['contact_no'] ?? '1234567890';

                $studentData['lmsuser'] = $username;
                $studentData['lmspswd'] = $mobileNo;
                
                // Filter and pad data to ensure identical keys
                $cleanData = [];
                foreach ($validColumns as $col) {
                    $cleanData[$col] = $studentData[$col] ?? null;
                }
                
                $student = $this->createStudentWithLinks(
                    $studentData, 
                    'Generated from Online Batch: ' . $this->batch_code, 
                    'PAID', 
                    0, 
                    $this->getPaymentModeId()
                );
                if ($student) {
                    \App\Jobs\EnrollStudentInEdmingleBatches::dispatch($student->student_id);
                }
                
                // Mark as registered
                $pendingStudent->is_registered = true;
                $pendingStudent->save();
            }
        });
    }

    public function getPaymentModeId()
    {
        if ($this->payment_type === 'online') {
            return \App\Models\PaymentMode::where('paymodename', 'Online')->value('paymode_id') ?: 1;
        }
        
        $method = $this->offline_payment_method;
        if ($method) {
            $mapped = \App\Models\PaymentMode::where('paymodename', 'LIKE', '%' . $method . '%')->value('paymode_id');
            if ($mapped) return $mapped;
        }
        
        return \App\Models\PaymentMode::where('paymodename', 'Offline')->value('paymode_id')
            ?: (\App\Models\PaymentMode::where('paymodename', 'Cash')->value('paymode_id') ?: 1);
    }

    public function reject($reason, $verifiedBy)
    {
        $this->status = 'rejected';
        $this->notes = $reason;
        $this->verified_by = $verifiedBy;
        $this->verified_at = now();
        $this->save();
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Helper to create student and link to school/initiator
     */
    private function createStudentWithLinks(array $studentData, $invoiceNotes, $invoiceStatus = 'PAID', $paymentAmount = 0, $paymentModeId = null)
    {
        // 1. Auto-fill from batch school if missing
        $school = $this->school;
        
        // Fallback: If batch has no school_id, try to get it from the uploader's assigned schools (for initiators)
        if (!$school && $this->uploader) {
            $assignedSchoolIds = $this->uploader->getAssignedSchoolIds();
            if (!empty($assignedSchoolIds)) {
                $school = School::find($assignedSchoolIds[0]);
            }
        }

        if ($school) {
            if (empty($studentData['school_id'])) {
                $studentData['school_id'] = $school->school_id;
            }
            
            if (empty($studentData['state_id'])) {
                $studentData['state_id'] = $school->state_id;
                if (empty($studentData['state_id']) && $school->taluka && $school->taluka->district) {
                    $studentData['state_id'] = $school->taluka->district->state_id;
                }
            }
            
            if (empty($studentData['dist_id'])) {
                $studentData['dist_id'] = $school->dist_id;
                if (empty($studentData['dist_id']) && $school->taluka) {
                    $studentData['dist_id'] = $school->taluka->dist_id;
                }
            }
            
            if (empty($studentData['takula_id'])) {
                $studentData['takula_id'] = $school->takula_id;
            }

            if (empty($studentData['institute'])) {
                $studentData['institute'] = $school->schoolname;
            }
            if (empty($studentData['school_name'])) {
                $studentData['school_name'] = $school->schoolname;
            }
            if (empty($studentData['school_address'])) {
                $studentData['school_address'] = trim(($school->address1 ?? '') . ', ' . ($school->address2 ?? ''), ', ');
            }
        }

        // 2. Create or update student (filter only fillable columns to avoid mass assignment issues and resolve unique email conflicts)
        $studentModel = new Student();
        $fillable = $studentModel->getFillable();
        $cleanStudentData = array_intersect_key($studentData, array_flip($fillable));

        // Ensure mandatory DB foreign keys have defaults if missing to prevent SQL constraint errors
        $cleanStudentData['studenttype_id'] = $cleanStudentData['studenttype_id'] ?? 2;
        $cleanStudentData['eduboard_id'] = $cleanStudentData['eduboard_id'] ?? 1;
        $cleanStudentData['std_id'] = $cleanStudentData['std_id'] ?? 1;
        $cleanStudentData['takula_id'] = $cleanStudentData['takula_id'] ?? 1;

        $cleanStudentData['fees_status'] = 'Paid';
        $cleanStudentData['batch_id'] = $this->batch_id;

        // Determine fee plan from coordinator verification first (takes priority over uploaded sheet value)
        if (!isset($this->_resolvedFeePlan)) {
            $feePlan = null;
            if ($this->fee_plan_id) {
                $feePlan = \App\Models\FeePlan::find($this->fee_plan_id);
            }
            if (!$feePlan && $this->sembatches_id) {
                $feePlan = \App\Models\FeePlan::where('sembatches_id', $this->sembatches_id)->first();
            }
            if (!$feePlan && $this->sembatches_id) {
                $feePlan = \App\Models\FeePlan::whereHas('semesterBatches', function ($q) {
                    $q->where('fee_plan_sembatches.sembatches_id', $this->sembatches_id);
                })->first();
            }
            $this->_resolvedFeePlan = $feePlan;
        }
        $feePlan = $this->_resolvedFeePlan;

        if ($feePlan) {
            $cleanStudentData['subscription'] = $feePlan->fee_name;
        } else {
            // Fallback to sheet value if valid
            $isValidSubscription = false;
            if (!empty($cleanStudentData['subscription'])) {
                if (!isset($this->_validSubscriptionNames)) {
                    $this->_validSubscriptionNames = \App\Models\FeePlan::pluck('fee_name')->toArray();
                }
                $isValidSubscription = in_array($cleanStudentData['subscription'], $this->_validSubscriptionNames);
            }
            if (!$isValidSubscription) {
                $cleanStudentData['subscription'] = 'Sankalp Fee Plan';
            }
        }

        // Sanitize phone/whatsapp numbers to safe 10-digit format
        if (!empty($cleanStudentData['whatsapp_no'])) {
            $whatsappClean = preg_replace('/\D/', '', $cleanStudentData['whatsapp_no']);
            if (strlen($whatsappClean) === 12 && str_starts_with($whatsappClean, '91')) {
                $whatsappClean = substr($whatsappClean, 2);
            } elseif (strlen($whatsappClean) === 11 && str_starts_with($whatsappClean, '0')) {
                $whatsappClean = substr($whatsappClean, 1);
            }
            $cleanStudentData['whatsapp_no'] = substr($whatsappClean, 0, 10);
        }
        if (!empty($cleanStudentData['contact_no'])) {
            $contactClean = preg_replace('/\D/', '', $cleanStudentData['contact_no']);
            if (strlen($contactClean) === 12 && str_starts_with($contactClean, '91')) {
                $contactClean = substr($contactClean, 2);
            } elseif (strlen($contactClean) === 11 && str_starts_with($contactClean, '0')) {
                $contactClean = substr($contactClean, 1);
            }
            $cleanStudentData['contact_no'] = substr($contactClean, 0, 10);
        }

        $student = null;
        if (!empty($cleanStudentData['email'])) {
            $student = Student::where('email', strtolower(trim($cleanStudentData['email'])))->first();
        }

        if ($student) {
            if (!empty($student->enrolment_no)) {
                unset($cleanStudentData['enrolment_no']);
            }
            $student->update($cleanStudentData);
        } else {
            $student = Student::create($cleanStudentData);
        }
        $student_id = $student->student_id;

        // 3. Create or update Guardian if provided
        if (!empty($studentData['guardian_fname']) || !empty($studentData['guardian_lname']) || !empty($studentData['guardian_name'])) {
            $gFname = $studentData['guardian_fname'] ?? null;
            $gMname = $studentData['guardian_mname'] ?? null;
            $gLname = $studentData['guardian_lname'] ?? null;
            $gGender = $studentData['guardian_gender'] ?? null;

            if (empty($gFname) && !empty($studentData['guardian_name'])) {
                $gNameParts = explode(' ', trim($studentData['guardian_name']));
                $gFname = $gNameParts[0] ?? 'Guardian';
                $gLname = (count($gNameParts) > 1) ? end($gNameParts) : ($studentData['lname'] ?? '');
                $gMname = (count($gNameParts) > 2) ? implode(' ', array_slice($gNameParts, 1, -1)) : '';
            }

            $gPhone = $studentData['guardian_contact'] ?? null;
            if (!empty($gPhone)) {
                $gPhoneClean = preg_replace('/\D/', '', $gPhone);
                if (strlen($gPhoneClean) === 12 && str_starts_with($gPhoneClean, '91')) {
                    $gPhoneClean = substr($gPhoneClean, 2);
                } elseif (strlen($gPhoneClean) === 11 && str_starts_with($gPhoneClean, '0')) {
                    $gPhoneClean = substr($gPhoneClean, 1);
                }
                $gPhone = substr($gPhoneClean, 0, 10);
            }

            $existingGuardian = Guardian::where('student_id', $student_id)->first();
            if ($existingGuardian) {
                $existingGuardian->update([
                    'fname' => $gFname ?: $existingGuardian->fname ?: 'Guardian',
                    'mname' => $gMname ?: $existingGuardian->mname ?: '',
                    'lname' => $gLname ?: $existingGuardian->lname ?: ($studentData['lname'] ?? ''),
                    'gender' => $gGender ?: $existingGuardian->gender,
                    'phone' => $gPhone ?: $existingGuardian->phone,
                    'relation_id' => $studentData['guardian_relation_id'] ?? $existingGuardian->relation_id ?? null,
                    'education' => $studentData['guardian_education'] ?? $existingGuardian->education ?? null,
                    'updated_at' => now(),
                ]);
            } else {
                Guardian::create([
                    'student_id' => $student_id,
                    'fname' => $gFname ?: 'Guardian',
                    'mname' => $gMname ?: '',
                    'lname' => $gLname ?: ($studentData['lname'] ?? ''),
                    'gender' => $gGender,
                    'phone' => $gPhone,
                    'relation_id' => $studentData['guardian_relation_id'] ?? null,
                    'education' => $studentData['guardian_education'] ?? null,
                    'createdby' => $this->uploaded_by,
                    'created_at' => now(),
                ]);
            }
        }

        // 4. Generate Invoice (with custom fee plan and partial payment support)
        try {
            $invoice = app(\App\Services\InvoiceService::class)->generateInvoice($student->student_id, $this->sembatches_id, [
                'status' => ($invoiceStatus === 'PAID') ? 'PAID' : 'ISSUED',
                'notes' => $invoiceNotes,
                'fee_plan_id' => $this->fee_plan_id, // Pass selected fee plan!
            ]);

            if ($invoiceStatus !== 'PAID' && $paymentAmount > 0) {
                // Get or fallback payment mode ID
                $paymodeId = $paymentModeId ?: (\App\Models\PaymentMode::where('paymodename', 'Offline')->value('paymode_id') ?: 1);

                // Create PaymentLog
                \App\Models\PaymentLog::create([
                    'invoice_id' => $invoice->invoice_id,
                    'paymode_id' => $paymodeId,
                    'processdatetime' => \Carbon\Carbon::now(),
                    'amount' => $paymentAmount,
                    'status' => '1', // success
                    'txnid' => 'BATCH_PARTIAL_' . strtoupper(uniqid()),
                    'notes' => 'Partial payment via batch verification ' . $this->batch_code,
                ]);

                // Update invoice status based on paid amount
                $totalPaid = $invoice->payments()->where('status', '1')->sum('amount');
                if ($totalPaid >= $invoice->total_due) {
                    $invoice->status = 'PAID';
                } elseif ($totalPaid > 0) {
                    $invoice->status = 'PARTIAL';
                } else {
                    $invoice->status = 'ISSUED';
                }
                $invoice->save();
            }

            // Sync student fees status dynamically
            \App\Models\Student::syncFeesStatus($student->student_id);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed invoice for student {$student->student_id}: " . $e->getMessage());
        }

        // 5. Link to Initiator if identified
        $initiatorUser = null;

        // A. From student record initiator contact column (if still present in some formats)
        if (!empty($student->initiator_contact)) {
            $contact = $student->initiator_contact;
            if (!isset($this->_contactInitiators)) {
                $this->_contactInitiators = [];
            }
            if (!array_key_exists($contact, $this->_contactInitiators)) {
                $this->_contactInitiators[$contact] = SysUser::where('phone', $contact)
                    ->orWhere('username', $contact)
                    ->orWhere('refcode', $contact)
                    ->first();
            }
            $initiatorUser = $this->_contactInitiators[$contact];
        }

        // B. Fallback to school initiator
        if (!$initiatorUser && $student->school_id) {
            if (!isset($this->_schoolInitiators)) {
                $this->_schoolInitiators = [];
            }
            if (!array_key_exists($student->school_id, $this->_schoolInitiators)) {
                $schoolInit = null;
                // If it's the batch's own school, reuse the loaded relation
                if ($this->school && $this->school->school_id == $student->school_id) {
                    $schoolObj = $this->school;
                } else {
                    $schoolObj = School::find($student->school_id);
                }
                if ($schoolObj && $schoolObj->initiator_id) {
                    $schoolInit = SysUser::find($schoolObj->initiator_id);
                }
                $this->_schoolInitiators[$student->school_id] = $schoolInit;
            }
            $initiatorUser = $this->_schoolInitiators[$student->school_id];
        }

        if ($initiatorUser) {
            // Check if student role can be assigned. Pivot table: userstudent
            $initiatorUser->students()->syncWithoutDetaching([$student->student_id]);
        }

        return $student;
    }
}
