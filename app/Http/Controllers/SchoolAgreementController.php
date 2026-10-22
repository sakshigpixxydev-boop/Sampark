<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\AgreementTemplate;
use App\Models\AuditLog;
use App\Services\AuditLoggerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SchoolAgreementController extends Controller
{
    protected $auditLogger;

    public function __construct(AuditLoggerService $auditLogger)
    {
        $this->auditLogger = $auditLogger;
    }

    /**
     * Authorize access to school agreement
     */
    private function authorizeAgreementAccess(School $school)
    {
        $user = Auth::user();
        if ($user->hasRole('Admin') || $user->hasRole('Sankalp Coordinator (School)') || $user->hasRole('School Coordinator')) {
            return;
        }

        if ($user->hasRole('Initiator')) {
            if ($school->initiator_id === $user->sysuser_id || in_array($school->school_id, $user->getAssignedSchoolIds())) {
                return;
            }
        }

        if ($user->hasRole('School')) {
            if (in_array($school->school_id, $user->getAssignedSchoolIds())) {
                return;
            }
        }

        abort(403, 'You do not have permission to access this school agreement.');
    }

    /**
     * Display the school agreement page
     */
    public function show(School $school)
    {
        $this->authorizeAgreementAccess($school);

        // Fetch audit logs for this school agreement
        $activities = AuditLog::with('user')
            ->where('auditable_type', School::class)
            ->where('auditable_id', $school->school_id)
            ->whereIn('event_type', [
                'AGREEMENT_UPLOADED',
                'AGREEMENT_REPLACED',
                'SIGNATURE_UPLOADED',
                'SIGNATURE_CHANGED',
                'INITIATOR_APPROVED',
                'COORDINATOR_APPROVED'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get school admin of this school to display their signature
        $schoolAdmin = $school->users()
            ->whereHas('role', function ($query) {
                $query->where('rolename', 'School');
            })
            ->first();

        return view('schools.agreement', compact('school', 'activities', 'schoolAdmin'));
    }

    /**
     * Download dynamic Word sample agreement
     */
    public function downloadSample(School $school)
    {
        $this->authorizeAgreementAccess($school);

        $user = Auth::user();
        if (!$user->hasRole('School') && !$user->hasRole('Initiator') && !$user->hasRole('Admin') && !$user->hasRole('Sankalp Coordinator (School)') && !$user->hasRole('School Coordinator')) {
            abort(403, 'You do not have permission to download the sample agreement.');
        }

        // Fetch active agreement template
        $template = AgreementTemplate::getActiveBySlug('school_registration');
        $content = $template ? $template->content_en : '<p>No terms and conditions defined.</p>';

        // Create HTML content format that Microsoft Word understands
        $html = '
        <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">
        <head>
            <title>' . ($template ? $template->title : 'School Agreement') . '</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
                h1 { text-align: center; color: #333333; margin-bottom: 20px; }
                p { margin-bottom: 15px; }
                ul, ol { margin-left: 20px; margin-bottom: 15px; }
                .meta-table { width: 100%; margin-bottom: 30px; border-collapse: collapse; }
                .meta-table td { padding: 8px; border: 1px solid #dddddd; }
                .sig-table { width: 100%; margin-top: 50px; }
                .sig-table td { width: 50%; vertical-align: top; }
            </style>
        </head>
        <body>
            <h1>' . ($template ? $template->title : 'Pramukh Sankalp - School Agreement') . '</h1>
            
            <table class="meta-table">
                <tr>
                    <td><strong>School Name</strong></td>
                    <td>' . htmlspecialchars($school->schoolname) . '</td>
                </tr>
                <tr>
                    <td><strong>Address</strong></td>
                    <td>' . htmlspecialchars($school->address1 . ($school->address2 ? ', ' . $school->address2 : '')) . '</td>
                </tr>
                <tr>
                    <td><strong>Established Year</strong></td>
                    <td>' . ($school->establishedyear ?? 'N/A') . '</td>
                </tr>
                <tr>
                    <td><strong>Date Generated</strong></td>
                    <td>' . date('Y-m-d H:i:s') . '</td>
                </tr>
            </table>

            <hr/>
            
            <div class="content">
                ' . $content . '
            </div>
            
            <table class="sig-table">
                <tr>
                    <td>
                        <p><strong>Authorized Signatory (School Admin)</strong></p>
                        <br/><br/><br/>
                        <p>___________________________</p>
                        <p>Name: ' . htmlspecialchars($school->adminname) . '</p>
                        <p>Designation: School Admin</p>
                    </td>
                    <td>
                        <p><strong>Authorized Signatory (Initiator)</strong></p>
                        <br/><br/><br/>
                        <p>___________________________</p>
                        <p>Name: ' . htmlspecialchars($school->initiator ? ($school->initiator->fname . ' ' . $school->initiator->lname) : 'N/A') . '</p>
                        <p>Designation: Initiator</p>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ';

        $filename = 'sample_agreement_' . str_replace(' ', '_', strtolower($school->schoolname)) . '.doc';

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-word')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Upload signed PDF agreement
     */
    public function uploadAgreement(Request $request, School $school)
    {
        $this->authorizeAgreementAccess($school);

        $user = Auth::user();
        if (!$user->hasRole('Initiator')) {
            return redirect()->back()->with('error', 'Only the Initiator can upload the signed agreement.');
        }

        if ($school->initiator_id !== $user->sysuser_id) {
            return redirect()->back()->with('error', 'You are not the assigned Initiator for this school.');
        }

        // Prevent modification if read-only or in initiator_approved review status
        if ($school->isAgreementReadOnly() || $school->agreement_status === 'initiator_approved') {
            return redirect()->back()->with('error', 'The agreement is in review / finalized and is read-only.');
        }

        $request->validate([
            'agreement_pdf' => 'required|mimes:pdf|max:5120', // PDF only, max 5MB
        ]);

        $oldPath = $school->agreement_pdf_path;

        // Delete old PDF if exists
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $path = $request->file('agreement_pdf')->store('school-agreements', 'public');

        $school->update([
            'agreement_pdf_path' => $path,
            'agreement_status' => 'uploaded'
        ]);

        // Log the action
        if ($oldPath) {
            $this->auditLogger->logCustom('AGREEMENT_REPLACED', $school, "Agreement PDF replaced by Initiator: " . $user->fname . ' ' . $user->lname);
        } else {
            $this->auditLogger->logCustom('AGREEMENT_UPLOADED', $school, "Agreement PDF uploaded by Initiator: " . $user->fname . ' ' . $user->lname);
        }

        return redirect()->back()->with('success', 'Signed agreement PDF uploaded successfully!');
    }

    /**
     * Initiator approval of school agreement
     */
    public function approveInitiator(School $school)
    {
        $this->authorizeAgreementAccess($school);

        $user = Auth::user();
        if (!$user->hasRole('Initiator')) {
            return redirect()->back()->with('error', 'Only the Initiator can approve this step.');
        }

        if ($school->initiator_id !== $user->sysuser_id) {
            return redirect()->back()->with('error', 'You are not the assigned Initiator for this school.');
        }

        if ($school->agreement_status !== 'uploaded') {
            return redirect()->back()->with('error', 'Agreement must be uploaded before approval.');
        }

        $school->update(['agreement_status' => 'initiator_approved']);

        // Log action
        $this->auditLogger->logCustom('INITIATOR_APPROVED', $school, "Agreement approved by Initiator: " . $user->fname . ' ' . $user->lname);

        return redirect()->back()->with('success', 'Agreement approved and submitted for review to School Coordinator!');
    }

    /**
     * School Coordinator approval of school agreement
     */
    public function approveCoordinator(School $school)
    {
        $this->authorizeAgreementAccess($school);

        $user = Auth::user();
        if (!$user->hasRole('Sankalp Coordinator (School)') && !$user->hasRole('School Coordinator') && !$user->hasRole('Admin')) {
            return redirect()->back()->with('error', 'Only the School Coordinator can approve this step.');
        }

        if ($school->agreement_status !== 'initiator_approved') {
            return redirect()->back()->with('error', 'Agreement must be approved by the Initiator first.');
        }

        $school->update(['agreement_status' => 'coordinator_approved']);

        // Log action
        $this->auditLogger->logCustom('COORDINATOR_APPROVED', $school, "Agreement approved by School Coordinator: " . $user->fname . ' ' . $user->lname);

        return redirect()->back()->with('success', 'Agreement approved successfully. The agreement is now finalized and read-only!');
    }

    /**
     * Upload digital signature image
     */
    public function uploadSignature(Request $request, School $school)
    {
        $this->authorizeAgreementAccess($school);

        // Check if agreement is read-only or in initiator_approved review status
        if ($school->isAgreementReadOnly() || $school->agreement_status === 'initiator_approved') {
            return redirect()->back()->with('error', 'The agreement is in review / finalized and is read-only.');
        }

        $request->validate([
            'signature_image' => 'required|image|mimes:png,jpg,jpeg,gif|max:2048', // max 2MB
        ]);

        $user = Auth::user();
        $oldPath = $user->signature_path;

        // Delete old signature if exists
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $path = $request->file('signature_image')->store('signatures', 'public');
        $user->update(['signature_path' => $path]);

        // Log action
        if ($oldPath) {
            $this->auditLogger->logCustom('SIGNATURE_CHANGED', $school, "Signature image updated by " . $user->fname . ' ' . $user->lname);
        } else {
            $this->auditLogger->logCustom('SIGNATURE_UPLOADED', $school, "Signature image uploaded by " . $user->fname . ' ' . $user->lname);
        }

        return redirect()->back()->with('success', 'Signature image uploaded successfully!');
    }
}
