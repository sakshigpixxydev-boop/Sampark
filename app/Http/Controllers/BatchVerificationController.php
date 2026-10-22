<?php

namespace App\Http\Controllers;

use App\Models\UploadBatch;
// use App\Models\PaymentMode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\DateHelper;

class BatchVerificationController extends Controller
{
    /**
     * Display listing of batches pending verification
     */
    public function index(Request $request)
    {
        $query = UploadBatch::with(['school', 'uploader', 'semesterBatch']);

        // Filter by payment status
        if ($request->has('payment_status')) {
            if ($request->payment_status === 'pending') {
                $query->where('status', 'pending')->where('payment_verified', false);
            } elseif ($request->payment_status === 'verified') {
                $query->where('payment_verified', true);
            }
        }

        // Filter by payment type
        if ($request->has('payment_type') && $request->payment_type != '') {
            $query->where('payment_type', $request->payment_type);
        }

        // Filter by school
        if ($request->has('school_id') && $request->school_id != '') {
            $query->where('school_id', $request->school_id);
        }

        // Date range filter
        if ($request->has('date_from') && $request->date_from != '') {
            $dateFrom = DateHelper::convertDateFormat($request->date_from);
            if ($dateFrom) {
                $query->whereDate('upload_date', '>=', $dateFrom);
            }
        }
        if ($request->has('date_to') && $request->date_to != '') {
            $dateTo = DateHelper::convertDateFormat($request->date_to);
            if ($dateTo) {
                $query->whereDate('upload_date', '<=', $dateTo);
            }
        }

        $batches = $query->orderBy('upload_date', 'desc')->paginate(20);

        // Get schools for filter dropdown
        $schools = \App\Models\School::active()->final()->orderBy('schoolname')->get();

        return view('batch-verification.index', compact('batches', 'schools'));
    }

    /**
     * Display the specified batch with students
     */
    public function show($id)
    {
        $batch = UploadBatch::with([
            'school',
            'uploader',
            'verifier',
            'semesterBatch',
            'pendingStudents'
        ])->findOrFail($id);

        // Paginate pending students
        $students = $batch->pendingStudents()->paginate(50);

        // Get options for coordinator updates
        $semesterBatches = \App\Models\SemesterBatch::with(['standard', 'feePlans'])->orderBy('start_date', 'desc')->get();

        return view('batch-verification.show', compact('batch', 'students', 'semesterBatches'));
    }

    /**
     * Verify and approve/reject batch
     */
    public function verify(Request $request, $id)
    {
        $batch = UploadBatch::findOrFail($id);

        // Check if batch is already verified
        if ($batch->payment_verified) {
            return redirect()->back()->with('error', 'This batch has already been verified.');
        }

        $action = $request->input('action'); // 'approve' or 'reject'

        if ($action === 'approve') {
            $validated = $request->validate([
                'sembatches_id' => 'required|exists:sembatches,sembatches_id',
                'transaction_id' => 'required|string|max:100',
                'bank_name' => 'nullable|string|max:255',
                'payment_date' => 'required|date_format_dmy',
                'amount_paid' => 'required|numeric|min:0',
                'selected_students' => 'required|string',
            ]);

            $validated['payment_date'] = DateHelper::convertDateFormat($validated['payment_date']);

            $selectedStudentIds = json_decode($validated['selected_students'], true);

            if (empty($selectedStudentIds)) {
                return redirect()->back()->with('error', 'Please select at least one student to enroll.');
            }

            // Save the Coordinator's inputs
            $sembatchesId = $validated['sembatches_id'];
            $batch->sembatches_id = $sembatchesId;

            // Resolve the fee plan dynamically for this semester batch and school
            $feePlan = \App\Models\FeePlan::where('sembatches_id', $sembatchesId)
                ->where('school_id', $batch->school_id)
                ->first()
                ?? \App\Models\FeePlan::where('sembatches_id', $sembatchesId)
                    ->whereNull('school_id')
                    ->first()
                ?? \App\Models\FeePlan::whereHas('semesterBatches', function ($q) use ($sembatchesId) {
                        $q->where('fee_plan_sembatches.sembatches_id', $sembatchesId);
                    })
                    ->where('school_id', $batch->school_id)
                    ->first()
                ?? \App\Models\FeePlan::whereHas('semesterBatches', function ($q) use ($sembatchesId) {
                        $q->where('fee_plan_sembatches.sembatches_id', $sembatchesId);
                    })
                    ->whereNull('school_id')
                    ->first()
                ?? \App\Models\FeePlan::where('sembatches_id', $sembatchesId)->first()
                ?? \App\Models\FeePlan::first();

            $batch->fee_plan_id = $feePlan ? $feePlan->fee_plan_id : null;
            $batch->save();

            $batch->approvePartial(
                $validated['amount_paid'],
                $selectedStudentIds,
                $validated['transaction_id'],
                $validated['payment_date'],
                Auth::user()->sysuser_id,
                $validated['bank_name'] ?? null
            );
            
            // Reload batch to check status
            if ($batch->fresh()->payment_verified) {
                return redirect()->route('batch-verification.index')
                    ->with('success', 'Batch fully paid and approved! All students enrolled.');
            } else {
                 return redirect()->back()
                    ->with('success', 'Partial payment recorded successfully. Selected students have been enrolled.');
            }

        } elseif ($action === 'reject') {
            $validated = $request->validate([
                'reason' => 'required|string',
            ]);

            $batch->reject($validated['reason'], Auth::user()->sysuser_id);

            return redirect()->route('batch-verification.index')
                ->with('success', 'Batch rejected successfully.');
        }

        return redirect()->back()->with('error', 'Invalid action.');
    }

    /**
     * Upload payment document proof for offline payment batch
     */
    public function uploadDocument(Request $request, $id)
    {
        $batch = UploadBatch::findOrFail($id);

        if ($batch->payment_verified) {
            return redirect()->back()->with('error', 'This batch has already been verified.');
        }

        $request->validate([
            'payment_document' => 'required|file|mimetypes:application/pdf,image/jpeg,image/jpg,image/png,image/gif|max:5120',
        ]);

        try {
            $fileUploadService = new \App\Services\FileUploadService();
            $path = $fileUploadService->storePublicFile($request->file('payment_document'), 'payment_docs');

            $batch->update([
                'payment_document_path' => $path
            ]);

            return redirect()->back()->with('success', 'Payment document uploaded successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Upload Document Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to upload document: ' . $e->getMessage());
        }
    }
}
