<?php

namespace App\Http\Controllers;

use App\Models\SysUser;
use App\Models\InitiatorFinancialConfig;
use Illuminate\Http\Request;

class InitiatorFinancialController extends Controller
{
    public function edit(SysUser $user)
    {
        if (!$user->hasRole('Initiator') || $user->financial_type !== 'paid') {
            return redirect()->route('users.index')->with('error', 'Financial configuration is only available for Paid Initiators.');
        }

        $configs = InitiatorFinancialConfig::all();
        $assignedConfigId = $user->financialConfigs()->pluck('initiator_financial_configs.id')->first();

        return view('users.financial-config', compact('user', 'configs', 'assignedConfigId'));
    }

    public function update(Request $request, SysUser $user)
    {
        if (!$user->hasRole('Initiator') || $user->financial_type !== 'paid') {
            return redirect()->route('users.index')->with('error', 'Financial configuration is only available for Paid Initiators.');
        }

        $validated = $request->validate([
            'config_id' => 'nullable|exists:initiator_financial_configs,id',
        ]);

        $user->financialConfigs()->sync($validated['config_id'] ? [$validated['config_id']] : []);

        return redirect()->route('users.index')->with('success', 'Initiator Fee Plan assigned successfully.');
    }
}
