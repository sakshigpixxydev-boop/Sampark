<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\Invoice;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // withLeads() drops the excludeLeads global scope: on a fresh install
        // this migration runs before the is_lead column is added, and there are
        // no leads to exclude at this point in history anyway.
        // withTrashed() for the same reason: the deleted_at column is added by a
        // later migration, so the SoftDeletes scope would reference a column
        // that does not exist yet on a fresh install.
        $students = Student::withLeads()
            ->withTrashed()
            ->where('createdby', 'Migration_Engine')
            ->whereIn('subscription', [
                'Semester-02 (July 2026 to Dec 2026)',
                'Semester-05 (July 2026 to Dec 2026)'
            ])->get();

        DB::transaction(function() use ($students) {
            foreach ($students as $student) {
                // Update fees_status to Unpaid
                if ($student->fees_status !== 'Unpaid') {
                    $student->fees_status = 'Unpaid';
                    $student->save();
                }

                // Delete incorrect PAID invoices for Sem 02 / Sem 05
                $plansToDelete = \App\Models\FeePlan::where(function($q) {
                    $q->where('fee_name', 'like', 'Semester-02%')
                      ->orWhere('fee_name', 'like', 'Semester-05%')
                      ->orWhere('fee_name', 'like', 'Sem-02%')
                      ->orWhere('fee_name', 'like', 'Sem-05%')
                      ->orWhere('fee_name', 'like', 'Sem 02%')
                      ->orWhere('fee_name', 'like', 'Sem 05%');
                })->pluck('fee_plan_id')->toArray();

                if (empty($plansToDelete)) {
                    continue;
                }

                $invoices = Invoice::where('student_id', $student->student_id)
                    ->where('status', 'PAID')
                    ->whereHas('items', function($q) use ($plansToDelete) {
                        $q->whereIn('fee_plan_id', $plansToDelete);
                    })->get();

                foreach ($invoices as $invoice) {
                    DB::table('paymentlog')->where('invoice_id', $invoice->invoice_id)->delete();
                    DB::table('invoice_item')->where('invoice_id', $invoice->invoice_id)->delete();
                    $invoice->delete();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed for data cleanup migration
    }
};
