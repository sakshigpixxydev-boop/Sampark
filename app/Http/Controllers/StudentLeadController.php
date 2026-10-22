<?php

namespace App\Http\Controllers;

use App\Helpers\DateHelper;
use App\Helpers\MailTemplateHelper;
use App\Models\AppSetting;
use App\Models\EducationalBoard;
use App\Models\Standard;
use App\Models\State;
use App\Models\Student;
use App\Services\AuditLoggerService;
use App\Services\ExternalEmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

/**
 * Student Leads: people who filled the public registration form while regular
 * registration was closed for the semester.
 *
 * Sending a lead the payment link converts them into a normal pending student
 * who resumes at plan selection — they never re-enter their details.
 */
class StudentLeadController extends Controller
{
    protected AuditLoggerService $auditLogger;

    public function __construct(AuditLoggerService $auditLogger)
    {
        $this->auditLogger = $auditLogger;
    }

    public function index(Request $request)
    {
        if ($request->has('clear_filters')) {
            return redirect()->route('student-leads.index');
        }

        $query = Student::onlyLeads()->with([
            'standard',
            'educationalBoard',
            'taluka.district.state',
            'guardians',
        ]);

        $this->applyFilters($request, $query);

        $sortBy = in_array($request->get('sort_by'), ['lead_captured_at', 'fname', 'email', 'created_at'])
            ? $request->get('sort_by')
            : 'lead_captured_at';
        $sortOrder = $request->get('sort_order') === 'asc' ? 'asc' : 'desc';

        $leads = $query->orderBy($sortBy, $sortOrder)->paginate(20)->withQueryString();

        // Unfiltered header counts.
        $totalLeads = Student::onlyLeads()->count();
        $linkSentCount = Student::onlyLeads()->whereNotNull('lead_payment_link_sent_at')->count();

        return view('student-leads.index', [
            'leads' => $leads,
            'standards' => Standard::orderBy('std_id')->get(),
            'boards' => EducationalBoard::orderBy('eduboard_id')->get(),
            'states' => State::orderBy('sname')->get(),
            'totalLeads' => $totalLeads,
            'linkSentCount' => $linkSentCount,
            'pendingCount' => $totalLeads - $linkSentCount,
            'registrationOpen' => AppSetting::registrationIsOpen(),
        ]);
    }

    public function show($id)
    {
        $lead = Student::onlyLeads()
            ->with(['standard', 'educationalBoard', 'taluka.district.state', 'guardians', 'school'])
            ->findOrFail($id);

        return view('student-leads.show', compact('lead'));
    }

    /**
     * Convert one lead into a pending student and email them the payment link.
     */
    public function sendPaymentLink(Request $request, $id)
    {
        $lead = Student::onlyLeads()->findOrFail($id);

        try {
            $this->convertAndNotify($lead);
        } catch (\Throwable $e) {
            Log::error('Student Lead payment link failed: ' . $e->getMessage(), ['student_id' => $id]);

            return redirect()->back()->with('error', 'Could not send the payment link to ' . $lead->email . '. ' . $e->getMessage());
        }

        return redirect()->route('student-leads.index')
            ->with('success', 'Payment link sent to ' . $lead->email . '.');
    }

    /**
     * Same, for a selected set of leads. Each lead is converted independently so
     * one bad address cannot block the rest.
     */
    public function bulkSendPaymentLink(Request $request)
    {
        $validated = $request->validate([
            'lead_ids' => 'required|array',
            'lead_ids.*' => 'integer',
        ]);

        $leads = Student::onlyLeads()->whereIn('student_id', $validated['lead_ids'])->get();

        $sent = 0;
        $failed = [];

        foreach ($leads as $lead) {
            try {
                $this->convertAndNotify($lead);
                $sent++;
            } catch (\Throwable $e) {
                Log::error('Student Lead bulk payment link failed: ' . $e->getMessage(), ['student_id' => $lead->student_id]);
                $failed[] = $lead->email;
            }
        }

        $message = "Payment link sent to {$sent} lead(s).";
        if (!empty($failed)) {
            $message .= ' Failed for ' . count($failed) . ': ' . implode(', ', $failed) . '.';
        }

        return redirect()->route('student-leads.index')
            ->with(empty($failed) ? 'success' : 'error', $message);
    }

    public function destroy($id)
    {
        $lead = Student::onlyLeads()->findOrFail($id);
        $email = $lead->email;
        $lead->delete();

        return redirect()->route('student-leads.index')
            ->with('success', 'Lead ' . $email . ' removed.');
    }

