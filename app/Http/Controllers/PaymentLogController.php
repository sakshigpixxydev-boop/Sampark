<?php

namespace App\Http\Controllers;

use App\Models\PaymentLog;
use App\Models\PaymentMode;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\DateHelper;

class PaymentLogController extends Controller
{
    /**
     * Display a listing of payment logs.
     */
    public function index(Request $request)
    {
        if (!Auth::user()->hasPermission(10071)) {
            abort(403);
        }

        $query = PaymentLog::with(['paymentMode', 'invoice.student', 'invoice.school']);

        $this->applySchoolScope($query);
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $dateFrom = DateHelper::convertDateFormat($request->date_from);
            if ($dateFrom) {
                $query->whereDate('processdatetime', '>=', $dateFrom);
            }
        }
        
        if ($request->filled('date_to')) {
            $dateTo = DateHelper::convertDateFormat($request->date_to);
            if ($dateTo) {
                $query->whereDate('processdatetime', '<=', $dateTo);
            }
        }
        
        // Filter by invoice
        if ($request->filled('invoice_id')) {
            $query->where('invoice_id', $request->invoice_id);
        }
        
        $payments = $query->orderBy('processdatetime', 'desc')->paginate(15);
        
        return view('payments.index', compact('payments'));
    }
    
    /**
     * Show the form for creating a new payment log.
     */
    public function create(Request $request)
    {
        if (!Auth::user()->hasPermission(10072)) {
            abort(403);
        }

        $schoolIds = Auth::user()->restrictedSchoolIds();

        $paymentModes = PaymentMode::all();
        $invoicesQuery = Invoice::with(['student', 'school'])
            ->whereIn('status', ['ISSUED', 'PARTIAL']);

        if ($schoolIds !== null) {
            $invoicesQuery->whereIn('school_id', $schoolIds);
        }

        $invoices = $invoicesQuery->get();
        
        $selectedInvoice = null;
        if ($request->filled('invoice_id')) {
            $selectedInvoiceQuery = Invoice::with(['student', 'school'])->whereKey($request->invoice_id);

            if ($schoolIds !== null) {
                $selectedInvoiceQuery->whereIn('school_id', $schoolIds);
            }

            $selectedInvoice = $selectedInvoiceQuery->first();
        }
        
        return view('payments.create', compact('paymentModes', 'invoices', 'selectedInvoice'));
    }
    
    /**
     * Store a newly created payment log in storage.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->hasPermission(10072)) {
            abort(403);
        }

        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoice,invoice_id',
            'paymode_id' => 'required|exists:paymentmode,paymode_id',
            'processdatetime' => 'required|date_format_dmy',
            'txnid' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'orgresponse' => 'nullable|string',
        ]);
        
        $this->authoriseInvoice($validated['invoice_id']);

        $validated['processdatetime'] = DateHelper::convertDateFormat($validated['processdatetime']);
        
        // Set default status to PENDING (0)
        $validated['status'] = '0';
        
        $payment = PaymentLog::create($validated);
        
        // Update invoice status based on payment
        $this->updateInvoiceStatus($payment->invoice_id);
        
        return redirect()->route('payments.index')
            ->with('success', 'Payment log created successfully.');
    }
    
    
    /**
     * Display the specified payment log.
     */
    public function show(PaymentLog $payment)
    {
        if (!Auth::user()->hasPermission(10071)) {
            abort(403);
        }

        $this->authorisePayment($payment);

        $payment->load(['paymentMode', 'invoice.student', 'invoice.school', 'invoice.items.feePlan.semesterBatches']);
        
        return view('payments.show', compact('payment'));
    }
    
    /**
     * Show the form for editing the specified payment log.
     */
    public function edit(PaymentLog $payment)
    {
        if (!Auth::user()->hasPermission(10072)) {
            abort(403);
        }

        $this->authorisePayment($payment);

        $paymentModes = PaymentMode::all();
        $payment->load(['invoice.student', 'invoice.school']);
        
        return view('payments.edit', compact('payment', 'paymentModes'));
    }
    
    /**
     * Update the specified payment log in storage.
     */
    public function update(Request $request, PaymentLog $payment)
    {
        if (!Auth::user()->hasPermission(10072)) {
            abort(403);
        }

        $this->authorisePayment($payment);

        $validated = $request->validate([
            'paymode_id' => 'required|exists:paymentmode,paymode_id',
            'processdatetime' => 'required|date_format_dmy',
            'txnid' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'status' => 'required|in:0,1,2,3,4',
            'orgresponse' => 'nullable|string',
        ]);
        
        $validated['processdatetime'] = DateHelper::convertDateFormat($validated['processdatetime']);

        $payment->update($validated);
        
        // Update invoice status based on payment
        $this->updateInvoiceStatus($payment->invoice_id);
        
        return redirect()->route('payments.index')
            ->with('success', 'Payment log updated successfully.');
    }
    
    /**
     * Update payment status.
     */
    public function updateStatus(Request $request, PaymentLog $payment)
    {
        if (!Auth::user()->hasPermission(10072)) {
            abort(403);
        }

        $this->authorisePayment($payment);

        $validated = $request->validate([
            'status' => 'required|in:0,1,2,3,4',
        ]);
        
        $payment->update(['status' => $validated['status']]);
        
        // Update invoice status based on payment
        $this->updateInvoiceStatus($payment->invoice_id);
        
        return redirect()->back()
            ->with('success', 'Payment status updated successfully.');
    }
    
    /**
     * Limit a payment-log query to the schools the current user belongs to.
     */
    private function applySchoolScope($query): void
    {
        $schoolIds = Auth::user()->restrictedSchoolIds();

        if ($schoolIds === null) {
            return;
        }

        $query->whereHas('invoice', function ($q) use ($schoolIds) {
            $q->whereIn('school_id', $schoolIds);
        });
    }

    /**
     * Abort unless the payment belongs to one of the user's own schools.
     */
    private function authorisePayment(PaymentLog $payment): void
    {
        $this->authoriseInvoice($payment->invoice_id);
    }

    /**
     * Abort unless the invoice belongs to one of the user's own schools.
     */
    private function authoriseInvoice($invoiceId): void
    {
        $schoolIds = Auth::user()->restrictedSchoolIds();

        if ($schoolIds === null) {
            return;
        }

        $belongs = Invoice::whereKey($invoiceId)
            ->whereIn('school_id', $schoolIds)
            ->exists();

        if (!$belongs) {
            abort(403);
        }
    }

    /**
     * Update invoice status based on payments.
     */
    private function updateInvoiceStatus($invoiceId)
    {
        $invoice = Invoice::with('payments')->find($invoiceId);
        
        if (!$invoice) {
            return;
        }
        
        // Calculate total paid amount (only AUTHORIZED payments)
        $totalPaid = $invoice->payments()
            ->where('status', '1')
            ->sum('amount');
        
        // Update invoice status
        if ($totalPaid >= $invoice->total_due) {
            $invoice->status = 'PAID';
        } elseif ($totalPaid > 0) {
            $invoice->status = 'PARTIAL';
        } else {
            $invoice->status = 'ISSUED';
        }
        
        $invoice->save();

        if ($invoice->student_id) {
            \App\Models\Student::syncFeesStatus($invoice->student_id);
        }
    }
}
