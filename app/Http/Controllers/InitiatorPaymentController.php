<?php

namespace App\Http\Controllers;

use App\Models\SysUser;
use App\Models\InitiatorPayment;
use App\Services\InitiatorFinancialService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\DateHelper;

class InitiatorPaymentController extends Controller
{
    protected $financialService;

    public function __construct(InitiatorFinancialService $financialService)
    {
        $this->financialService = $financialService;
    }

    public function index(SysUser $user)
    {
        if (!$user->isPaidInitiator()) {
            return redirect()->route('users.index')->with('error', 'Payments can only be recorded for Paid Initiators.');
        }

        $earnings = $this->financialService->getDetailedEarnings($user);
        $payments = $user->initiatorPayments()->orderBy('payment_date', 'desc')->get();

        return view('users.initiator-payments', compact('user', 'earnings', 'payments'));
    }

    public function store(Request $request, SysUser $user)
    {
        if (!$user->isPaidInitiator()) {
            return redirect()->route('users.index')->with('error', 'Payments can only be recorded for Paid Initiators.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date_format_dmy',
            'payment_method' => 'nullable|string|max:50',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        if (isset($validated['payment_date'])) {
            $validated['payment_date'] = DateHelper::convertDateFormat($validated['payment_date']);
        }

        $validated['initiator_id'] = $user->sysuser_id;
        $validated['created_by'] = Auth::id();

        InitiatorPayment::create($validated);

        return redirect()->route('users.initiator-payments', $user->sysuser_id)
            ->with('success', 'Payment recorded successfully.');
    }

    public function destroy(InitiatorPayment $payment)
    {
        $initiatorId = $payment->initiator_id;
        InitiatorPayment::destroy($payment->payment_id);

        return redirect()->route('users.initiator-payments', $initiatorId)
            ->with('success', 'Payment record deleted successfully.');
    }
}
