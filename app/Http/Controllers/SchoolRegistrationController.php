<?php

namespace App\Http\Controllers;

use App\Models\SchoolLead;
use App\Models\School;
use App\Models\SysUser;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SchoolRegistrationController extends Controller
{
    /**
     * Show registration landing page (login or create account choice)
     */
    public function showRegistrationForm($token)
    {
        $lead = SchoolLead::where('registration_token', $token)->first();

        if (!$lead) {
            return view('school-registration.invalid-token');
        }

        if (!$lead->isTokenValid()) {
            return view('school-registration.expired-token', compact('lead'));
        }

        return view('school-registration.landing', compact('lead'));
    }

    /**
     * Show create account form for new user
     */
    public function showCreateAccountForm($token)
    {
        $lead = SchoolLead::where('registration_token', $token)->first();

        if (!$lead) {
            return view('school-registration.invalid-token');
        }

        if (!$lead->isTokenValid()) {
            return view('school-registration.expired-token', compact('lead'));
        }

        return view('school-registration.create-account', [
            'lead' => $lead,
            'token' => $token,
        ]);
    }

    /**
     * Check if user exists
     */
    public function checkExistingUser(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = SysUser::where('email', $request->email)->first();

        return response()->json([
            'exists' => $user ? true : false,
            'message' => $user ? 'User exists, please login' : 'User not found, please create account'
        ]);
    }

    /**
     * Create new school admin account
     */
    public function createAccount(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'fname' => 'required|string|max:100',
            'lname' => 'nullable|string|max:100',
            'mname' => 'nullable|string|max:100',
            'email' => 'required|email|max:250|unique:sysuser,email',
            'phone' => 'required|digits:10',
            'username' => 'required|string|max:255|unique:sysuser,username',
            'pswd' => 'required|string|min:6|confirmed',
            'whatsapp' => 'nullable|digits:10',
        ]);

        $lead = SchoolLead::where('registration_token', $validated['token'])->first();

        if (!$lead || !$lead->isTokenValid()) {
            return redirect()->back()->with('error', 'Invalid or expired registration token.');
        }

        DB::beginTransaction();
        try {
            // Get School role
            $schoolRole = Role::where('rolename', 'School')->first();

            if (!$schoolRole) {
                throw new \Exception('School role not found. Please contact administrator.');
            }

            // Create user
            $user = SysUser::create([
                'role_id' => $schoolRole->role_id,
                'fname' => $validated['fname'],
                'lname' => $validated['lname'],
                'mname' => $validated['mname'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'username' => $validated['username'],
                'pswd' => $validated['pswd'], // Will be hashed by mutator
                'whatsapp' => $validated['whatsapp'] ?? null,
                'inactive' => false,
            ]);

            // Auto-login the user
            Auth::login($user);

            DB::commit();

            // Redirect to school form
            return redirect()->route('school-registration.school-form', ['token' => $validated['token']])
                ->with('success', 'Account created successfully! Please complete school registration.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create account: ' . $e->getMessage());
        }
    }

    /**
     * Login existing user
     */
    public function loginExisting(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'token' => 'required|string',
        ]);

        $lead = SchoolLead::where('registration_token', $credentials['token'])->first();

        if (!$lead || !$lead->isTokenValid()) {
            return redirect()->back()->with('error', 'Invalid or expired registration token.');
        }

        // Attempt login
        $user = SysUser::where('username', $credentials['username'])->first();

        if ($user && Hash::check($credentials['password'], $user->pswd)) {
            Auth::login($user);

            return redirect()->route('school-registration.school-form', ['token' => $credentials['token']])
                ->with('success', 'Logged in successfully! Please complete school registration.');
        }

        return redirect()->back()
            ->withInput($request->only('username'))
            ->with('error', 'Invalid credentials.');
    }

    /**
     * Show school registration form
     */
    public function showSchoolForm($token)
    {
        if (!Auth::check()) {
            return redirect()->route('school-registration.landing', ['token' => $token])
                ->with('error', 'Please login or create an account first.');
        }

        $lead = SchoolLead::where('registration_token', $token)->first();

        if (!$lead || !$lead->isTokenValid()) {
            return view('school-registration.expired-token', compact('lead'));
        }

        // Get necessary data for form
        $schoolTypes = \App\Models\SchoolType::all();
        $educationalBoards = \App\Models\EducationalBoard::all();
        $india = \App\Models\Country::where('cname', 'India')->first();
        $indiaCountryId = $india ? $india->country_id : 1;

        return view('school-registration.school-form', compact('lead', 'schoolTypes', 'educationalBoards', 'indiaCountryId'));
    }

    /**
     * Register school
     */
    public function registerSchool(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'schooltype_id' => 'required|exists:schooltype,schooltype_id',
            'eduboard_id' => 'required|exists:ms_eductionalboard,eduboard_id',
            'schoolname' => 'required|string|max:1000',
            'address1' => 'nullable|string|max:1000',
            'address2' => 'nullable|string|max:255',
            'city_village' => 'nullable|string|max:100',
            'takula_id' => 'required|exists:ms_taluko,takula_id',
            'pincode' => 'nullable|digits:6',
            'phone' => 'nullable|digits:10',
            'mobile' => 'nullable|digits:10',
            'email' => 'nullable|email|max:150',
            'website' => 'nullable|url|max:255',
            'referral_link' => 'nullable|url|max:255',
            'establishedyear' => 'nullable|integer|min:1800|max:' . date('Y'),
            'adminname' => 'required|string|max:200',
            'admincontact' => 'required|digits:10',
            'auth_role' => 'required|string|in:Principal,Trustee,Others,Admin',
            'auth_role_other' => 'required_if:auth_role,Others|nullable|string|max:100',
            'auth_name' => 'required|string|max:200',
            'auth_email' => 'required|email|max:150',
            'auth_phone' => 'required|digits:10',
            'geolocation' => 'nullable|string|max:500',
            'auth2_role' => 'nullable|string|in:Principal,Trustee,Others,Admin',
            'auth2_role_other' => 'required_if:auth2_role,Others|nullable|string|max:100',
            'auth2_name' => 'required_with:auth2_role|nullable|string|max:200',
            'auth2_email' => 'required_with:auth2_role|nullable|email|max:150',
            'auth2_phone' => 'required_with:auth2_role|nullable|digits:10',
            'logourl' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $lead = SchoolLead::where('registration_token', $validated['token'])->first();

        if (!$lead || !$lead->isTokenValid()) {
            return redirect()->back()->with('error', 'Invalid or expired registration token.');
        }

        DB::beginTransaction();
        try {
            // Handle logo upload
            $logoPath = null;
            if ($request->hasFile('logourl')) {
                $logoPath = $request->file('logourl')->store('school-logos', 'public');
            }

            // Create school
            $school = School::create([
                'lead_id' => $lead->lead_id,
                'initiator_id' => $lead->initiator_id,
                'schooltype_id' => $validated['schooltype_id'],
                'eduboard_id' => $validated['eduboard_id'],
                'schoolname' => $validated['schoolname'],
                'logourl' => $logoPath,
                'address1' => $validated['address1'] ?? null,
                'address2' => $validated['address2'] ?? null,
                'city_village' => $validated['city_village'] ?? null,
                'takula_id' => $validated['takula_id'],
                'pincode' => $validated['pincode'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'mobile' => $validated['mobile'] ?? null,
                'email' => $validated['email'] ?? null,
                'website' => $validated['website'] ?? null,
                'geolocation' => $validated['geolocation'] ?? null,
                'referral_link' => $validated['referral_link'] ?? null,
                'establishedyear' => $validated['establishedyear'] ?? null,
                'adminname' => $validated['adminname'],
                'admincontact' => $validated['admincontact'],
                'auth_role' => $validated['auth_role'],
                'auth_role_other' => $validated['auth_role_other'] ?? null,
                'auth_name' => $validated['auth_name'],
                'auth_email' => $validated['auth_email'],
                'auth_phone' => $validated['auth_phone'],
                'auth2_role' => $validated['auth2_role'] ?? null,
                'auth2_role_other' => $validated['auth2_role_other'] ?? null,
                'auth2_name' => $validated['auth2_name'] ?? null,
                'auth2_email' => $validated['auth2_email'] ?? null,
                'auth2_phone' => $validated['auth2_phone'] ?? null,
                'status' => 0, // Lead status
                'inactive' => false,
                'createdby' => Auth::user()->username,
            ]);

            // Link school admin to school
            Auth::user()->schools()->attach($school->school_id);

            // Mark lead as used
            $lead->markTokenAsUsed($school->school_id);

            DB::commit();

            return redirect()->route('dashboard')
                ->with('success', 'School registered successfully! Your school is pending approval from admin.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to register school: ' . $e->getMessage());
        }
    }
}
