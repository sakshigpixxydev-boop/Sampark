<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Student;
use App\Models\SemesterBatch;
use App\Services\InvoiceCsvExportService;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use App\Helpers\DateHelper;

class InvoiceController extends Controller
{
    protected $invoiceService;
    protected $csvExporter;

    public function __construct(InvoiceService $invoiceService, InvoiceCsvExportService $csvExporter)
    {
        $this->invoiceService = $invoiceService;
        $this->csvExporter = $csvExporter;
    }

    /**
     * Build the invoice query shared by the CSV and PDF exports:
     * bulk selection or filters, then role-based scoping.
     */
    private function buildExportQuery(Request $request)
    {
        $query = Invoice::with(InvoiceCsvExportService::eagerLoads());

        // Handle bulk selection
        if ($request->filled('selected_invoices')) {
            $selectedIds = explode(',', $request->selected_invoices);
            $query->whereIn('invoice_id', $selectedIds);
        } else {
            // Apply filters if no specific selection
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('school_id')) {
                $query->where('school_id', $request->school_id);
            }

            if ($request->filled('student_id')) {
                $query->where('student_id', $request->student_id);
            }

            if ($request->filled('date_from')) {
                $dateFrom = DateHelper::convertDateFormat($request->date_from);
                if ($dateFrom) {
                    $query->where('invoice_date', '>=', $dateFrom);
                }
            }

            if ($request->filled('date_to')) {
                $dateTo = DateHelper::convertDateFormat($request->date_to);
                if ($dateTo) {
                    $query->where('invoice_date', '<=', $dateTo);
                }
            }
        }

        // Role-based filtering (always apply)
        $user = Auth::user();
        if ($user->hasRole('School')) {
            $schoolIds = $user->schools->pluck('school_id');
            $query->whereIn('school_id', $schoolIds);
        } elseif ($user->hasRole('Initiator')) {
            $schoolIds = $user->getAssignedSchoolIds();
            $query->whereIn('school_id', $schoolIds);
        }

