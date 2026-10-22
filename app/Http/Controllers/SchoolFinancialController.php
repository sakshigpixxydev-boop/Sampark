<?php

namespace App\Http\Controllers;

use App\Models\SysUser;
use App\Models\FeePlan;
use Illuminate\Http\Request;

class SchoolFinancialController extends Controller
{
    public function edit(SysUser $user)
    {
        if (!$user->hasRole('School')) {
            return redirect()->route('users.index')->with('error', 'Financial configuration is only available for School users.');
        }

        $feePlans = FeePlan::where('plan_type', 'school')->get();
        $assignedFeePlanIds = $user->schoolFeePlans()->pluck('fee_plan.fee_plan_id')->toArray();

        return view('users.school-financial-config', compact('user', 'feePlans', 'assignedFeePlanIds'));
    }

    public function update(Request $request, SysUser $user)
    {
        if (!$user->hasRole('School')) {
            return redirect()->route('users.index')->with('error', 'Financial configuration is only available for School users.');
        }

        $validated = $request->validate([
            'fee_plan_ids' => 'nullable|array',
            'fee_plan_ids.*' => 'exists:fee_plan,fee_plan_id',
        ]);

        $user->schoolFeePlans()->sync($validated['fee_plan_ids'] ?? []);

        return redirect()->route('users.index')->with('success', 'School Fee Plans assigned successfully.');
    }
}
