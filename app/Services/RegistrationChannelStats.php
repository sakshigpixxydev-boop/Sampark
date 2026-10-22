<?php

namespace App\Services;

use App\Models\Student;
use App\Models\UploadBatch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Uniform registration metrics for one intake channel.
 *
 * Both channels return the same array shape so a single view partial can render
 * either one — only the numbers differ. Where a metric has to be derived
 * differently per channel (money, most obviously) the difference is confined to
 * this class rather than leaking into the view.
 */
class RegistrationChannelStats
{
    public const CHANNEL_SCHOOL = 'school';
    public const CHANNEL_REGULAR = 'regular';

    /**
     * @param  array|null  $schoolIds  Schools the viewer is limited to, or null for all.
     */
    public function forChannel(string $channel, ?array $schoolIds = null): array
    {
        return $channel === self::CHANNEL_SCHOOL
            ? $this->schoolChannel($schoolIds)
            : $this->regularChannel($schoolIds);
    }

    /**
     * Students who came in through a school: the registration link or a bulk
     * import batch.
     */
    private function schoolChannel(?array $schoolIds): array
    {
        $totals = $this->base($schoolIds, self::CHANNEL_SCHOOL)
            ->selectRaw($this->headlineSelect())
            ->first();

        $status = $this->base($schoolIds, self::CHANNEL_SCHOOL)
            ->selectRaw("
                SUM(CASE WHEN school_payment_status = 'Approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN school_payment_status = 'Pending'  THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN school_payment_status = 'Rejected' THEN 1 ELSE 0 END) as rejected
            ")
            ->first();

        $split = $this->base($schoolIds, self::CHANNEL_SCHOOL)
            ->selectRaw("
                SUM(CASE WHEN registered_via_school_link = 1 THEN 1 ELSE 0 END) as via_link,
                SUM(CASE WHEN registered_via_school_link = 0 THEN 1 ELSE 0 END) as via_bulk
            ")
            ->first();

        // School-link payments are verified offline against a screenshot and
        // never reach paymentlog, so they are summed from the student row; bulk
        // imports are paid per batch.
        $linkCollected = (float) $this->base($schoolIds, self::CHANNEL_SCHOOL)
            ->where('school_payment_status', 'Approved')
            ->sum('school_payment_amount');

        $batchCollected = (float) UploadBatch::query()
            ->when($schoolIds !== null, fn ($q) => $q->whereIn('school_id', $schoolIds))
            ->where('status', 'approved')
            ->sum('paid_amount');

        return [
            'key' => self::CHANNEL_SCHOOL,
            'label' => 'School Registration',
            'description' => 'Students registered through a school link or bulk import',
            'total' => (int) ($totals->total ?? 0),
            'this_month' => (int) ($totals->this_month ?? 0),
            'paid' => (int) ($totals->paid ?? 0),
            'awaiting' => (int) ($totals->awaiting ?? 0),
            'collected' => $linkCollected + $batchCollected,
            'collected_note' => 'Approved link payments and batch payments',
            'status_title' => 'Payment Verification',
            'status_rows' => [
                ['label' => 'Approved', 'value' => (int) ($status->approved ?? 0), 'tone' => 'green'],
                ['label' => 'Pending', 'value' => (int) ($status->pending ?? 0), 'tone' => 'amber'],
                ['label' => 'Rejected', 'value' => (int) ($status->rejected ?? 0), 'tone' => 'red'],
            ],
            'split_title' => 'By Source',
            'split_rows' => [
                ['label' => 'Registration Link', 'value' => (int) ($split->via_link ?? 0)],
                ['label' => 'Bulk Import', 'value' => (int) ($split->via_bulk ?? 0)],
            ],
            'recent' => $this->recent($schoolIds, self::CHANNEL_SCHOOL),
            'list_url' => route('school.registrations.index'),
        ];
    }

    /**
     * Students who registered themselves on the public form, with no school
     * link and no import batch behind them.
     */
    private function regularChannel(?array $schoolIds): array
    {
        $totals = $this->base($schoolIds, self::CHANNEL_REGULAR)
            ->selectRaw($this->headlineSelect())
            ->first();

        $status = $this->base($schoolIds, self::CHANNEL_REGULAR)
            ->selectRaw("
                SUM(CASE WHEN fees_status = 'Paid' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN (fees_status IS NULL OR fees_status <> 'Paid')
                              AND (application_status IS NULL OR application_status <> 'Rejected')
                         THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN application_status = 'Rejected' THEN 1 ELSE 0 END) as rejected
            ")
            ->first();

        $split = $this->base($schoolIds, self::CHANNEL_REGULAR)
            ->selectRaw("
                SUM(CASE WHEN application_status = 'Enrolled' THEN 1 ELSE 0 END) as enrolled,
                SUM(CASE WHEN application_status = 'Approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN application_status = 'Documents Submitted' THEN 1 ELSE 0 END) as submitted
            ")
            ->first();

        // These students pay online, so the money actually banked is the sum of
        // authorised payment log rows against their invoices.
        $collected = (float) DB::table('paymentlog')
            ->join('invoice', 'paymentlog.invoice_id', '=', 'invoice.invoice_id')
            ->where('paymentlog.status', '1')
            ->whereIn('invoice.student_id', $this->base($schoolIds, self::CHANNEL_REGULAR)->select('student.student_id'))
            ->sum('paymentlog.amount');

        return [
            'key' => self::CHANNEL_REGULAR,
            'label' => 'Regular Registration',
            'description' => 'Students who registered themselves on the public form',
            'total' => (int) ($totals->total ?? 0),
            'this_month' => (int) ($totals->this_month ?? 0),
            'paid' => (int) ($totals->paid ?? 0),
            'awaiting' => (int) ($totals->awaiting ?? 0),
            'collected' => $collected,
            'collected_note' => 'Authorised online payments',
            'status_title' => 'Fee Status',
            'status_rows' => [
                ['label' => 'Paid', 'value' => (int) ($status->approved ?? 0), 'tone' => 'green'],
                ['label' => 'Pending', 'value' => (int) ($status->pending ?? 0), 'tone' => 'amber'],
                ['label' => 'Rejected', 'value' => (int) ($status->rejected ?? 0), 'tone' => 'red'],
            ],
            'split_title' => 'By Application Stage',
            'split_rows' => [
                ['label' => 'Enrolled', 'value' => (int) ($split->enrolled ?? 0)],
                ['label' => 'Approved', 'value' => (int) ($split->approved ?? 0)],
                ['label' => 'Documents Submitted', 'value' => (int) ($split->submitted ?? 0)],
            ],
            'recent' => $this->recent($schoolIds, self::CHANNEL_REGULAR),
            'list_url' => route('admin.registrations.index'),
        ];
    }

    /**
     * The four headline numbers, identical for both channels.
     */
    private function headlineSelect(): string
    {
        $monthStart = now()->startOfMonth()->toDateString();

        return "
            COUNT(*) as total,
            SUM(CASE WHEN DATE(student.created_at) >= '{$monthStart}' THEN 1 ELSE 0 END) as this_month,
            SUM(CASE WHEN fees_status = 'Paid' THEN 1 ELSE 0 END) as paid,
            SUM(CASE WHEN fees_status IS NULL OR fees_status <> 'Paid' THEN 1 ELSE 0 END) as awaiting
        ";
    }

    /**
     * Students belonging to one channel, limited to the viewer's schools.
     */
    private function base(?array $schoolIds, string $channel): Builder
    {
        // This class selects the channel itself, so the header's global cohort
        // must not intersect with it — otherwise the channel the admin is not
        // currently viewing would always report zero.
        $query = Student::query()
            ->withoutGlobalScope(Student::SCOPE_VIEW_COHORT)
            ->when($schoolIds !== null, fn ($q) => $q->whereIn('school_id', $schoolIds));

        if ($channel === self::CHANNEL_SCHOOL) {
            return $query->where(function ($q) {
                $q->where('registered_via_school_link', true)->orWhereNotNull('batch_id');
            });
        }

        return $query->where('registered_via_school_link', false)->whereNull('batch_id');
    }

    /**
     * Latest registrations, reduced to the columns the shared partial renders.
     */
    private function recent(?array $schoolIds, string $channel)
    {
        return $this->base($schoolIds, $channel)
            ->with(['school', 'standard'])
            ->orderByDesc('student_id')
            ->limit(6)
            ->get()
            ->map(function (Student $student) use ($channel) {
                $isSchool = $channel === self::CHANNEL_SCHOOL;

                return [
                    'id' => $student->student_id,
                    'name' => trim($student->fname . ' ' . $student->mname . ' ' . $student->lname),
                    'contact' => $student->email ?: $student->contact_no,
                    'context' => $isSchool
                        ? ($student->school->schoolname ?? '—')
                        : ($student->standard->stdname ?? '—'),
                    'plan' => $isSchool
                        ? $student->school_payment_plan_name
                        : $student->subscription,
                    'amount' => $isSchool ? $student->school_payment_amount : null,
                    'status' => $isSchool
                        ? ($student->registered_via_school_link ? $student->school_payment_status : $student->fees_status)
                        : $student->fees_status,
                    'date' => $student->created_at,
                ];
            });
    }
}
