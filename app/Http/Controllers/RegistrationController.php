<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Guardian;
use App\Models\Relation;
use App\Models\State;
use App\Models\District;
use App\Models\Taluko;
use App\Models\EducationalBoard;
use App\Models\Standard;
use App\Models\Country;
use App\Models\FeePlan;
use App\Models\AppSetting;
use App\Services\ExternalEmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\File;
use Razorpay\Api\Api;
use App\Models\Document;
use App\Helpers\DateHelper;

class RegistrationController extends Controller
{
    /**
     * Handle public student registration via school link.
     */
    public function registerFromSchoolLink($token)
    {
        $school = \App\Models\School::where('reg_token', $token)->first();
        if (!$school) {
            abort(404, 'Registration link not found.');
        }

        $now = now()->timezone('Asia/Kolkata');
        $startDate = $school->reg_start_date ? \Carbon\Carbon::parse($school->reg_start_date->format('Y-m-d H:i:s'), 'Asia/Kolkata') : null;
        $endDate = $school->reg_end_date ? \Carbon\Carbon::parse($school->reg_end_date->format('Y-m-d H:i:s'), 'Asia/Kolkata') : null;

        if ($startDate && $now->lessThan($startDate)) {
            session()->forget([
                'school_registration_token',
                'school_registration_school_id',
                'school_registration_school_name',
                'school_registration_form_name',
                'school_registration_logo_url',
                'school_registration_notes',
            ]);
            session()->save();
            return response()->view('registration.expired', [
                'school' => $school,
                'isFuture' => true,
            ], 403);
        }
        if ($endDate && $now->greaterThan($endDate)) {
            session()->forget([
                'school_registration_token',
                'school_registration_school_id',
                'school_registration_school_name',
                'school_registration_form_name',
                'school_registration_logo_url',
                'school_registration_notes',
            ]);
            session()->save();
            return response()->view('registration.expired', [
                'school' => $school,
                'isFuture' => false,
            ], 403);
        }

        session([
            'school_registration_token' => $token,
            'school_registration_school_id' => $school->school_id,
            'school_registration_school_name' => $school->schoolname,
            'school_registration_form_name' => $school->reg_form_name,
            'school_registration_logo_url' => $school->logourl,
            'school_registration_notes' => $school->notes,
        ]);
        session()->save();

        return redirect()->route('home');
    }

    /**
     * Show the registration form.
     */
    public function index(Request $request)
    {
        // Kept in scope after the validation block below so the school's own
        // fees can be rendered on the form (see $schoolCustomFees).
        $school = null;

        if (session()->has('school_registration_token')) {
            $token = session('school_registration_token');
            $school = \App\Models\School::where('reg_token', $token)->first();
            if ($school) {
                $now = now()->timezone('Asia/Kolkata');
                $startDate = $school->reg_start_date ? \Carbon\Carbon::parse($school->reg_start_date->format('Y-m-d H:i:s'), 'Asia/Kolkata') : null;
                $endDate = $school->reg_end_date ? \Carbon\Carbon::parse($school->reg_end_date->format('Y-m-d H:i:s'), 'Asia/Kolkata') : null;

                if (($startDate && $now->lessThan($startDate)) || ($endDate && $now->greaterThan($endDate))) {
                    session()->forget([
                        'school_registration_token',
                        'school_registration_school_id',
                        'school_registration_school_name',
                        'school_registration_form_name',
                        'school_registration_logo_url',
                        'school_registration_notes',
                    ]);
                    session()->save();
                    return response()->view('registration.expired', [
                        'school' => $school,
                        'isFuture' => ($startDate && $now->lessThan($startDate)),
                    ], 403);
                }
            } else {
                session()->forget([
                    'school_registration_token',
                    'school_registration_school_id',
                    'school_registration_school_name',
                    'school_registration_form_name',
                    'school_registration_logo_url',
                    'school_registration_notes',
                ]);
                session()->save();
            }
        }
        $standards = DB::table('ms_standard')
            ->whereNotIn('stdname', ['Standard 1', 'Standard 2', 'Standard 3', 'Standard 4', 'Standard 5'])
            ->get();
        $boards = DB::table('ms_eductionalboard')->get();
        $mandals = DB::table('ms_satsangmandal')->get();
        $mandirs = DB::table('ms_shikharmandir')->get();
        $mediums = DB::table('ms_medium')->get();
        $feePlans = FeePlan::where('is_active_on_form', 1)
            ->get();

        // When the visitor arrived through a school link, the school's own fees
        // are what they will actually pay — show those instead of the generic
        // online programme plans.
        $schoolCustomFees = ($school && !empty($school->custom_fees)) ? $school->custom_fees : [];

        // Regular (non school-link) registration may be closed for the semester.
        $registrationClosed = $this->regularRegistrationClosed();
        $nextBatchNote = $registrationClosed ? AppSetting::nextBatchNote(app()->getLocale()) : null;

        $savedData = session('registration_progress', []);

        $studentId = $request->query('student_id');
        if ($studentId) {
            $decodedId = base64_decode($studentId);
            $student = Student::withLeads()->find($decodedId);
            if ($student) {
                // A captured lead has no plan/payment step to resume — send
                // them back to their confirmation page instead of the wizard.
                if ($student->is_lead) {
                    return redirect()->route('registration.lead-thanks', $studentId);
                }

                if ($student->is_email_verified) {
                    $nextUrl = ($student->fees_status === 'Paid' || $student->registered_via_school_link)
                        ? route('documents.form', $studentId)
                        : route('registration.plans', $studentId);
                    return redirect($nextUrl);
                }

                $guardian = $student->guardians()->first();
                $savedData = [
                    'consent' => true,
                    'first_name' => $student->fname,
                    'middle_name' => $student->mname,
                    'surname' => $student->lname,
                    'gender' => $student->gender == 'M' ? 'Male' : ($student->gender == 'F' ? 'Female' : 'Other'),
                    'email' => $student->email,
                    'dob' => $student->birthdate,
                    'primary_contact' => $student->contact_no,
                    'whatsapp_contact' => $student->whatsapp_no,
                    'whatsapp_same_as_primary' => $student->contact_no === $student->whatsapp_no,
                    'education_level' => $student->std_id,
                    'stream' => $student->stream,
                    'board' => $student->eduboard_id,
                    'other_eduboard_name' => $student->other_eduboard_name,
                    'medium' => $student->educationmedium,
                    'school_name' => $student->school_name,
                    'attending_sabha' => ($student->satsangmandal_id || $student->kshetra_id || $student->shikharm_id || $student->satsang_karyakar_name) ? 'Yes' : 'No',
                    'satsang_mandal' => $student->satsangmandal_id ? DB::table('ms_satsangmandal')->where('satsangmandal_id', $student->satsangmandal_id)->value('mandalname') : null,
                    'kshetra_name' => $student->kshetra_id ? DB::table('ms_kshetra')->where('kshetra_id', $student->kshetra_id)->value('kname') : null,
                    'shikharabaddha_mandir' => $student->shikharm_id ? DB::table('ms_shikharmandir')->where('shikharm_id', $student->shikharm_id)->value('mname') : null,
                    'mandal_sanchalak_name' => $student->satsang_karyakar_name,
                    'mandal_sanchalak_contact' => $student->satsang_karyakar_contact,
                    'guardian_first_name' => $guardian ? $guardian->fname : '',
                    'guardian_middle_name' => $guardian ? $guardian->mname : '',
                    'guardian_last_name' => $guardian ? $guardian->lname : '',
                    'guardian_full_name' => $guardian ? trim($guardian->fname . ' ' . $guardian->mname . ' ' . $guardian->lname) : '',
                    'guardian_gender' => $guardian ? ($guardian->gender == 'M' ? 'Male' : ($guardian->gender == 'F' ? 'Female' : 'Other')) : '',
                    'guardian_contact' => $guardian ? $guardian->phone : '',
                    'handout_language' => $student->materiallanguage,
                    'country' => $student->country_id,
                    'state' => $student->state_id,
                    'district' => $student->dist_id,
                    'taluka' => $student->takula_id,
                    'city_village' => $student->city_village,
                    'address' => $student->address,
                    'pincode' => $student->pincode,
                    'step' => 6,
                    'isSubmitted' => true,
                    'studentId' => $studentId
                ];
            }
        }

        $agreementTemplate = \App\Models\AgreementTemplate::getActiveBySlug('school_registration');

        return view('registration.index', compact(
            'standards',
            'boards',
            'mandals',
            'mandirs',
            'mediums',
            'feePlans',
            'savedData',
            'agreementTemplate',
            'registrationClosed',
            'nextBatchNote',
            'schoolCustomFees'
        ));
    }

