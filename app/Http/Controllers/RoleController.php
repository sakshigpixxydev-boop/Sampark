<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->orderBy('rolename')->get();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('permkey')->get();
        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rolename' => 'required|string|max:100|unique:role,rolename',
            'ispredefined' => 'boolean',
            'permissions' => 'array',
            'permissions.*' => 'exists:permission,permission_id',
        ]);

        $role = Role::create([
            'rolename' => $validated['rolename'],
            'ispredefined' => $validated['ispredefined'] ?? 0,
        ]);

        if (!empty($validated['permissions'])) {
            $role->permissions()->attach($validated['permissions']);
        }

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::orderBy('permkey')->get();
        $rolePermissions = $role->permissions->pluck('permission_id')->toArray();
        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'rolename' => [
                'required',
                'string',
                'max:100',
                Rule::unique('role', 'rolename')->ignore($role->role_id, 'role_id'),
            ],
            'permissions' => 'array',
            'permissions.*' => 'exists:permission,permission_id',
        ]);

        $role->update([
            'rolename' => $validated['rolename'],
        ]);

        // Sync permissions
        $role->permissions()->sync($validated['permissions'] ?? []);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        // Prevent deletion of predefined roles
        if ($role->ispredefined) {
            return redirect()->route('roles.index')->with('error', 'Cannot delete predefined roles.');
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')->with('error', 'Cannot delete role that has assigned users.');
        }

        $role->permissions()->detach();
        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }
}
