<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Log;

class FixRegistrationInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'registrations:fix-invoices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate missing invoices and payment logs for already paid registered students';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Scanning for paid students missing invoices...");

        // Find students who are 'Paid' and have a subscription plan
        $students = Student::where('fees_status', 'Paid')
            ->whereNotNull('subscription')
            ->where('subscription', '!=', '')
            ->get();

        $invoiceService = new InvoiceService();
        $count = 0;

        foreach ($students as $student) {
            // Check if they already have an invoice for this plan
            $hasInvoice = \App\Models\Invoice::where('student_id', $student->student_id)
                ->whereHas('items', function($q) use ($student) {
                    $q->where('description', $student->subscription);
                })
                ->exists();

            if (!$hasInvoice) {
                $this->info("Generating invoice for Student ID: {$student->student_id} ({$student->fname} {$student->lname})");
                try {
                    $invoiceService->generateRegistrationInvoiceAndPayment(
                        $student, 
                        $student->razorpay_payment_id ?? 'MANUAL_SYNC_MIGRATION'
                    );
                    $count++;
                } catch (\Exception $e) {
                    $this->error("Failed for Student ID {$student->student_id}: " . $e->getMessage());
                    Log::error("Migration Invoice Error for Student ID {$student->student_id}: " . $e->getMessage());
                }
            }
        }

        $this->info("Completed! Generated {$count} missing invoices/payments.");
    }
}
