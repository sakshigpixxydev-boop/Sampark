<?php

namespace App\Http\Controllers;

use App\Models\SchoolLead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\DateHelper;

class LeadController extends Controller
{
    /**
     * Display a listing of leads
     */
    public function index(Request $request)
    {
        $query = SchoolLead::with(['initiator', 'school']);

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Search by school name, email, or phone
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('school_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $leads = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('leads.index', compact('leads'));
    }

    /**
     * Show the form for creating a new lead
     */
    public function create()
    {
        $defaultTemplate = \App\Models\AgreementTemplate::getActiveBySlug('school_registration');
        return view('leads.create', compact('defaultTemplate'));
    }

    /**
     * Store a newly created lead
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_name' => 'required|string|max:1000',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|min:7|max:15',
            'estimated_students' => 'nullable|integer|min:1',
            'address' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'notes' => 'nullable|string',
            'tc_expiry_date' => [
                'nullable',
                'date_format_dmy',
                function ($attribute, $value, $fail) {
                    $date = DateHelper::convertDateFormat($value);
                    if ($date && strtotime($date) <= strtotime('today')) {
                        $fail('The tc expiry date must be a date after today.');
                    }
                }
            ],
            'tc_content' => 'required|string',
        ]);

        $validated['tc_expiry_date'] = DateHelper::convertDateFormat($validated['tc_expiry_date']);


        $validated['initiator_id'] = Auth::user()->sysuser_id;

        $lead = SchoolLead::create($validated);

        return redirect()->route('leads.show', $lead->lead_id)
            ->with('success', 'Lead created successfully! Share the registration URL with the school.');
    }

    /**
     * Display the specified lead
     */
    public function show($id)
    {
        $lead = SchoolLead::with(['initiator', 'school'])->findOrFail($id);

        return view('leads.show', compact('lead'));
    }

    /**
     * Show the form for editing the specified lead
     */
    public function edit($id)
    {
        $lead = SchoolLead::findOrFail($id);

        // Only allow editing if lead is still pending
        if ($lead->status !== 'pending') {
            return redirect()->route('leads.show', $id)
                ->with('error', 'Cannot edit a lead that has been registered or expired.');
        }

        return view('leads.edit', compact('lead'));
    }

    /**
     * Update the specified lead
     */
    public function update(Request $request, $id)
    {
        $lead = SchoolLead::findOrFail($id);

        // Only allow updating if lead is still pending
        if ($lead->status !== 'pending') {
            return redirect()->route('leads.show', $id)
                ->with('error', 'Cannot update a lead that has been registered or expired.');
        }

        $validated = $request->validate([
            'school_name' => 'required|string|max:1000',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|min:7|max:15',
            'estimated_students' => 'nullable|integer|min:1',
            'address' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'notes' => 'nullable|string',
            'tc_expiry_date' => [
                'nullable',
                'date_format_dmy',
                function ($attribute, $value, $fail) {
                    $date = DateHelper::convertDateFormat($value);
                    if ($date && strtotime($date) <= strtotime('today')) {
                        $fail('The tc expiry date must be a date after today.');
                    }
                }
            ],
            'tc_content' => 'required|string',
        ]);

        $validated['tc_expiry_date'] = DateHelper::convertDateFormat($validated['tc_expiry_date']);

        $lead->update($validated);

        return redirect()->route('leads.show', $id)
            ->with('success', 'Lead updated successfully!');
    }

    /**
     * Regenerate registration token for a lead
     */
    public function regenerateToken($id)
    {
        $lead = SchoolLead::findOrFail($id);

        // Only allow regenerating if lead is still pending
        if ($lead->status !== 'pending') {
            return redirect()->route('leads.show', $id)
                ->with('error', 'Cannot regenerate token for a lead that has been registered or expired.');
        }

        $lead->update([
            'registration_token' => SchoolLead::generateUniqueToken(),
            'token_expires_at' => now()->addDays(30),
            'token_used_at' => null,
        ]);

        return redirect()->route('leads.show', $id)
            ->with('success', 'New registration URL generated successfully!');
    }
}