    public function saveProgress(Request $request)
    {
        session(['registration_progress' => $request->all()]);
        return response()->json(['success' => true]);
    }

    /**
     * Is the regular (non school-link) registration form currently closed?
     *
     * The school-link flow keys off 'school_registration_school_id' in the
     * session — the same value store() uses to decide school attribution — so
     * school-link and school registration are never affected by this switch.
     */
    private function regularRegistrationClosed(): bool
    {
        return !session()->has('school_registration_school_id')
            && !AppSetting::registrationIsOpen();
    }

    /**
     * The wizard posts a single 'guardian_full_name'; split it into parts and
     * default the guardian contact to the student's primary contact.
     */
    private function normaliseGuardianInput(Request $request): void
    {
        if ($request->has('guardian_full_name')) {
            $parts = explode(' ', preg_replace('/\s+/', ' ', trim($request->guardian_full_name)));
            $firstName = isset($parts[0]) ? $parts[0] : '';
            $lastName = count($parts) > 1 ? end($parts) : '';

            $middleName = '';
            if (count($parts) > 2) {
                $middleParts = array_slice($parts, 1, -1);
                $middleName = implode(' ', $middleParts);
            }

            $request->merge([
                'guardian_first_name' => $firstName,
                'guardian_middle_name' => $middleName,
                'guardian_last_name' => $lastName,
            ]);
        }

        if ($request->has('primary_contact')) {
            $request->merge([
                'guardian_contact' => $request->primary_contact,
            ]);
        }
    }

