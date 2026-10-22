<?php

namespace App\Http\Controllers;

use App\Models\UploadBatch;
use App\Models\SemesterBatch;
use App\Models\School;
use App\Notifications\BatchRejectedNotification;
use App\Notifications\PaymentConfirmationNotification;
use Illuminate\Http\Request;
use App\Helpers\DateHelper;

class AccountantController extends Controller
{
    /**
     * Display a list of upload batches for verification
     */
    public function index(Request $request)
    {
        $query = UploadBatch::with(['semesterBatch.standard', 'uploader', 'verifier']);
        
        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Default to pending batches
            $query->where('status', 'pending');
        }
        
        if ($request->filled('sembatches_id')) {
            $query->where('sembatches_id', $request->sembatches_id);
        }
        
        if ($request->filled('date_from')) {
            $dateFrom = DateHelper::convertDateFormat($request->date_from);
            if ($dateFrom) {
                $query->whereDate('upload_date', '>=', $dateFrom);
            }
        }
        
        if ($request->filled('date_to')) {
            $dateTo = DateHelper::convertDateFormat($request->date_to);
            if ($dateTo) {
                $query->whereDate('upload_date', '<=', $dateTo);
            }
        }
        
        // Order by most recent first
        $query->orderBy('upload_date', 'desc');
        
        $batches = $query->paginate(15);
        
        // Get filter options
        $semesterBatches = SemesterBatch::orderBy('start_date', 'desc')->get();
        
        return view('accountant.batches.index', compact('batches', 'semesterBatches'));
    }
    
    /**
     * Display batch details with all students
     */
    public function show($id)
    {
        $batch = UploadBatch::with([
            'pendingStudents',
            'semesterBatch.standard',
            'uploader',
            'verifier'
        ])->findOrFail($id);
        
        return view('accountant.batches.show', compact('batch'));
    }
    
    /**
     * Show the approval form for a batch
     */
    public function showApprovalForm($id)
    {
        $batch = UploadBatch::with(['semesterBatch.standard', 'uploader', 'pendingStudents'])
            ->findOrFail($id);
        
        if ($batch->status !== 'pending') {
            return redirect()->route('accountant.batches.show', $batch->batch_id)
                ->with('error', 'This batch has already been processed.');
        }
        
        return view('accountant.batches.approve', compact('batch'));
    }
    
    /**
     * Approve a batch and convert pending students to registered
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'transaction_id' => 'required|string|max:100',
            'payment_date' => 'required|date_format_dmy',
            'total_amount' => 'required|numeric|min:0',
        ]);

        $paymentDate = DateHelper::convertDateFormat($request->payment_date);
        $request->merge(['payment_date' => $paymentDate]);
        
        $batch = UploadBatch::findOrFail($id);
        
        if ($batch->status !== 'pending') {
            return redirect()->route('accountant.batches.show', $batch->batch_id)
                ->with('error', 'This batch has already been processed.');
        }
        
        try {
            \DB::transaction(function () use ($batch, $request) {
                // Approve the batch
                $batch->approve(
                    null, // payment mode removed
                    $request->transaction_id,
                    $request->payment_date,
                    auth()->id()
                );
            });
            
            // Send payment confirmation notification to uploader
            if ($batch->uploader && $batch->uploader->email) {
                $batch->uploader->notify(new PaymentConfirmationNotification($batch));
            }
            
            return redirect()->route('accountant.batches.index')
                ->with('success', "Batch {$batch->batch_code} has been approved and {$batch->total_students} students have been registered.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to approve batch: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Show the rejection form for a batch
     */
    public function showRejectionForm($id)
    {
        $batch = UploadBatch::with(['semesterBatch.standard', 'uploader'])
            ->findOrFail($id);
        
        if ($batch->status !== 'pending') {
            return redirect()->route('accountant.batches.show', $batch->batch_id)
                ->with('error', 'This batch has already been processed.');
        }
        
        return view('accountant.batches.reject', compact('batch'));
    }
    
    /**
     * Reject a batch with a reason
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);
        
        $batch = UploadBatch::findOrFail($id);
        
        if ($batch->status !== 'pending') {
            return redirect()->route('accountant.batches.show', $batch->batch_id)
                ->with('error', 'This batch has already been processed.');
        }
        
        try {
            $batch->reject($request->reason, auth()->id());
            
            // Send rejection notification to uploader
            if ($batch->uploader && $batch->uploader->email) {
                $batch->uploader->notify(new BatchRejectedNotification($batch, $request->reason));
            }
            
            return redirect()->route('accountant.batches.index')
                ->with('success', "Batch {$batch->batch_code} has been rejected. The uploader will be notified.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to reject batch: ' . $e->getMessage())
                ->withInput();
        }
    }
}
