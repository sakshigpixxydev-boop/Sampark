<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Student;
use App\Services\AuditLoggerService;
use Illuminate\Http\Request;

/**
 * Opens and closes the public (non school-link) registration form.
 *
 * While closed the form still collects details, but they are stored as
 * Student Leads instead of enrolling the student.
 */
class RegistrationControlController extends Controller
{
    protected AuditLoggerService $auditLogger;

    public function __construct(AuditLoggerService $auditLogger)
    {
        $this->auditLogger = $auditLogger;
    }

    public function edit()
    {
        return view('settings.registration', [
            'registrationOpen' => AppSetting::registrationIsOpen(),
            'notes' => [
                'en' => AppSetting::get('registration.next_batch_note_en', ''),
                'hi' => AppSetting::get('registration.next_batch_note_hi', ''),
                'gu' => AppSetting::get('registration.next_batch_note_gu', ''),
            ],
            'leadCount' => Student::onlyLeads()->count(),
            'pendingLinkCount' => Student::onlyLeads()->whereNull('lead_payment_link_sent_at')->count(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'registration_open' => 'required|boolean',
            'next_batch_note_en' => 'nullable|string|max:1000',
            'next_batch_note_hi' => 'nullable|string|max:1000',
            'next_batch_note_gu' => 'nullable|string|max:1000',
        ]);

        $wasOpen = AppSetting::registrationIsOpen();
        $willBeOpen = (bool) $validated['registration_open'];

        AppSetting::set('registration.open', $willBeOpen, 'bool', 'registration');

        foreach (['en', 'hi', 'gu'] as $lang) {
            AppSetting::set(
                "registration.next_batch_note_{$lang}",
                $validated["next_batch_note_{$lang}"] ?? '',
                'string',
                'registration'
            );
        }

        // High-impact switch: always leave a trail of who flipped it.
        if ($wasOpen !== $willBeOpen) {
            $this->auditLogger->logCustom(
                'REGISTRATION_STATUS_CHANGED',
                null,
                'Regular registration ' . ($willBeOpen ? 'opened' : 'closed') . '.',
                ['registration_open' => $wasOpen],
                ['registration_open' => $willBeOpen]
            );
        }

        return redirect()->route('settings.registration.edit')
            ->with('success', 'Registration settings updated successfully.');
    }
}