    /**
     * Validation rules shared by the OTP step, the final submit and lead capture.
     *
     * @param int|null $studentId Existing student to exclude from the email uniqueness check.
     * @param bool $uniqueEmail Lead capture handles duplicates itself, so it opts out.
     */
    private function registrationRules(?int $studentId = null, bool $uniqueEmail = true): array
    {
        $emailRule = 'required|email|max:100';
        if ($uniqueEmail) {
            $emailRule .= '|unique:student,email' . ($studentId ? ',' . $studentId . ',student_id' : '');
        }

        return [
            'first_name' => 'required|string|max:100',
            'middle_name' => 'required|string|max:100',
            'surname' => 'required|string|max:100',
            'gender' => 'required|in:Male,Female,Other',
            'dob' => [
                'required',
                function ($attribute, $value, $fail) {
                    // Convert DD/MM/YYYY to YYYY-MM-DD for validation
                    $convertedDate = DateHelper::convertDateFormat($value);
                    $birthDate = new \DateTime($convertedDate);
                    $today = new \DateTime();
                    $age = $today->diff($birthDate)->y;
                    if ($age < 10) {
                        $fail('Student must be in standard 6 or above (minimum 10 years old) to be eligible.');
                    }
                }
            ],
            'email' => $emailRule,
            'primary_contact' => 'required|digits:10',
            'whatsapp_contact' => 'required|digits:10',
            'education_level' => 'required|integer',
            'stream' => 'required_if:education_level,11,12|nullable|string|in:Arts,Commerce,Science',
            'board' => 'required|integer',
            'other_eduboard_name' => 'required_if:board,11|nullable|string|max:100',
            'medium' => 'required|integer',
            'school_name' => 'required|string|max:255',
            'attending_sabha' => 'required|in:Yes,No',
            'satsang_mandal' => 'required_if:attending_sabha,Yes|nullable|string',
            'kshetra_name' => 'required_if:attending_sabha,Yes|nullable|string',
            'shikharabaddha_mandir' => 'required_if:attending_sabha,Yes|nullable|string',
            'mandal_sanchalak_name' => 'required_if:attending_sabha,Yes|nullable|string',
            'mandal_sanchalak_contact' => 'required_if:attending_sabha,Yes|nullable|digits:10',
            'guardian_first_name' => 'required|string|max:100',
            'guardian_middle_name' => 'nullable|string|max:100',
            'guardian_last_name' => 'required|string|max:100',
            'guardian_gender' => 'required|in:Male,Female,Other',
            'guardian_contact' => 'required|digits:10',
            'handout_language' => 'required|integer',
            'country' => 'required|numeric',
            'state' => 'required|exists:ms_state,state_id',
            'district' => 'required|exists:ms_dist,dist_id',
            'taluka' => 'required|exists:ms_taluko,takula_id',
            'city_village' => 'required|string|max:100',
            'address' => 'required|string|max:500',
            'pincode' => 'required|digits:6',
        ];
    }

    /**
     * Map the posted form fields onto student table columns.
     * Does not include the school-session attribution — see applySchoolSessionData().
     */
    private function mapStudentData(Request $request): array
    {
        $studentData = [
            'fname' => strtoupper(trim($request->first_name)),
            'mname' => strtoupper(trim($request->middle_name)),
            'lname' => strtoupper(trim($request->surname)),
            'gender' => $request->gender == 'Male' ? 'M' : ($request->gender == 'Female' ? 'F' : 'O'),
            'birthdate' => DateHelper::convertDateFormat($request->dob),
            'email' => $request->email,
            'contact_no' => $request->primary_contact,
            'whatsapp_no' => $request->whatsapp_contact,

            'std_id' => $request->education_level,
            'stream' => in_array($request->education_level, [11, 12]) ? $request->stream : null,
            'eduboard_id' => $request->board,
            'other_eduboard_name' => $request->board == 11 ? strtoupper(trim($request->other_eduboard_name)) : null,
            'educationmedium' => $request->medium,
            'school_name' => strtoupper(trim($request->school_name)),

            'materiallanguage' => $request->handout_language,
            'country_id' => $request->country ?? 1,

            'state_id' => $request->state,
            'dist_id' => $request->district,
            'takula_id' => $request->taluka,
            'city_village' => strtoupper(trim($request->city_village)),
            'address' => strtoupper(trim($request->address)),
            'pincode' => $request->pincode,
            'selected_language' => app()->getLocale() ?: 'en',
        ];

        // If attending sabha, handle satsang details
        if ($request->attending_sabha === 'Yes') {
            $studentData['satsangmandal_id'] = $this->getSatsangMandalId($request->satsang_mandal);
            $studentData['kshetra_id'] = $this->getKshetraId($request->kshetra_name);
            $studentData['shikharm_id'] = $this->getShikharMandirId($request->shikharabaddha_mandir);
            $studentData['satsang_karyakar_name'] = strtoupper(trim($request->mandal_sanchalak_name));
            $studentData['satsang_karyakar_contact'] = $request->mandal_sanchalak_contact;
        } else {
            $studentData['satsangmandal_id'] = null;
            $studentData['kshetra_id'] = null;
            $studentData['shikharm_id'] = null;
            $studentData['satsang_karyakar_name'] = null;
            $studentData['satsang_karyakar_contact'] = null;
        }

        return $studentData;
    }

    /**
     * Attribute the registration to the school whose link was used, if any.
     */
    private function applySchoolSessionData(array $studentData): array
    {
        if (session('school_registration_school_id')) {
            $studentData['school_id'] = session('school_registration_school_id');
            $studentData['registered_via_school_link'] = true;
            $studentData['school_approval_status'] = 'Pending';
            $studentData['fees_status'] = 'Pending';
        }

        return $studentData;
    }

    /**
     * Map the posted guardian fields onto guardian table columns.
     */
    private function mapGuardianData(Request $request, $studentId): array
    {
        return [
            'student_id' => $studentId,
            'fname' => strtoupper(trim($request->guardian_first_name)),
            'mname' => strtoupper(trim($request->guardian_middle_name)),
            'lname' => strtoupper(trim($request->guardian_last_name)),
            'gender' => $request->guardian_gender == 'Male' ? 'M' : ($request->guardian_gender == 'Female' ? 'F' : 'O'),
            'phone' => $request->guardian_contact,
            'created_at' => now(),
        ];
    }

