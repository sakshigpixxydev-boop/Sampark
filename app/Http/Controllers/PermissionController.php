<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::orderBy('permkey')->get();
        return view('permissions.index', compact('permissions'));
    }

    public function create()
    {
        return view('permissions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'permkey' => 'required|numeric|unique:permission,permkey',
            'permname' => 'required|string|max:255',
        ]);

        Permission::create([
            'permkey' => $validated['permkey'],
            'permname' => $validated['permname'],
        ]);

        return redirect()->route('permissions.index')->with('success', 'Permission created successfully.');
    }

    public function edit(Permission $permission)
    {
        return view('permissions.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'permkey' => [
                'required',
                'numeric',
                Rule::unique('permission', 'permkey')->ignore($permission->permission_id, 'permission_id'),
            ],
            'permname' => 'required|string|max:255',
        ]);

        $permission->update([
            'permkey' => $validated['permkey'],
            'permname' => $validated['permname'],
        ]);

        return redirect()->route('permissions.index')->with('success', 'Permission updated successfully.');
    }
}
