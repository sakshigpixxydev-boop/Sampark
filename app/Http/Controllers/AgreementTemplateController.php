<?php

namespace App\Http\Controllers;

use App\Models\AgreementTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgreementTemplateController extends Controller
{
    public function index()
    {
        if (!Auth::user()->hasPermission(11001)) {
            abort(403);
        }

        $templates = AgreementTemplate::orderBy('title')->get();
        return view('agreement-templates.index', compact('templates'));
    }

    public function create()
    {
        if (!Auth::user()->hasPermission(11002)) {
            abort(403);
        }

        return view('agreement-templates.create');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermission(11002)) {
            abort(403);
        }

        $validated = $request->validate([
            'slug' => 'required|string|unique:agreement_templates,slug|max:255',
            'title' => 'required|string|max:255',
            'content_en' => 'required|string',
            'content_hi' => 'nullable|string',
            'content_gu' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');

        AgreementTemplate::create($validated);

        return redirect()->route('agreement-templates.index')
            ->with('success', 'Agreement template created successfully.');
    }

    public function edit(AgreementTemplate $agreement_template)
    {
        if (!Auth::user()->hasPermission(11002)) {
            abort(403);
        }

        return view('agreement-templates.edit', compact('agreement_template'));
    }

    public function update(Request $request, AgreementTemplate $agreement_template)
    {
        if (!Auth::user()->hasPermission(11002)) {
            abort(403);
        }

        $validated = $request->validate([
            'slug' => 'required|string|max:255|unique:agreement_templates,slug,' . $agreement_template->agreement_template_id . ',agreement_template_id',
            'title' => 'required|string|max:255',
            'content_en' => 'required|string',
            'content_hi' => 'nullable|string',
            'content_gu' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $agreement_template->update($validated);

        return redirect()->route('agreement-templates.index')
            ->with('success', 'Agreement template updated successfully.');
    }

    public function destroy(AgreementTemplate $agreement_template)
    {
        if (!Auth::user()->hasPermission(11003)) {
            abort(403);
        }

        $agreement_template->delete();

        return redirect()->route('agreement-templates.index')
            ->with('success', 'Agreement template deleted successfully.');
    }
}