    public function export(Request $request)
    {
        $query = Student::onlyLeads()->with(['standard', 'educationalBoard', 'taluka.district.state', 'guardians']);
        $this->applyFilters($request, $query);
        $leads = $query->orderBy('lead_captured_at', 'desc')->get();

        $filename = 'student-leads-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($leads) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Name', 'Email', 'Primary Contact', 'WhatsApp', 'Gender', 'Date of Birth',
                'Standard', 'Stream', 'Board', 'School', 'Guardian Name', 'Guardian Contact',
                'City/Village', 'Taluka', 'District', 'State', 'Pincode',
                'Language', 'Captured On', 'Payment Link Sent',
            ]);

            foreach ($leads as $lead) {
                $guardian = $lead->guardians->first();
                $taluka = $lead->taluka;

                fputcsv($handle, [
                    trim($lead->fname . ' ' . $lead->mname . ' ' . $lead->lname),
                    $lead->email,
                    $lead->contact_no,
                    $lead->whatsapp_no,
                    $lead->gender,
                    $lead->birthdate ? $lead->birthdate->format('d/m/Y') : '',
                    optional($lead->standard)->stdname,
                    $lead->stream,
                    optional($lead->educationalBoard)->boardname ?? $lead->other_eduboard_name,
                    $lead->school_name,
                    $guardian ? trim($guardian->fname . ' ' . $guardian->mname . ' ' . $guardian->lname) : '',
                    $guardian ? $guardian->phone : '',
                    $lead->city_village,
                    optional($taluka)->tname,
                    optional(optional($taluka)->district)->dname,
                    optional(optional(optional($taluka)->district)->state)->sname,
                    $lead->pincode,
                    $lead->selected_language,
                    $lead->lead_captured_at ? $lead->lead_captured_at->format('d/m/Y H:i') : '',
                    $lead->lead_payment_link_sent_at ? $lead->lead_payment_link_sent_at->format('d/m/Y H:i') : 'No',
                ]);
            }

            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Flip the lead into a pending student and send the payment link.
     *
     * The email is sent inside the transaction and a failure throws, so a lead
     * is never left converted-but-unnotified: the admin can simply retry.
     */
    protected function convertAndNotify(Student $lead): void
    {
        DB::transaction(function () use ($lead) {
            $lead->update([
                'is_lead' => 0,
                'lead_converted_at' => now(),
                'lead_payment_link_sent_at' => now(),
                // Delivery to their inbox is the proof of address ownership;
                // they can only proceed by clicking the link sent to it.
                'is_email_verified' => 1,
                'otp' => null,
                'otp_expires_at' => null,
                // application_status and fees_status stay null so the row lands
                // in the pending-docs / unpaid buckets and selectPlan renders.
            ]);

            $guardian = $lead->guardians()->first();

            $rendered = MailTemplateHelper::render('student_lead_payment_link', [
                'guardian_name' => $guardian
                    ? strtoupper(trim($guardian->fname . ' ' . $guardian->lname))
                    : strtoupper(trim($lead->fname . ' ' . $lead->lname)),
                'student_name' => strtoupper(trim($lead->fname . ' ' . $lead->lname)),
                'payment_link' => route('registration.plans', base64_encode($lead->student_id)),
                'next_batch_note' => AppSetting::nextBatchNote($lead->selected_language) ?? '',
            ], $lead->selected_language);

            if (!$rendered) {
                throw new \RuntimeException("Mail template 'student_lead_payment_link' not found.");
            }

            $emailService = new ExternalEmailService();
            $sent = $emailService->sendSmtpMail(
                [$lead->email => strtoupper(trim($lead->fname))],
                $rendered['subject'],
                $rendered['message']
            );

            if (!$sent) {
                throw new \RuntimeException('Email delivery failed: ' . ($emailService->lastResponse ?? 'unknown error'));
            }

            $this->auditLogger->logCustom(
                'STUDENT_LEAD_CONVERTED',
                $lead,
                "Payment link sent to {$lead->email}; lead converted to pending student."
            );
        });
    }

    /**
     * Shared filtering for the listing and the CSV export.
     */
    protected function applyFilters(Request $request, $query): void
    {
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('fname', 'like', "%{$search}%")
                    ->orWhere('mname', 'like', "%{$search}%")
                    ->orWhere('lname', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('contact_no', 'like', "%{$search}%")
                    ->orWhere('school_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('std_id')) {
            $query->whereIn('std_id', (array) $request->get('std_id'));
        }

        if ($request->filled('eduboard_id')) {
            $query->whereIn('eduboard_id', (array) $request->get('eduboard_id'));
        }

        if ($request->filled('state_id')) {
            $query->where('state_id', $request->get('state_id'));
        }

        if ($request->filled('city_village')) {
            $query->where('city_village', 'like', '%' . $request->get('city_village') . '%');
        }

        if ($request->filled('selected_language')) {
            $query->where('selected_language', $request->get('selected_language'));
        }

        if ($request->filled('link_sent')) {
            if ($request->get('link_sent') === 'yes') {
                $query->whereNotNull('lead_payment_link_sent_at');
            } elseif ($request->get('link_sent') === 'no') {
                $query->whereNull('lead_payment_link_sent_at');
            }
        }

        if ($request->filled('start_date')) {
            $query->whereDate('lead_captured_at', '>=', DateHelper::convertDateFormat($request->get('start_date')));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('lead_captured_at', '<=', DateHelper::convertDateFormat($request->get('end_date')));
        }
    }
}
