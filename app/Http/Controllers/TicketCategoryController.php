<?php

namespace App\Http\Controllers;

use App\Models\TicketCategory;
use Illuminate\Http\Request;

class TicketCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Admin only for category management
        if (!auth()->user()->hasRole('Admin')) {
            abort(403);
        }

        $categories = TicketCategory::withCount('tickets')->get();
        return view('tickets.categories.index', compact('categories'));
    }

    public function create()
    {
        if (!auth()->user()->hasRole('Admin')) {
            abort(403);
        }

        return view('tickets.categories.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasRole('Admin')) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:ticket_categories',
            'emails' => 'required|array|min:1',
            'emails.*' => 'required|email'
        ]);

        TicketCategory::create($validated);

        return redirect()->route('ticket-categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(TicketCategory $ticketCategory)
    {
        if (!auth()->user()->hasRole('Admin')) {
            abort(403);
        }

        return view('tickets.categories.edit', compact('ticketCategory'));
    }

    public function update(Request $request, TicketCategory $ticketCategory)
    {
        if (!auth()->user()->hasRole('Admin')) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:ticket_categories,name,' . $ticketCategory->id,
            'emails' => 'required|array|min:1',
            'emails.*' => 'required|email'
        ]);

        $ticketCategory->update($validated);

        return redirect()->route('ticket-categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(TicketCategory $ticketCategory)
    {
        if (!auth()->user()->hasRole('Admin')) {
            abort(403);
        }

        if ($ticketCategory->tickets()->exists()) {
            return back()->with('error', 'Cannot delete category with associated tickets.');
        }

        $ticketCategory->delete();

        return redirect()->route('ticket-categories.index')->with('success', 'Category deleted successfully.');
    }
}
