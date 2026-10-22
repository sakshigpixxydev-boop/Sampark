<?php

namespace App\Http\Controllers;

use App\Models\SysUser;
use App\Models\Role;
use App\Models\Permission;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SchoolEducatorController extends Controller
{
    private function checkSchoolAdmin()
    {
        $user = Auth::user();
        if (!$user->hasRole('School') && !$user->hasRole('Admin')) {
            abort(403, 'Unauthorized action. Only School Administrators can manage educators.');
        }
    }

    public function index()
    {
        $this->checkSchoolAdmin();
        $user = Auth::user();
        $schoolIds = $user->getAssignedSchoolIds();

        if (empty($schoolIds)) {
            $educators = collect();
        } else {
            $educators = SysUser::whereHas('role', function ($q) {
                    $q->where('rolename', 'like', 'Educator%');
                })
                ->whereHas('schools', function ($q) use ($schoolIds) {
                    $q->whereIn('school.school_id', $schoolIds);
                })
                ->get();
        }

        return view('school.educators.index', compact('educators'));
    }

    public function create()
    {
        $this->checkSchoolAdmin();
        
        $educatorPermissions = Permission::whereIn('permkey', [
            11601, // Fee Collection
            11602, // Registration Process
            11603, // Form Generation
            11604, // Bulk Upload
            11605, // Student Approve
            11606, // Student Reject
        ])->get();

        return view('school.educators.create', compact('educatorPermissions'));
    }

    public function store(Request $request)
    {
        $this->checkSchoolAdmin();
        $user = Auth::user();
        $schoolIds = $user->getAssignedSchoolIds();

        if (empty($schoolIds)) {
            return redirect()->back()->with('error', 'You must be assigned to at least one school to create an educator.');
        }

        $validated = $request->validate([
            'fname' => 'required|string|max:100',
            'mname' => 'nullable|string|max:100',
            'lname' => 'nullable|string|max:100',
            'username' => 'required|string|max:255|unique:sysuser,username',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|digits:10',
            'pswd' => 'required|string|min:8',
            'pin' => 'nullable|digits:4',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permission,permkey',
        ]);

        DB::beginTransaction();
        try {
            // 1. Create a dynamic role for this educator
            $roleName = "Educator - " . $validated['username'];
            $role = Role::create([
                'rolename' => $roleName,
                'ispredefined' => false,
            ]);

            // 2. Sync permissions with the role
            $selectedPermKeys = $validated['permissions'] ?? [];
            $permissionIds = Permission::whereIn('permkey', $selectedPermKeys)->pluck('permission_id')->toArray();
            $role->permissions()->sync($permissionIds);

            // 3. Create the user
            $educator = SysUser::create([
                'role_id' => $role->role_id,
                'fname' => $validated['fname'],
                'mname' => $validated['mname'],
                'lname' => $validated['lname'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'pswd' => $validated['pswd'],
                'pin' => $validated['pin'] ?? null,
                'inactive' => false,
                'createdby' => $user->sysuser_id,
            ]);

            // 4. Link educator to the school(s) managed by this School Admin
            $educator->schools()->sync($schoolIds);

            DB::commit();
            return redirect()->route('school.educators.index')->with('success', 'Educator created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Failed to create educator: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $this->checkSchoolAdmin();
        $user = Auth::user();
        $schoolIds = $user->getAssignedSchoolIds();

        $educator = SysUser::whereHas('role', function ($q) {
                $q->where('rolename', 'like', 'Educator%');
            })
            ->whereHas('schools', function ($q) use ($schoolIds) {
                $q->whereIn('school.school_id', $schoolIds);
            })
            ->findOrFail($id);

        $educatorPermissions = Permission::whereIn('permkey', [
            11601, // Fee Collection
            11602, // Registration Process
            11603, // Form Generation
            11604, // Bulk Upload
            11605, // Student Approve
            11606, // Student Reject
        ])->get();

        $assignedPermKeys = [];
        if ($educator->role) {
            $assignedPermKeys = $educator->role->permissions()->pluck('permkey')->toArray();
        }

        return view('school.educators.edit', compact('educator', 'educatorPermissions', 'assignedPermKeys'));
    }

    public function update(Request $request, $id)
    {
        $this->checkSchoolAdmin();
        $user = Auth::user();
        $schoolIds = $user->getAssignedSchoolIds();

        $educator = SysUser::whereHas('role', function ($q) {
                $q->where('rolename', 'like', 'Educator%');
            })
            ->whereHas('schools', function ($q) use ($schoolIds) {
                $q->whereIn('school.school_id', $schoolIds);
            })
            ->findOrFail($id);

        $validated = $request->validate([
            'fname' => 'required|string|max:100',
            'mname' => 'nullable|string|max:100',
            'lname' => 'nullable|string|max:100',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sysuser', 'username')->ignore($educator->sysuser_id, 'sysuser_id'),
            ],
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|digits:10',
            'pswd' => 'nullable|string|min:8',
            'pin' => 'nullable|digits:4',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permission,permkey',
        ]);

        DB::beginTransaction();
        try {
            // Update role permissions
            $role = $educator->role;
            if (!$role) {
                $roleName = "Educator - " . $validated['username'];
                $role = Role::create([
                    'rolename' => $roleName,
                    'ispredefined' => false,
                ]);
                $educator->role_id = $role->role_id;
            } else {
                // If username changed, update role name
                if ($role->rolename !== "Educator - " . $validated['username']) {
                    $role->update(['rolename' => "Educator - " . $validated['username']]);
                }
            }

            $selectedPermKeys = $validated['permissions'] ?? [];
            $permissionIds = Permission::whereIn('permkey', $selectedPermKeys)->pluck('permission_id')->toArray();
            $role->permissions()->sync($permissionIds);

            // Update user details
            $userData = [
                'fname' => $validated['fname'],
                'mname' => $validated['mname'],
                'lname' => $validated['lname'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
            ];

            if ($request->filled('pswd')) {
                $userData['pswd'] = $validated['pswd'];
            }
            if ($request->filled('pin')) {
                $userData['pin'] = $validated['pin'];
            }

            $educator->update($userData);

            DB::commit();
            return redirect()->route('school.educators.index')->with('success', 'Educator updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Failed to update educator: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $this->checkSchoolAdmin();
        $user = Auth::user();
        $schoolIds = $user->getAssignedSchoolIds();

        $educator = SysUser::whereHas('role', function ($q) {
                $q->where('rolename', 'like', 'Educator%');
            })
            ->whereHas('schools', function ($q) use ($schoolIds) {
                $q->whereIn('school.school_id', $schoolIds);
            })
            ->findOrFail($id);

        DB::beginTransaction();
        try {
            $role = $educator->role;
            
            // Unlink school associations
            $educator->schools()->detach();
            
            // Delete user
            $educator->delete();

            // Delete user's custom role
            if ($role && !$role->ispredefined) {
                $role->permissions()->detach();
                $role->delete();
            }

            DB::commit();
            return redirect()->route('school.educators.index')->with('success', 'Educator deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to delete educator: ' . $e->getMessage());
        }
    }
}