        return $query;
    }

    /**
     * Export invoices to CSV
     */
    public function export(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $invoices = $this->csvExporter->applyOrdering($query)->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="invoices_export_' . date('Y-m-d_H-i-s') . '.csv"',
        ];

        return Response::stream($this->csvExporter->streamCallback($invoices), 200, $headers);
    }

    /**
     * Download Invoice as PDF
     */
    public function downloadPdf($invoiceId)
    {
        $invoice = Invoice::with(InvoiceCsvExportService::eagerLoads())->findOrFail($invoiceId);

        $invoiceData = $this->invoiceService->buildPdfViewData($invoice, true);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.invoice', $invoiceData);
        return $pdf->download('Invoice_' . $invoice->invoice_id . '.pdf');
    }

    /**
     * Export selected or filtered invoices as a merged PDF
     */
    public function exportPdf(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $invoices = $this->csvExporter->applyOrdering($query)->get();

        if ($invoices->isEmpty()) {
            return redirect()->back()->with('error', 'No invoices found to export.');
        }

        $combinedBody = '';

        foreach ($invoices as $index => $invoice) {
            $invoiceData = $this->invoiceService->buildPdfViewData($invoice, false);

            // Render view and extract body content
            $renderedHtml = view('admin.invoice', $invoiceData)->render();
            
            if (preg_match('/<body>(.*?)<\/body>/s', $renderedHtml, $matches)) {
                $bodyContent = $matches[1];
            } else {
                $bodyContent = $renderedHtml;
            }

            // Append with page break if not the last item
            if ($index < count($invoices) - 1) {
                $combinedBody .= '<div class="invoice-container" style="page-break-after: always;">' . $bodyContent . '</div>';
            } else {
                $combinedBody .= '<div class="invoice-container">' . $bodyContent . '</div>';
            }
        }

        $finalHtml = '<!DOCTYPE html><html><head><title>Bulk Invoices</title><style>' . 
            'body { font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; color: #1e293b; margin: 0; padding: 10px; font-size: 13px; line-height: 1.5; }' .
            '.invoice-header { border-bottom: 2px solid #00bde0; padding-bottom: 12px; margin-bottom: 25px; }' .
            '.invoice-title { font-size: 22px; font-weight: bold; color: #0f172a; margin: 0; text-transform: uppercase; letter-spacing: 0.5px; }' .
            '.address-table { width: 100%; margin-bottom: 25px; border-collapse: collapse; }' .
            '.address-table td { padding: 0; vertical-align: top; }' .
            '.academy-title, .student-title { font-size: 15px; font-weight: bold; color: #0f172a; margin: 0 0 6px 0; }' .
            '.address-text { color: #334155; line-height: 1.5; font-size: 13px; }' .
            '.meta-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; table-layout: fixed; }' .
            '.meta-table td { padding: 6px 4px; vertical-align: middle; font-size: 13px; word-wrap: break-word; }' .
            '.meta-label { font-weight: bold; color: #475569; }' .
            '.meta-value { color: #0f172a; font-weight: bold; }' .
            '.items { width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 30px; }' .
            '.items th { background-color: #f1f5f9; color: #0f172a; font-weight: bold; border: 1px solid #cbd5e1; padding: 10px 8px; font-size: 12px; text-transform: uppercase; }' .
            '.items td { border: 1px solid #cbd5e1; padding: 10px 8px; font-size: 12px; color: #1e293b; }' .
            '.align-right { text-align: right; }' .
            '.align-center { text-align: center; }' .
            '.total-row td { font-weight: bold; background-color: #f8fafc; border-top: 2px solid #cbd5e1; color: #0f172a; }' .
            '.footer { margin-top: 80px; font-size: 11px; color: #475569; line-height: 1.6; border-top: 1px solid #cbd5e1; padding-top: 15px; }' .
            '.footer-note { margin-bottom: 4px; }' .
            '</style></head><body>' . $combinedBody . '</body></html>';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($finalHtml);
        return $pdf->download('Invoices_Export_' . date('Y-m-d_H-i-s') . '.pdf');
    }

    /**
     * Display a listing of invoices
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['student', 'school']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('date_from')) {
            $dateFrom = DateHelper::convertDateFormat($request->date_from);
            if ($dateFrom) {
                $query->where('invoice_date', '>=', $dateFrom);
            }
        }

        if ($request->filled('date_to')) {
            $dateTo = DateHelper::convertDateFormat($request->date_to);
            if ($dateTo) {
                $query->where('invoice_date', '<=', $dateTo);
            }
        }

        // Role-based filtering
        $user = Auth::user();
        if ($user->hasRole('School')) {
            $schoolIds = $user->schools->pluck('school_id');
            $query->whereIn('school_id', $schoolIds);
        } elseif ($user->hasRole('Initiator')) {
            $schoolIds = $user->getAssignedSchoolIds();
            $query->whereIn('school_id', $schoolIds);
        }

        $invoices = $query->orderBy('invoice_date', 'desc')->orderBy('invoice_id', 'asc')->paginate(20);

        return view('invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new invoice
     */
    public function create()
    {
        $students = Student::with(['school', 'standard'])->get();
        $semesterBatches = SemesterBatch::all();
        $schools = \App\Models\School::all();

        return view('invoices.create', compact('students', 'semesterBatches', 'schools'));
    }

    /**
     * Store a newly created invoice
     */
    public function store(Request $request)
    {
        $invoiceType = $request->input('invoice_type', 'student');

        if ($invoiceType === 'school') {
            $request->validate([
                'school_id' => 'required|exists:school,school_id',
                'due_date' => 'nullable|date_format_dmy',
                'notes' => 'nullable|string|max:500',
                'status' => 'nullable|in:DRAFT,ISSUED',
                'items' => 'required|array|min:1',
                'items.*.description' => 'required|string|max:255',
                'items.*.amount' => 'required|numeric|min:0',
            ]);

            $dueDate = DateHelper::convertDateFormat($request->due_date);

            try {
                $invoice = $this->invoiceService->generateSchoolInvoice([
                    'school_id' => $request->school_id,
                    'due_date' => $dueDate,
                    'notes' => $request->notes,
                    'status' => $request->status ?? 'DRAFT',
                    'items' => $request->items,
                ]);

                return redirect()->route('invoices.show', $invoice->invoice_id)
                    ->with('success', 'School invoice generated successfully.');
            } catch (\Exception $e) {
                return back()->withInput()
                    ->with('error', 'Failed to generate school invoice: ' . $e->getMessage());
            }
        } else {
            $request->validate([
                'student_id' => 'required|exists:student,student_id',
                'semester_batch_id' => 'required|exists:sembatches,sembatches_id',
                'due_date' => 'nullable|date_format_dmy',
                'notes' => 'nullable|string|max:500',
                'status' => 'nullable|in:DRAFT,ISSUED',
            ]);

            $dueDate = DateHelper::convertDateFormat($request->due_date);

            try {
                $invoice = $this->invoiceService->generateInvoice(
                    $request->student_id,
                    $request->semester_batch_id,
                    [
                        'due_date' => $dueDate,
                        'notes' => $request->notes,
                        'status' => $request->status ?? 'DRAFT',
                    ]
                );

                return redirect()->route('invoices.show', $invoice->invoice_id)
                    ->with('success', 'Student invoice generated successfully.');
            } catch (\Exception $e) {
                return back()->withInput()
                    ->with('error', 'Failed to generate student invoice: ' . $e->getMessage());
            }
        }
    }

    /**
     * Mark invoice as completely paid manually
     */
    public function markAsPaid(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        // Check permission: Admin, School Coordinator, or Accountant
        $user = Auth::user();
        if (!$user->hasRole('Admin') && !$user->hasRole('School Coordinator') && !$user->hasRole('Accountant')) {
            return redirect()->back()->with('error', 'Only Admins, School Coordinators, or Accountants can perform this action.');
        }

        // Calculate remaining balance
        $totalPaid = $invoice->payments()->where('status', '1')->sum('amount');
        $remainingBalance = $invoice->total_due - $totalPaid;

        if ($remainingBalance <= 0) {
            $invoice->status = 'PAID';
            $invoice->save();
            if ($invoice->student_id) {
                \App\Models\Student::syncFeesStatus($invoice->student_id);
            }
            return redirect()->back()->with('success', 'Invoice is already fully paid.');
        }

        // Find or fallback cash/offline payment mode
        $cashPaymodeId = \App\Models\PaymentMode::where('paymodename', 'Cash')->value('paymode_id')
            ?: (\App\Models\PaymentMode::where('paymodename', 'Offline')->value('paymode_id')
                ?: (\App\Models\PaymentMode::where('paymodename', 'Manual')->value('paymode_id') ?: 1));

        // Create PaymentLog
        \App\Models\PaymentLog::create([
            'invoice_id' => $invoice->invoice_id,
            'paymode_id' => $cashPaymodeId,
            'processdatetime' => \Carbon\Carbon::now(),
            'amount' => $remainingBalance,
            'status' => '1', // success
            'txnid' => 'MANUAL_CASH_FULL_' . strtoupper(uniqid()),
            'notes' => 'Marked as complete paid by ' . $user->fname . ' ' . $user->lname . ' (' . ($user->role ? $user->role->rolename : 'Unknown Role') . ')',
        ]);

        $invoice->status = 'PAID';
        $invoice->save();

        if ($invoice->student_id) {
            \App\Models\Student::syncFeesStatus($invoice->student_id);
        }

        return redirect()->back()->with('success', 'Invoice has been marked as fully paid successfully.');
    }

    /**
     * Display the specified invoice
     */
    public function show($id)
    {
        $invoice = Invoice::with(['items.feePlan', 'student.school', 'student.standard', 'payments'])
            ->findOrFail($id);

        // Check permissions
        $user = Auth::user();
        if ($user->hasRole('School')) {
            $schoolIds = $user->schools->pluck('school_id');
            if (!$schoolIds->contains($invoice->school_id)) {
                abort(403, 'Unauthorized access to this invoice.');
            }
        } elseif ($user->hasRole('Initiator')) {
            $schoolIds = $user->getAssignedSchoolIds();
            if (!in_array($invoice->school_id, $schoolIds)) {
                abort(403, 'Unauthorized access to this invoice.');
            }
        }

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Update the specified invoice
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:DRAFT,ISSUED,PARTIAL,PAID,VOID',
            'notes' => 'nullable|string|max:500',
            'due_date' => 'nullable|date_format_dmy',
        ]);

        $dueDate = DateHelper::convertDateFormat($request->due_date);

        $invoice = Invoice::findOrFail($id);

        // Check permissions
        $user = Auth::user();
        if ($user->hasRole('School')) {
            $schoolIds = $user->schools->pluck('school_id');
            if (!$schoolIds->contains($invoice->school_id)) {
                abort(403, 'Unauthorized access to this invoice.');
            }
        }

        $invoice->status = $request->status;
        $invoice->notes = $request->notes;
        $invoice->due_date = $dueDate;
        $invoice->updated_at = now();
        $invoice->save();

        return redirect()->route('invoices.show', $invoice->invoice_id)
            ->with('success', 'Invoice updated successfully.');
    }

    /**
     * Void the specified invoice
     */
    public function void($id)
    {
        try {
            $invoice = Invoice::findOrFail($id);

            // Check permissions
            $user = Auth::user();
            if ($user->hasRole('School')) {
                $schoolIds = $user->schools->pluck('school_id');
                if (!$schoolIds->contains($invoice->school_id)) {
                    abort(403, 'Unauthorized access to this invoice.');
                }
            }

            $this->invoiceService->voidInvoice($id);

            return redirect()->route('invoices.show', $id)
                ->with('success', 'Invoice voided successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to void invoice: ' . $e->getMessage());
        }
    }

    /**
     * Recalculate invoice totals
     */
    public function recalculate($id)
    {
        try {
            $invoice = Invoice::findOrFail($id);

            // Check permissions
            $user = Auth::user();
            if ($user->hasRole('School')) {
                $schoolIds = $user->schools->pluck('school_id');
                if (!$schoolIds->contains($invoice->school_id)) {
                    abort(403, 'Unauthorized access to this invoice.');
                }
            }

            $this->invoiceService->recalculateInvoice($id);

            return redirect()->route('invoices.show', $id)
                ->with('success', 'Invoice recalculated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to recalculate invoice: ' . $e->getMessage());
        }
    }
}
