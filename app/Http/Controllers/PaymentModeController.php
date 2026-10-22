<?php

namespace App\Http\Controllers;

use App\Models\PaymentMode;
use Illuminate\Http\Request;

class PaymentModeController extends Controller
{
    /**
     * Display a listing of payment modes.
     */
    public function index()
    {
        $paymentModes = PaymentMode::orderBy('paymodename')->paginate(15);
        
        return view('payment-modes.index', compact('paymentModes'));
    }
    
    /**
     * Show the form for creating a new payment mode.
     */
    public function create()
    {
        return view('payment-modes.create');
    }
    
    /**
     * Store a newly created payment mode in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'paymodename' => 'required|string|max:100|unique:paymentmode,paymodename',
        ]);
        
        PaymentMode::create($validated);
        
        return redirect()->route('payment-modes.index')
            ->with('success', 'Payment mode created successfully.');
    }
    
    /**
     * Show the form for editing the specified payment mode.
     */
    public function edit(PaymentMode $paymentMode)
    {
        return view('payment-modes.edit', compact('paymentMode'));
    }
    
    /**
     * Update the specified payment mode in storage.
     */
    public function update(Request $request, PaymentMode $paymentMode)
    {
        $validated = $request->validate([
            'paymodename' => 'required|string|max:100|unique:paymentmode,paymodename,' . $paymentMode->paymode_id . ',paymode_id',
        ]);
        
        $paymentMode->update($validated);
        
        return redirect()->route('payment-modes.index')
            ->with('success', 'Payment mode updated successfully.');
    }
    
    /**
     * Remove the specified payment mode from storage.
     */
    public function destroy(PaymentMode $paymentMode)
    {
        // Check if payment mode is in use
        if ($paymentMode->paymentLogs()->exists() || $paymentMode->uploadBatches()->exists()) {
            return redirect()->route('payment-modes.index')
                ->with('error', 'Cannot delete payment mode that is in use.');
        }
        
        $paymentMode->delete();
        
        return redirect()->route('payment-modes.index')
            ->with('success', 'Payment mode deleted successfully.');
    }
}