    /**
     * Handle the registration form submission.
     */
    public function store(Request $request)
    {
        try {
            // Log the request for debugging
            Log::info('Registration Submission:', $request->all());

            // Regular registration is closed: leads must go through storeLead().
            // Guards the endpoint against a direct POST that bypasses the UI.
            if ($this->regularRegistrationClosed()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Registration is currently closed.'),
                ], 422);
            }

            // Check if we are updating an existing registration
            $studentId = $request->input('student_id');
            $decodedStudentId = $studentId ? base64_decode($studentId) : null;

            $this->normaliseGuardianInput($request);

            // Validate the request
            $validator = Validator::make($request->all(), $this->registrationRules($decodedStudentId));

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verify OTP from database record
            if ($decodedStudentId) {
                $student = Student::findOrFail($decodedStudentId);

                if (!$student->is_email_verified) {
                    if (!$request->input('otp') || $student->otp !== $request->input('otp')) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Invalid OTP. Please try again.',
                            'errors' => ['otp' => ['Invalid OTP']]
                        ], 422);
                    }

                    if ($student->otp_expires_at && now()->greaterThan($student->otp_expires_at)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'OTP has expired. Please resend.',
                            'errors' => ['otp' => ['OTP has expired']]
                        ], 422);
                    }
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Student record not found. Please click Submit Application again.',
                    'errors' => ['otp' => ['Student record not found']]
                ], 422);
            }

            DB::beginTransaction();

            // 1. Map and Create Student
            $studentData = $this->applySchoolSessionData($this->mapStudentData($request));

            $student->update($studentData);

            // 2. Map and Create/Update Guardian
            Guardian::updateOrCreate(
                ['student_id' => $student->student_id],
                $this->mapGuardianData($request, $student->student_id)
            );

            // Clear OTP and mark email verified
            $student->otp = null;
            $student->otp_expires_at = null;
            $student->is_email_verified = 1;
            $student->save();

            DB::commit();

            session()->forget('registration_progress');

            return response()->json([
                'success' => true,
                'message' => 'Registration successful!',
                'student_id' => base64_encode($student->student_id),
                'redirect_url' => route('registration.success', base64_encode($student->student_id))
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Registration Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Capture a Student Lead while regular registration is closed.
     *
     * Same form, same fields — but no OTP, no plan and no payment. The row is
     * flagged is_lead so it stays out of every student listing until an admin
     * sends the payment link once the next batch opens.
     */
    public function storeLead(Request $request)
    {
        try {
            // Only reachable while regular registration is closed.
            if (!$this->regularRegistrationClosed()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Registration is currently open.'),
                    'redirect_url' => route('home'),
                ], 422);
            }

            $this->normaliseGuardianInput($request);

            // Duplicate handling is done below, so skip the unique rule.
            $validator = Validator::make($request->all(), $this->registrationRules(null, false));

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // The lead will only ever be reachable by email, so a typo makes the
            // record worthless. Cheap sanity check on the domain.
            $domain = substr(strrchr($request->email, '@') ?: '', 1);
            if ($domain && function_exists('checkdnsrr') && !checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => ['email' => [__('This email domain does not appear to accept mail. Please check it.')]]
                ], 422);
            }

            $existing = Student::withLeads()->where('email', $request->email)->first();

            if ($existing && !$existing->is_lead) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => ['email' => [__('This email is already registered.')]]
                ], 422);
            }

            DB::beginTransaction();

            $studentData = $this->mapStudentData($request);
            $studentData['is_lead'] = 1;
            $studentData['lead_captured_at'] = now();
            $studentData['lead_source'] = 'registration_form';
            $studentData['is_email_verified'] = 0;
            $studentData['application_status'] = null;
            $studentData['fees_status'] = null;
            $studentData['otp'] = null;
            $studentData['otp_expires_at'] = null;

            if ($existing) {
                // Re-submission: refresh the details in place rather than
                // creating a second lead for the same person.
                $existing->update($studentData);
                $student = $existing;
            } else {
                $student = Student::create($studentData);
            }

            // The Guardian row is required later: the payment-success email
            // sent after conversion reads the guardian name from it.
            Guardian::updateOrCreate(
                ['student_id' => $student->student_id],
                $this->mapGuardianData($request, $student->student_id)
            );

            DB::commit();

            session()->forget('registration_progress');

            return response()->json([
                'success' => true,
                'message' => 'Details saved successfully!',
                'student_id' => base64_encode($student->student_id),
                'redirect_url' => route('registration.lead-thanks', base64_encode($student->student_id)),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Student Lead Capture Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirmation page shown to a captured Student Lead.
     */
    public function leadThanks($encodedId)
    {
        $student = Student::withLeads()->findOrFail(base64_decode($encodedId));

        // Already converted (payment link sent) — resume the normal flow.
        if (!$student->is_lead) {
            return redirect()->route('registration.success', $encodedId);
        }

        return view('registration.lead-thanks', [
            'student' => $student,
            'encodedId' => $encodedId,
            'nextBatchNote' => AppSetting::nextBatchNote($student->selected_language),
        ]);
    }

    /**
     * Helper to get Satsang Mandal ID.
     */
    private function getSatsangMandalId($name)
    {
        if (!$name)
            return null;

        $mandal = DB::table('ms_satsangmandal')->where('mandalname', strtoupper(trim($name)))->first();

        if (!$mandal) {
            $id = DB::table('ms_satsangmandal')->insertGetId([
                'mandalname' => strtoupper(trim($name)),
                'created_at' => now()
            ]);
            return $id;
        }

        return $mandal->satsangmandal_id;
    }

    /**
     * Helper to get Kshetra ID.
     */
    private function getKshetraId($name)
    {
        if (empty($name))
            return null;

        $kshetra = DB::table('ms_kshetra')
            ->where('kshetraname', 'LIKE', "%{$name}%")
            ->first();

        if (!$kshetra) {
            $id = DB::table('ms_kshetra')->insertGetId([
                'kshetraname' => strtoupper(trim($name)),
                'created_at' => now()
            ]);
            return $id;
        }

        return $kshetra->kshetra_id;
    }

    /**
     * Helper to get Shikhar Mandir ID.
     */
    private function getShikharMandirId($name)
    {
        if (empty($name))
            return null;

        $mandir = DB::table('ms_shikharmandir')
            ->where('mname', 'LIKE', "%{$name}%")
            ->first();

        if (!$mandir) {
            $id = DB::table('ms_shikharmandir')->insertGetId([
                'mname' => strtoupper(trim($name)),
                'created_at' => now()
            ]);
            return $id;
        }

        return $mandir->shikharm_id;
    }


    public function selectPlan($encodedId)
    {
        $studentId = base64_decode($encodedId);
        $student = Student::findOrFail($studentId);

        // If already paid, direct to appropriate page
        if ($student->fees_status === 'Paid') {
            if ($student->application_status === 'Documents Submitted') {
                return redirect()->route('registration.success', $encodedId);
            } else {
                return redirect()->route('documents.form', $encodedId);
            }
        }

        $feePlans = FeePlan::where('is_active_on_form', 1)
            ->where(function ($query) {
                $query->whereNull('valid_to')
                    ->orWhere('valid_to', '>=', now());
            })
            ->get();

        return view('registration.select-plan', compact('student', 'feePlans', 'encodedId'));
    }

    public function savePlan(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
            'plan_id' => 'required|exists:fee_plan,fee_plan_id'
        ]);

        $studentId = base64_decode($request->student_id);
        $student = Student::findOrFail($studentId);
        $plan = FeePlan::findOrFail($request->plan_id);

        $student->subscription = $plan->fee_name;
        $student->save();

        return redirect()->route('registration.payment', [$request->student_id, $plan->fee_plan_id]);
    }

    public function payLater(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
            'plan_id' => 'required|exists:fee_plan,fee_plan_id'
        ]);

        $studentId = base64_decode($request->student_id);
        $student = Student::findOrFail($studentId);
        $plan = FeePlan::findOrFail($request->plan_id);

        $student->subscription = $plan->fee_name;
        $student->fees_status = 'Pending';
        $student->save();

        if ($student->application_status === 'Documents Submitted') {
            return redirect()->route('registration.success', $request->student_id)
                ->with('success', 'Application saved!');
        }

        return redirect()->route('documents.form', $request->student_id)
            ->with('success', 'Application saved! Please upload your photo to complete the registration.');
    }

    public function paymentForm($encodedId, $planId)
    {
        $studentId = base64_decode($encodedId);
        $student = Student::findOrFail($studentId);
        $plan = FeePlan::findOrFail($planId);

        // Check if there is already a PAID invoice for this specific fee plan for this student
        $alreadyPaid = \App\Models\Invoice::where('student_id', $studentId)
            ->where('status', 'PAID')
            ->whereHas('items', function ($query) use ($planId) {
                $query->where('fee_plan_id', $planId);
            })
            ->exists();

        if ($alreadyPaid) {
            if ($student->application_status === 'Documents Submitted' || in_array($student->application_status, ['Enrolled', 'Approved', 'Done'])) {
                return redirect()->route('registration.success', $encodedId)->with('success', 'Your payment for ' . $plan->fee_name . ' has already been successfully received and registration is completed.');
            }
            return redirect()->route('documents.form', $encodedId)->with('success', 'Your payment for ' . $plan->fee_name . ' has already been successfully received. Please complete your photo upload.');
        }

        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

        $order = $api->order->create([
            'receipt' => 'order_rcptid_' . $studentId,
            'amount' => $plan->amount * 100, // amount in the smallest currency unit
            'currency' => 'INR',
            'notes' => [
                'student_id' => $studentId,
                'plan_id' => $planId
            ]
        ]);

        $student->fees_status = 'In Progress';
        $student->razorpay_order_id = $order['id'];
        $student->save();

        $razorpayData = [
            "key" => config('services.razorpay.key'),
            "amount" => $plan->amount * 100,
            "name" => "PRAMUKH SANKALP Programme",
            "description" => $plan->fee_name,
            "image" => asset('images/sankalp.png'),
            "order_id" => $order['id'],
            "prefill" => [
                "name" => $student->fname . ' ' . $student->lname,
                "email" => $student->email,
                "contact" => $student->contact_no,
            ],
            "theme" => [
                "color" => "#00bde0"
            ],
            "callback_url" => route('registration.payment-status', ['student_id' => $encodedId, 'plan_id' => $planId]),
        ];

        return view('registration.payment', compact('student', 'plan', 'razorpayData', 'encodedId'));
    }

    public function paymentStatus(Request $request)
    {
        $encodedId = $request->student_id;
        $studentId = base64_decode($encodedId);
        $student = Student::findOrFail($studentId);

        // Here you would normally verify the payment with Razorpay
        // For now, let's assume success if we get back from Razorpay
        $alreadyPaid = ($student->fees_status === 'Paid');
        $student->fees_status = 'Paid';

        // Capture Razorpay Payment ID from request or fallback
        $razorpayPaymentId = $request->input('razorpay_payment_id') ?? $request->input('payment_id');
        if ($razorpayPaymentId) {
            $student->razorpay_payment_id = $razorpayPaymentId;
        }

        // Update subscription column if plan_id is provided
        $planId = $request->input('plan_id');
        if ($planId) {
            $plan = \App\Models\FeePlan::find($planId);
            if ($plan) {
                $student->subscription = $plan->fee_name;
            }
        }

        // Generate enrollment number only after payment success
        if (empty($student->enrolment_no)) {
            $student->enrolment_no = \App\Models\Student::generateNextEnrolmentNumber($student->student_id);
        }

        $student->save();

        // Generate Invoice and Payment Log
        try {
            $invoiceService = new \App\Services\InvoiceService();
            $invoiceService->generateRegistrationInvoiceAndPayment($student, $student->razorpay_payment_id);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Registration Invoice Generation Error: ' . $e->getMessage());
        }

        // Send confirmation email with fee details only if not already processed
        if (!$alreadyPaid) {
            $this->sendPaymentSuccessEmail($student);
        }

        if ($student->application_status === 'Documents Submitted') {
            return redirect()->route('registration.success', $encodedId)->with('success', 'Payment successful!');
        }

        return redirect()->route('documents.form', $encodedId)->with('success', 'Payment successful! Please upload your photo.');
    }

    private function sendPaymentSuccessEmail($student)
    {
        try {
            $emailService = new ExternalEmailService();
            $guardian = $student->guardians()->first();
            $guardianName = $guardian ? ($guardian->fname . ' ' . $guardian->lname) : '';
            $studentName = $student->fname . ' ' . $student->lname;

            // Render via dynamic MailTemplateHelper
            $rendered = \App\Helpers\MailTemplateHelper::render('payment_success', [
                'guardian_name' => $guardianName,
                'student_name' => $studentName,
                'subscription' => $student->subscription,
            ], $student->selected_language);

            $to = [$student->email => $student->fname . ' ' . $student->lname];
            $emailService->sendSmtpMail($to, $rendered['subject'], $rendered['message']);
        } catch (\Exception $e) {
            Log::error('Payment Success Email Error: ' . $e->getMessage());
        }
    }

    public function documentForm($encodedId)
    {
        $studentId = base64_decode($encodedId);
        $student = Student::findOrFail($studentId);

        if ($student->registered_via_school_link) {
            if (empty($student->school_payment_screenshot) || $student->school_payment_status === 'Rejected') {
                return redirect()->route('registration.school-payment', $encodedId);
            }
        }

        // If documents are already submitted and fees are paid, redirect directly to success page
        if ($student->application_status === 'Documents Submitted' && ($student->fees_status === 'Paid' || $student->registered_via_school_link)) {
            return redirect()->route('registration.success', $encodedId);
        }

        return view('registration.document', compact('student', 'encodedId'));
    }

    public function sendPreRegistrationOtp(Request $request)
    {
        try {
            // Regular registration is closed: the wizard posts to storeLead()
            // instead. Guards against a direct POST that bypasses the UI.
            if ($this->regularRegistrationClosed()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Registration is currently closed.'),
                ], 422);
            }

            $studentId = $request->input('student_id');
            $decodedStudentId = $studentId ? base64_decode($studentId) : null;

            // If we don't have a student_id from the frontend, but the email already exists
            // and hasn't been verified yet, we treat this as a retry and use the existing record.
            if (!$decodedStudentId && $request->has('email')) {
                $existingStudent = Student::withLeads()->where('email', $request->email)->first();

                // A previously captured lead must not be resumed as a normal
                // registration — send them back to their thank-you page.
                if ($existingStudent && $existingStudent->is_lead) {
                    return response()->json([
                        'success' => false,
                        'message' => __('Your details are already with us.'),
                        'redirect_url' => route('registration.lead-thanks', base64_encode($existingStudent->student_id)),
                    ], 422);
                }

                if ($existingStudent && $existingStudent->is_email_verified == 0) {
                    $decodedStudentId = $existingStudent->student_id;
                }
            }

            $this->normaliseGuardianInput($request);

            // Validate the request
            $validator = Validator::make($request->all(), $this->registrationRules($decodedStudentId), [
                'email.unique' => 'This email is already registered. Please change your email.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // 1. Map Student Data
            $studentData = $this->applySchoolSessionData($this->mapStudentData($request));
            $studentData['is_email_verified'] = 0;

            if ($decodedStudentId) {
                $student = Student::findOrFail($decodedStudentId);
                $student->update($studentData);
            } else {
                $student = Student::create($studentData);
            }

            // 2. Map and Create/Update Guardian
            Guardian::updateOrCreate(
                ['student_id' => $student->student_id],
                $this->mapGuardianData($request, $student->student_id)
            );

            // Generate OTP
            $otp = sprintf("%06d", mt_rand(100000, 999999));
            $student->otp = $otp;
            $student->otp_expires_at = now()->addMinutes(10);
            $student->save();

            DB::commit();

            // Log the OTP for debugging/testing
            Log::info("OTP generated for email " . $student->email . ": " . $otp);

            $emailSent = false;
            $curlResponse = null;
            try {
                $emailService = new ExternalEmailService();
                $firstName = strtoupper(trim($student->fname));
                $encodedStudentId = base64_encode($student->student_id);
                $verifyLink = route('home') . '?student_id=' . urlencode($encodedStudentId);

                $guardian = $student->guardians()->first();
                $guardianName = $guardian ? strtoupper(trim($guardian->fname . ' ' . $guardian->lname)) : strtoupper(trim(($request->guardian_first_name ?? '') . ' ' . ($request->guardian_last_name ?? '')));
                $studentName = strtoupper(trim($student->fname . ' ' . $student->lname));

                $rendered = \App\Helpers\MailTemplateHelper::render('pre_registration_otp', [
                    'guardian_name' => $guardianName,
                    'student_name' => $studentName,
                    'otp' => $otp,
                    'verify_link' => $verifyLink,
                ], $student->selected_language);

                $to = [$student->email => $firstName];
                $emailSent = $emailService->sendSmtpMailDirect($to, $rendered['subject'], $rendered['message']);
                $curlResponse = $emailService->lastResponse;
                if ($emailSent) {
                    Log::info("Email OTP details sent: Email=" . $student->email . ", OTP=" . $otp . ", Link=" . $verifyLink);
                } else {
                    Log::error("Email OTP details send failed: Email=" . $student->email . ", Response=" . $curlResponse);
                }
            } catch (\Exception $e) {
                Log::error('Pre-Registration OTP Email Send Error: ' . $e->getMessage());
                $curlResponse = $e->getMessage();
            }

            if (!$emailSent) {
                Log::warning("Pre-Registration OTP email sending failed, but redirecting user to OTP page. Email=" . $student->email . ", Response=" . $curlResponse);
            }

            return response()->json([
                'success' => true,
                'student_id' => base64_encode($student->student_id),
                'message' => 'OTP sent successfully to your email.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Pre-Registration OTP Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function sendOtp(Request $request)
    {
        $request->validate(['student_id' => 'required']);
        $studentId = base64_decode($request->student_id);
        $student = Student::findOrFail($studentId);

        $otp = sprintf("%06d", mt_rand(100000, 999999));
        $student->otp = $otp;
        $student->otp_expires_at = now()->addMinutes(10);
        $student->save();

        $emailSent = false;
        $curlResponse = null;
        try {
            $emailService = new ExternalEmailService();
            $guardian = $student->guardians()->first();
            $guardianName = $guardian ? strtoupper(trim($guardian->fname . ' ' . $guardian->lname)) : '';
            $studentName = strtoupper(trim($student->fname . ' ' . $student->lname));

            $encodedStudentId = base64_encode($student->student_id);
            $verifyLink = route('home') . '?student_id=' . urlencode($encodedStudentId);

            $rendered = \App\Helpers\MailTemplateHelper::render('pre_registration_otp', [
                'guardian_name' => $guardianName,
                'student_name' => $studentName,
                'otp' => $otp,
                'verify_link' => $verifyLink,
            ], $student->selected_language);
            $to = [$student->email => $student->fname . ' ' . $student->lname];
            $emailSent = $emailService->sendSmtpMailDirect($to, $rendered['subject'], $rendered['message']);
            $curlResponse = $emailService->lastResponse;
        } catch (\Exception $e) {
            Log::error('OTP Email Error: ' . $e->getMessage());
            $curlResponse = $e->getMessage();
        }

        if (!$emailSent) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP.',
                'curl_response' => $curlResponse
            ], 500);
        }

        return response()->json(['success' => true, 'message' => 'OTP sent successfully to your email.']);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
            'otp' => 'required|digits:6'
        ]);

        $studentId = base64_decode($request->student_id);
        $student = Student::findOrFail($studentId);

        if ($student->otp !== $request->otp) {
            return response()->json(['success' => false, 'message' => 'Invalid OTP.']);
        }

        if (now()->greaterThan($student->otp_expires_at)) {
            return response()->json(['success' => false, 'message' => 'OTP has expired.']);
        }

        $student->is_email_verified = true;
        $student->otp = null;
        $student->otp_expires_at = null;
        $student->save();

        $nextUrl = $student->fees_status === 'Paid'
            ? route('documents.form', $request->student_id)
            : route('registration.plans', $request->student_id);

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully.',
            'redirect_url' => $nextUrl
        ]);
    }

    public function documentStore(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $studentId = base64_decode($request->student_id);
        $student = Student::findOrFail($studentId);

        if (!$student->is_email_verified) {
            return back()->with('error', 'Please verify your email using OTP before completing registration.');
        }

        if ($request->hasFile('photo')) {
            $fileName = time() . '_' . $studentId . '_photo.' . $request->file('photo')->extension();

            // Use Laravel's built-in storage to avoid permission issues with public_path on live servers
            $request->file('photo')->storeAs('user-documents/photo', $fileName, 'public');

            $filePath = 'storage/user-documents/photo/' . $fileName;

            Document::updateOrCreate(
                ['student_id' => $studentId],
                ['photo' => $filePath, 'create_at' => now()]
            );
        }

        // Update application status
        $student->application_status = 'Documents Submitted';
        $student->save();

        // Send confirmation email
        // $this->sendDocumentConfirmationEmail($student);

        return redirect()->route('registration.success', ['id' => $request->student_id]);
    }

    private function sendDocumentConfirmationEmail($student)
    {
        try {
            $emailService = new ExternalEmailService();
            $subject = 'Documents Received - PRAMUKH SANKALP Programme';

            $encodedId = base64_encode($student->student_id);
            $paymentLink = route('registration.plans', $encodedId);

            $paymentInfo = "";
            if ($student->fees_status === 'Pending') {
                $paymentInfo = "
                    <p><strong>Payment Pending:</strong></p>
                    <p>You have opted to pay later. You can complete your fee payment at any time by clicking the link below:</p>
                    <p><a href='{$paymentLink}' style='background-color:#00bde0;color:white;padding:12px 24px;text-decoration:none;border-radius:8px;font-weight:bold;display:inline-block;margin-top:10px;margin-bottom:10px;'>Proceed to Payment</a></p>
                    <p>Or copy this link to your browser: <br> {$paymentLink}</p>
                ";
            }

            $message = "
                <p>Dear {$student->fname},</p>
                <p>Thank you for uploading your documents. Your documents are under review.</p>
                <p><strong>પ્રમુખ સંકલ્પ</strong> પ્રોગ્રામ માટે તમારા દસ્તાવેજો અપલોડ કરવા બદલ આભાર. તમારા દસ્તાવેજો અત્યારે ચકાસણી હેઠળ છે.</p>
                {$paymentInfo}
                <p>Best regards,<br>PRAMUKH SANKALP Team</p>
            ";

            $to = [$student->email => $student->fname . ' ' . $student->lname];
            $emailService->sendSmtpMail($to, $subject, $message);
        } catch (\Exception $e) {
            Log::error('Document Confirmation Email Error: ' . $e->getMessage());
        }
    }

    public function success($encodedId)
    {
        $studentId = base64_decode($encodedId);
        $student = Student::withLeads()->findOrFail($studentId);

        // Captured lead — there is no completed registration to show yet.
        if ($student->is_lead) {
            return redirect()->route('registration.lead-thanks', $encodedId);
        }

        // If email is verified and registration is not fully complete, redirect to appropriate step
        if ($student->is_email_verified && $student->application_status !== 'Documents Submitted') {
            if ($student->registered_via_school_link) {
                if (empty($student->school_payment_screenshot) || $student->school_payment_status === 'Rejected') {
                    return redirect()->route('registration.school-payment', $encodedId);
                }
                return redirect()->route('documents.form', $encodedId);
            } else {
                if ($student->fees_status === 'Paid') {
                    return redirect()->route('documents.form', $encodedId);
                } else {
                    return redirect()->route('registration.plans', $encodedId);
                }
            }
        }

        return view('registration.success', compact('student', 'encodedId'));
    }

    /**
     * Suggest schools based on a query.
     */
    public function suggestSchools(Request $request)
    {
        $query = $request->query('query');
        if (!$query || strlen($query) < 2) {
            return response()->json([]);
        }

        $schools = DB::table('school')
            ->where('schoolname', 'like', '%' . $query . '%')
            ->select('school_id', 'schoolname', 'status')
            ->limit(10)
            ->get();

        return response()->json($schools);
    }

    /**
     * Check if a school is connected.
     */
    public function checkSchool(Request $request)
    {
        $name = $request->query('name');
        if (!$name) {
            return response()->json(['status' => 0]);
        }

        $school = DB::table('school')
            ->where('schoolname', $name)
            ->first();

        return response()->json([
            'status' => $school ? $school->status : 0
        ]);
    }

    public function schoolPaymentForm($encodedId)
    {
        $studentId = base64_decode($encodedId);
        $student = Student::findOrFail($studentId);

        if (!$student->registered_via_school_link || !$student->school_id) {
            return redirect()->route('registration.plans', $encodedId);
        }

        $school = $student->school;
        if (!$school) {
            abort(404, 'School not found.');
        }

        return view('registration.school-payment', compact('student', 'school', 'encodedId'));
    }

    public function storeSchoolPayment(Request $request, $encodedId)
    {
        $studentId = base64_decode($encodedId);
        $student = Student::findOrFail($studentId);

        if (!$student->registered_via_school_link || !$student->school_id) {
            return redirect()->route('registration.plans', $encodedId);
        }

        // The student pays for one of the school's fee plans, not the sum of
        // all of them, so the chosen plan has to come back with the payment.
        $customFees = array_values($student->school?->custom_fees ?? []);

        $rules = [
            'school_payment_method' => 'required|string|max:255',
            'school_payment_upi_id' => 'nullable|string|max:255',
            'payment_screenshot' => [
                $student->school_payment_screenshot ? 'nullable' : 'required',
                'image',
                'max:2048'
            ]
        ];

        if (!empty($customFees)) {
            $rules['school_fee_plan_index'] = ['required', Rule::in(array_keys($customFees))];
        }

        $request->validate($rules, [
            'school_fee_plan_index.required' => __('Please select the fee plan you are paying for.'),
            'school_fee_plan_index.in' => __('The selected fee plan is no longer available.'),
        ]);

        $updateData = [
            'school_payment_method' => $request->school_payment_method,
            'school_payment_upi_id' => $request->school_payment_upi_id,
            'school_payment_status' => 'Pending',
            'school_payment_rejection_reason' => null,
        ];

        // Take the name and amount from the school record rather than the
        // request, so a tampered form cannot change what is owed.
        if (!empty($customFees)) {
            $selectedFee = $customFees[(int) $request->school_fee_plan_index];
            $updateData['school_payment_plan_name'] = $selectedFee['name'] ?? null;
            $updateData['school_payment_amount'] = (float) ($selectedFee['amount'] ?? 0);
        }

        if ($request->hasFile('payment_screenshot')) {
            // Delete old screenshot if exists
            if ($student->school_payment_screenshot) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($student->school_payment_screenshot);
            }
            $path = $request->file('payment_screenshot')->store('school_payments', 'public');
            $updateData['school_payment_screenshot'] = $path;
        }

        $student->update($updateData);

        // Audit Log
        app(\App\Services\AuditLoggerService::class)->logCustom(
            'SCHOOL_PAYMENT_SUBMITTED',
            $student,
            "Student {$student->fname} {$student->lname} submitted payment screenshot for school verification.",
            null,
            $student->only(['school_payment_method', 'school_payment_plan_name', 'school_payment_amount', 'school_payment_upi_id', 'school_payment_status'])
        );

        return redirect()->route('documents.form', $encodedId)->with('success', 'Payment screenshot submitted successfully. Please upload required documents next.');
    }
}
