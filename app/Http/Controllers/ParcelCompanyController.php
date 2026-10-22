<?php

namespace App\Http\Controllers;

use App\Models\ParcelCompany;
use Illuminate\Http\Request;

class ParcelCompanyController extends Controller
{
    /**
     * Display a listing of parcel companies
     */
    public function index(Request $request)
    {
        $query = ParcelCompany::query();
        
        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('inactive', false);
            } elseif ($request->status === 'inactive') {
                $query->where('inactive', true);
            }
        }
        
        // Search by company name
        if ($request->filled('search')) {
            $query->where('companyname', 'like', '%' . $request->search . '%');
        }
        
        $companies = $query->orderBy('companyname')->paginate(15);
        
        return view('parcel-companies.index', compact('companies'));
    }
    
    /**
     * Show the form for creating a new parcel company
     */
    public function create()
    {
        return view('parcel-companies.create');
    }
    
    /**
     * Store a newly created parcel company
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'companyname' => 'required|string|max:255',
            'trackingurl' => 'nullable|string|max:500|url',
            'apiendpoint' => 'nullable|string|max:255|url',
            'apikey' => 'nullable|string|max:255',
            'supportcontact' => 'nullable|string|max:50',
            'deliverytimeestimate' => 'nullable|string|max:50',
            'inactive' => 'boolean',
        ], [
            'companyname.required' => 'Company name is required.',
            'companyname.max' => 'Company name cannot exceed 255 characters.',
            'trackingurl.url' => 'Tracking URL must be a valid URL.',
            'trackingurl.max' => 'Tracking URL cannot exceed 500 characters.',
            'apiendpoint.url' => 'API endpoint must be a valid URL.',
            'apiendpoint.max' => 'API endpoint cannot exceed 255 characters.',
            'apikey.max' => 'API key cannot exceed 255 characters.',
            'supportcontact.max' => 'Support contact cannot exceed 50 characters.',
            'deliverytimeestimate.max' => 'Delivery time estimate cannot exceed 50 characters.',
        ]);
        
        $validated['inactive'] = $request->has('inactive') ? true : false;
        
        ParcelCompany::create($validated);
        
        return redirect()->route('parcel-companies.index')
            ->with('success', 'Parcel company created successfully.');
    }
    
    /**
     * Show the form for editing a parcel company
     */
    public function edit(ParcelCompany $parcelCompany)
    {
        return view('parcel-companies.edit', compact('parcelCompany'));
    }
    
    /**
     * Update the specified parcel company
     */
    public function update(Request $request, ParcelCompany $parcelCompany)
    {
        $validated = $request->validate([
            'companyname' => 'required|string|max:255',
            'trackingurl' => 'nullable|string|max:500|url',
            'apiendpoint' => 'nullable|string|max:255|url',
            'apikey' => 'nullable|string|max:255',
            'supportcontact' => 'nullable|string|max:50',
            'deliverytimeestimate' => 'nullable|string|max:50',
            'inactive' => 'boolean',
        ], [
            'companyname.required' => 'Company name is required.',
            'companyname.max' => 'Company name cannot exceed 255 characters.',
            'trackingurl.url' => 'Tracking URL must be a valid URL.',
            'trackingurl.max' => 'Tracking URL cannot exceed 500 characters.',
            'apiendpoint.url' => 'API endpoint must be a valid URL.',
            'apiendpoint.max' => 'API endpoint cannot exceed 255 characters.',
            'apikey.max' => 'API key cannot exceed 255 characters.',
            'supportcontact.max' => 'Support contact cannot exceed 50 characters.',
            'deliverytimeestimate.max' => 'Delivery time estimate cannot exceed 50 characters.',
        ]);
        
        $validated['inactive'] = $request->has('inactive') ? true : false;
        
        $parcelCompany->update($validated);
        
        return redirect()->route('parcel-companies.index')
            ->with('success', 'Parcel company updated successfully.');
    }
    
    /**
     * Remove the specified parcel company
     */
    public function destroy(ParcelCompany $parcelCompany)
    {
        // Check if company has parcels
        if ($parcelCompany->parcels()->count() > 0) {
            return redirect()->route('parcel-companies.index')
                ->with('error', 'Cannot delete parcel company that has associated parcels.');
        }
        
        $parcelCompany->delete();
        
        return redirect()->route('parcel-companies.index')
            ->with('success', 'Parcel company deleted successfully.');
    }
}
