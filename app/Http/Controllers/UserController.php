<?php

namespace App\Http\Controllers;

use App\Models\SysUser;
use App\Models\Role;
use App\Models\School;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        $users = SysUser::with(['role', 'financialConfigs'])
            ->whereHas('role', function ($query) {
                $query->where('rolename', '!=', 'Student');
            })
            ->orderBy('fname')
            ->paginate(15);
        
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::orderBy('rolename')->get();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:role,role_id',
            'fname' => 'required|string|max:100',
            'mname' => 'nullable|string|max:100',
            'lname' => 'nullable|string|max:100',
            'username' => 'required|string|max:255|unique:sysuser,username',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|digits:10',
            'whatsapp' => 'nullable|digits:10',
            'address1' => 'nullable|string|max:500',
            'address2' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'pswd' => 'required_without:pin|nullable|string|min:8',
            'pin' => 'required_without:pswd|nullable|digits:4',
            'inactive' => 'boolean',
            'financial_type' => 'nullable|string|in:volunteer,paid',
        ]);

        // Set default inactive status
        $validated['inactive'] = $validated['inactive'] ?? 0;
        $validated['createdby'] = Auth::id();

        // Default financial_type for Initiators
        $initiatorRole = Role::where('rolename', 'Initiator')->first();
        if ($initiatorRole && $validated['role_id'] == $initiatorRole->role_id && empty($validated['financial_type'])) {
            $validated['financial_type'] = 'volunteer';
        }

        $user = SysUser::create($validated);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(SysUser $user)
    {
        $roles = Role::orderBy('rolename')->get();
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, SysUser $user)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:role,role_id',
            'fname' => 'required|string|max:100',
            'mname' => 'nullable|string|max:100',
            'lname' => 'nullable|string|max:100',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sysuser', 'username')->ignore($user->sysuser_id, 'sysuser_id'),
            ],
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|digits:10',
            'whatsapp' => 'nullable|digits:10',
            'address1' => 'nullable|string|max:500',
            'address2' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'pswd' => 'nullable|string|min:8',
            'pin' => 'nullable|digits:4',
            'inactive' => 'boolean',
            'financial_type' => 'nullable|string|in:volunteer,paid',
        ]);

        // Only update password/PIN if provided
        if (empty($validated['pswd'])) {
            unset($validated['pswd']);
        }
        if (empty($validated['pin'])) {
            unset($validated['pin']);
        }

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(SysUser $user)
    {
        if (Auth::id() === $user->sysuser_id) {
            return redirect()->route('users.index')->with('error', 'You cannot delete your own account.');
        }

        // These tables cascade (or hold history) on sysuser delete, so a user
        // with any of these records must be deactivated instead.
        $linkedRecords = [
            'schools initiated' => DB::table('school')->where('initiator_id', $user->sysuser_id)->exists(),
            'initiator payments' => DB::table('initiator_payments')->where('initiator_id', $user->sysuser_id)->exists(),
            'school leads' => DB::table('school_leads')->where('initiator_id', $user->sysuser_id)->exists(),
            'tickets' => DB::table('tickets')->where('created_by', $user->sysuser_id)->exists(),
            'upload batches' => DB::table('upload_batch')->where('uploaded_by', $user->sysuser_id)->exists(),
            'student growth records' => DB::table('student_growth_records')->where('created_by', $user->sysuser_id)->exists(),
        ];
        $blocking = array_keys(array_filter($linkedRecords));

        if ($blocking) {
            return redirect()->route('users.index')->with('error',
                'Cannot delete ' . $user->username . ' because they have linked ' . implode(', ', $blocking) . '. Mark the user inactive instead.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    public function assignSchools(SysUser $user)
    {
        // Only allow assignment for Initiator and School roles
        $allowedRoles = ['Initiator', 'School'];
        $userRole = $user->role->rolename ?? '';
        
        if (!in_array($userRole, $allowedRoles)) {
            return redirect()->route('users.index')
                ->with('error', 'School assignments are only available for Initiator and School roles.');
        }

        $assignedSchools = $user->schools->pluck('school_id')->toArray();
        $schools = School::active()->where('status', 1)->orderBy('schoolname')->get();
        
        return view('users.assign-schools', compact('user', 'schools', 'assignedSchools'));
    }

    public function updateSchools(Request $request, SysUser $user)
    {
        // Only allow assignment for Initiator and School roles
        $allowedRoles = ['Initiator', 'School'];
        $userRole = $user->role->rolename ?? '';
        
        if (!in_array($userRole, $allowedRoles)) {
            return redirect()->route('users.index')
                ->with('error', 'School assignments are only available for Initiator and School roles.');
        }

        $validated = $request->validate([
            'schools' => 'nullable|array',
            'schools.*' => 'exists:school,school_id',
        ]);

        $schoolIds = $validated['schools'] ?? [];
        $user->schools()->sync($schoolIds);

        return redirect()->route('users.index')
            ->with('success', 'School assignments updated successfully.');
    }

    public function assignStudents(SysUser $user)
    {
        // Only allow assignment for Initiator role
        $userRole = $user->role->rolename ?? '';
        
        if ($userRole !== 'Initiator') {
            return redirect()->route('users.index')
                ->with('error', 'Student assignments are only available for Initiator role.');
        }

        $assignedStudents = $user->students->pluck('student_id')->toArray();
        
        // Get students with their related data for better display
        $students = Student::with(['school', 'standard', 'educationalBoard'])
            ->orderBy('fname')
            ->orderBy('mname')
            ->orderBy('lname')
            ->get();
        
        return view('users.assign-students', compact('user', 'students', 'assignedStudents'));
    }

    public function updateStudents(Request $request, SysUser $user)
    {
        // Only allow assignment for Initiator role
        $userRole = $user->role->rolename ?? '';
        
        if ($userRole !== 'Initiator') {
            return redirect()->route('users.index')
                ->with('error', 'Student assignments are only available for Initiator role.');
        }

        $validated = $request->validate([
            'students' => 'nullable|array',
            'students.*' => 'exists:student,student_id',
        ]);

        $studentIds = $validated['students'] ?? [];
        $user->students()->sync($studentIds);

        return redirect()->route('users.index')
            ->with('success', 'Student assignments updated successfully.');
    }

    public function impersonate(SysUser $user)
    {
        $currentUser = Auth::user();
        if ($currentUser->sysuser_id === $user->sysuser_id) {
            return redirect()->route('users.index')->with('error', 'You cannot impersonate yourself.');
        }

        // Store original user ID in session
        session(['impersonator_id' => $currentUser->sysuser_id]);

        // Audit Log
        app(\App\Services\AuditLoggerService::class)->logAuth(
            'IMPERSONATION_START',
            $user->sysuser_id,
            "User " . $currentUser->username . " started impersonating " . $user->username,
            ['impersonated_by' => $currentUser->sysuser_id]
        );

        // Login as the target user
        Auth::loginUsingId($user->sysuser_id);

        return redirect()->route('dashboard')->with('success', 'Logged in as ' . $user->username);
    }

    public function stopImpersonating()
    {
        if (!session()->has('impersonator_id')) {
            return redirect()->route('dashboard');
        }

        $impersonatorId = session('impersonator_id');
        $originalUser = SysUser::findOrFail($impersonatorId);
        $impersonatedUser = Auth::user();

        // Audit Log
        app(\App\Services\AuditLoggerService::class)->logAuth(
            'IMPERSONATION_STOP',
            $impersonatedUser ? $impersonatedUser->sysuser_id : null,
            "User " . $originalUser->username . " stopped impersonating target user.",
            ['impersonated_by' => $impersonatorId]
        );

        // Login back as original user
        Auth::loginUsingId($impersonatorId);

        // Clear session key
        session()->forget('impersonator_id');

        return redirect()->route('users.index')->with('success', 'Returned to original account.');
    }
}
