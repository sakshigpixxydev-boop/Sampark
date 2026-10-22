<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\FeePlan;
use App\Models\DiscountRule;
use App\Models\Student;
use Carbon\Carbon;

class InvoiceService
{
    /**
     * Generate an invoice for a student based on semester batch
     *
     * @param int $studentId
     * @param int $semesterBatchId
     * @param array $options Additional options (due_date, notes, etc.)
     * @return Invoice
     */
    public function generateInvoice($studentId, $semesterBatchId, array $options = [])
    {
        $student = Student::allCohorts()->with(['school', 'standard'])->findOrFail($studentId);
        
        // Fetch applicable fee plans
        if (!empty($options['fee_plan_id'])) {
            $feePlans = FeePlan::where('fee_plan_id', $options['fee_plan_id'])->get();
        } else {
            $feePlans = $this->getApplicableFeePlans($student, $semesterBatchId);
        }
        
        // Fetch batch for fallback fee
        $batch = \App\Models\SemesterBatch::find($semesterBatchId);
        $baseFee = $batch ? ($batch->fees ?? 0) : 0;
        
        // Calculate subtotal: If fee plans exist, use them. Otherwise use base batch fee.
        $subtotal = $feePlans->count() > 0 ? $feePlans->sum('amount') : $baseFee;
        
        if ($subtotal <= 0 && $feePlans->isEmpty() && (!$batch || $batch->fees <= 0)) {
            throw new \Exception('No applicable fee plans or semester batch fees found for this student.');
        }
        
        // Apply discount rules (school-level first, then student-level)
        $discountTotal = $this->calculateDiscounts($student, $subtotal);
        
        // Calculate total_due = subtotal - discount_total
        $totalDue = $subtotal - $discountTotal;
        
        // Create invoice
        $invoice = Invoice::create([
            'invoice_date' => $options['invoice_date'] ?? Carbon::now(),
            'due_date' => $options['due_date'] ?? Carbon::now()->addDays(30),
            'notes' => $options['notes'] ?? null,
            'total_before_discount' => $subtotal,
            'discount_total' => $discountTotal,
            'total_due' => $totalDue,
            'status' => $options['status'] ?? 'DRAFT',
            'school_id' => $student->school_id,
            'student_id' => $student->student_id,
        ]);
        
        // Create invoice line items
        foreach ($feePlans as $feePlan) {
            InvoiceItem::create([
                'invoice_id' => $invoice->invoice_id,
                'fee_plan_id' => $feePlan->fee_plan_id,
                'description' => $feePlan->fee_name,
                'qty' => 1,
                'unit_amount' => $feePlan->amount,
                'line_amount' => $feePlan->amount,
            ]);
        }
        
        return $invoice->load('items', 'student', 'school');
    }
    
    /**
     * Fetch applicable fee plans for student/school/batch
     *
     * @param Student $student
     * @param int $semesterBatchId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getApplicableFeePlans(Student $student, $semesterBatchId)
    {
        $today = Carbon::now();
        
        return FeePlan::where(function($q) use ($semesterBatchId) {
                $q->where('sembatches_id', $semesterBatchId)
                  ->orWhereHas('semesterBatches', function ($q2) use ($semesterBatchId) {
                      $q2->where('fee_plan_sembatches.sembatches_id', $semesterBatchId);
                  });
            })
            ->where(function($q) use ($student) {
                $q->where('school_id', $student->school_id)
                  ->orWhereNull('school_id');
            })
            ->where('valid_from', '<=', $today)
            ->where(function($query) use ($today) {
                $query->whereNull('valid_to')
                      ->orWhere('valid_to', '>=', $today);
            })
            ->get();
    }
    
    /**
     * Calculate total discounts (school-level first, then student-level)
     *
     * @param Student $student
     * @param float $subtotal
     * @return float
     */
    protected function calculateDiscounts(Student $student, $subtotal)
    {
        $today = Carbon::now();
        $totalDiscount = 0;
        
        // Apply school-level discounts first
        $schoolDiscounts = DiscountRule::where('scope', 'SCHOOL')
            ->where('school_id', $student->school_id)
            ->where('active', true)
            ->where('valid_from', '<=', $today)
            ->where(function($query) use ($today) {
                $query->whereNull('valid_to')
                      ->orWhere('valid_to', '>=', $today);
            })
            ->get();
        
        foreach ($schoolDiscounts as $discount) {
            $totalDiscount += $this->applyDiscount($discount, $subtotal);
        }
        
        // Then apply student-level discounts
        $studentDiscounts = DiscountRule::where('scope', 'STUDENT')
            ->where('student_id', $student->student_id)
            ->where('active', true)
            ->where('valid_from', '<=', $today)
            ->where(function($query) use ($today) {
                $query->whereNull('valid_to')
                      ->orWhere('valid_to', '>=', $today);
            })
            ->get();
        
        foreach ($studentDiscounts as $discount) {
            $totalDiscount += $this->applyDiscount($discount, $subtotal);
        }
        
        return $totalDiscount;
    }
    
    /**
     * Apply a single discount rule
     *
     * @param DiscountRule $discount
     * @param float $subtotal
     * @return float
     */
    protected function applyDiscount(DiscountRule $discount, $subtotal)
    {
        if ($discount->discount_type === 'FLAT') {
            return $discount->discount_value;
        } else { // PERCENT
            return ($subtotal * $discount->discount_value / 100);
        }
    }
    
    /**
     * Update invoice status
     *
     * @param int $invoiceId
     * @param string $status
     * @return Invoice
     */
    public function updateStatus($invoiceId, $status)
    {
        $validStatuses = ['DRAFT', 'ISSUED', 'PARTIAL', 'PAID', 'VOID'];
        
        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException("Invalid status: {$status}");
        }
        
        $invoice = Invoice::findOrFail($invoiceId);
        $invoice->status = $status;
        $invoice->updated_at = Carbon::now();
        $invoice->save();
        
        return $invoice;
    }
    
    /**
     * Void an invoice
     *
     * @param int $invoiceId
     * @return Invoice
     */
    public function voidInvoice($invoiceId)
    {
        return $this->updateStatus($invoiceId, 'VOID');
    }
    
    /**
     * Recalculate invoice totals
     *
     * @param int $invoiceId
     * @return Invoice
     */
    public function recalculateInvoice($invoiceId)
    {
        $invoice = Invoice::with(['items', 'student'])->findOrFail($invoiceId);
        
        // Recalculate subtotal from items
        $subtotal = $invoice->items->sum('line_amount');
        $invoice->total_before_discount = $subtotal;
        
        // Recalculate discounts
        if ($invoice->student) {
            $invoice->discount_total = $this->calculateDiscounts($invoice->student, $subtotal);
        }
        
        // Recalculate total due
        $invoice->total_due = $invoice->total_before_discount - $invoice->discount_total;
        $invoice->updated_at = Carbon::now();
        $invoice->save();
        
        return $invoice;
    }

    /**
     * Generate an invoice and payment log for a registration fee payment.
     *
     * @param Student $student
     * @param string|null $paymentId
     * @param array|null $razorpayResponse
     * @return Invoice|null
     */
    public function generateRegistrationInvoiceAndPayment($student, $paymentId = null, $razorpayResponse = null)
    {
        // Don't duplicate if an invoice for this fee plan already exists and is paid
        $existingInvoice = Invoice::where('student_id', $student->student_id)
            ->whereHas('items', function($q) use ($student) {
                $q->where('description', $student->subscription);
            })->orderBy('created_at', 'desc')->first();

        if ($existingInvoice && $existingInvoice->status === 'PAID') {
            return $existingInvoice;
        }

        $plan = FeePlan::where('fee_name', $student->subscription)
            ->where(function($q) use ($student) {
                $q->where('school_id', $student->school_id)
                  ->orWhereNull('school_id');
            })
            ->orderByRaw('school_id DESC')
            ->first();
        if (!$plan) {
            return null;
        }

        $invoice = $existingInvoice;

        if (!$invoice) {
            $invoice = Invoice::create([
                'invoice_date' => Carbon::now(),
                'due_date' => Carbon::now(),
                'total_before_discount' => $plan->amount,
                'discount_total' => 0,
                'total_due' => $plan->amount,
                'status' => 'PAID',
                'school_id' => $student->school_id,
                'student_id' => $student->student_id,
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->invoice_id,
                'fee_plan_id' => $plan->fee_plan_id,
                'description' => $plan->fee_name,
                'qty' => 1,
                'unit_amount' => $plan->amount,
                'line_amount' => $plan->amount,
            ]);
        } else {
            $invoice->update(['status' => 'PAID']);
        }

        // Add payment log if not exists for this txnid
        if ($paymentId) {
            $existingPayment = \App\Models\PaymentLog::where('txnid', $paymentId)->first();
            if ($existingPayment) {
                return $invoice;
            }
        }

        // Get 'Online' payment mode ID, fallback to 1
        $onlinePaymodeId = \App\Models\PaymentMode::where('paymodename', 'Online')->value('paymode_id') ?: 1;

        \App\Models\PaymentLog::create([
            'invoice_id' => $invoice->invoice_id,
            'paymode_id' => $onlinePaymodeId,
            'processdatetime' => Carbon::now(),
            'amount' => $plan->amount,
            'status' => '1',
            'txnid' => $paymentId ?: 'MANUAL_SYNC',
            'orgresponse' => $razorpayResponse ? json_encode($razorpayResponse) : null,
        ]);

        return $invoice;
    }

    /**
     * Build the view data for the invoice PDF template.
     *
     * Shared by InvoiceController::downloadPdf() and ::exportPdf(). The only
     * behavioural difference between those two paths is the payment date, so
     * that is the one thing parameterised here: the single-invoice download
     * prefers the payment log's timestamp, the bulk export always uses the
     * invoice date.
     *
     * @param  bool  $paymentDateFromPaymentLog
     * @return array
     */
    public function buildPdfViewData(Invoice $invoice, $paymentDateFromPaymentLog = true)
    {
        $taxService = app(\App\Services\InvoiceTaxService::class);

        $student = $invoice->student;
        $tax = $taxService->resolve($invoice);

        $isIndia = $tax['is_india'];
        $baseAmount = $tax['taxable'];
        $cgst = $tax['cgst'];
        $sgst = $tax['sgst'];
        $igst = $tax['igst'];

        $payment = $invoice->payments->first();

        $order_date = $invoice->invoice_date ? $invoice->invoice_date->format('d-m-Y') : '';

        if ($paymentDateFromPaymentLog) {
            $payment_date = $payment
                ? ($payment->processdatetime ? $payment->processdatetime->format('d-m-Y') : '')
                : ($invoice->invoice_date ? $invoice->invoice_date->format('d-m-Y') : '');
        } else {
            $payment_date = $invoice->invoice_date ? $invoice->invoice_date->format('d-m-Y') : '';
        }

        $districtName = $student && $student->district ? $student->district->dname : '';
        $stateName = $student && $student->state ? $student->state->sname : '';
        $countryName = $student && $student->country ? $student->country->cname : 'India';

        $address = '';
        if ($student) {
            $addressParts = array_filter([
                $student->address,
                $student->taluka ? $student->taluka->tname : '',
                $districtName,
                $student->pincode,
                $stateName,
                $countryName
            ]);
            $address = implode(' ', $addressParts);
        }

        // Generate items array
        $itemsList = [];
        foreach ($invoice->items as $item) {
            $itemsList[] = [
                'sac' => '9992',
                'course' => $item->description,
                'amount' => number_format($taxService->itemTaxable($item, $tax), 4, '.', '')
            ];
        }

        if (empty($itemsList) && $student) {
            $itemsList[] = [
                'sac' => '9992',
                'course' => $student->subscription ?: 'Fees Plan',
                'amount' => number_format($baseAmount, 4, '.', '')
            ];
        }

        // Number to words for final amount
        $finalAmount = floatval($baseAmount + $cgst + $sgst + $igst);

        try {
            $numberToWords = new \NumberToWords\NumberToWords();
            $numberTransformer = $numberToWords->getNumberTransformer('en');
            $finalAmountWord = strtoupper($numberTransformer->toWords($finalAmount) . ' ONLY');
        } catch (\Exception $e) {
            $finalAmountWord = strtoupper(number_format($finalAmount, 2) . ' ONLY');
        }

        return [
            'student_name' => $student ? ($student->fname . ' ' . $student->mname . ' ' . $student->lname) : 'N/A',
            'address' => $address ?: 'N/A',
            'phone' => $student ? $student->contact_no : 'N/A',
            'email' => $student ? $student->email : 'N/A',
            'isIndia' => $isIndia,
            'slug' => '',
            'order_no' => $payment ? ($payment->txnid ?: 'N/A') : ($student ? ($student->razorpay_order_id ?: 'N/A') : 'N/A'),
            'invoice_no' => \App\Services\InvoiceCsvExportService::invoiceNumberFor($invoice->invoice_id),
            'order_date' => $order_date,
            'payment_date' => $payment_date,
            'place' => $districtName ?: 'N/A',
            'items' => $itemsList,
            'cgst' => number_format($cgst, 4, '.', ''),
            'sgst' => number_format($sgst, 4, '.', ''),
            'igst' => number_format($igst, 4, '.', ''),
            'sub_total' => number_format($baseAmount, 4, '.', ''),
            'final_amount' => number_format($finalAmount, 4, '.', ''),
            'final_amount_word' => $finalAmountWord,
        ];
    }

    /**
     * Generate custom invoice for a school
     *
     * @param array $data
     * @return Invoice
     */
    public function generateSchoolInvoice(array $data)
    {
        $subtotal = 0;
        foreach ($data['items'] as $item) {
            $subtotal += floatval($item['amount']);
        }

        $invoice = Invoice::create([
            'invoice_date' => $data['invoice_date'] ?? Carbon::now(),
            'due_date' => $data['due_date'] ?? Carbon::now()->addDays(30),
            'notes' => $data['notes'] ?? null,
            'total_before_discount' => $subtotal,
            'discount_total' => 0,
            'total_due' => $subtotal,
            'status' => $data['status'] ?? 'DRAFT',
            'school_id' => $data['school_id'],
            'student_id' => null,
        ]);

        $itemsData = [];
        foreach ($data['items'] as $item) {
            $itemsData[] = [
                'invoice_id' => $invoice->invoice_id,
                'fee_plan_id' => null,
                'description' => $item['description'],
                'qty' => 1,
                'unit_amount' => floatval($item['amount']),
                'line_amount' => floatval($item['amount']),
            ];
        }

        if (!empty($itemsData)) {
            InvoiceItem::insert($itemsData);
        }

        return $invoice->load('items', 'school');
    }
}
