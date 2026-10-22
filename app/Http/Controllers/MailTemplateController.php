<?php

namespace App\Http\Controllers;

use App\Models\MailTemplate;
use Illuminate\Http\Request;

class MailTemplateController extends Controller
{
    public function index()
    {
        $templates = MailTemplate::orderBy('title_en')->get();
        return view('mail-templates.index', compact('templates'));
    }

    public function create()
    {
        return view('mail-templates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'identifier' => 'required|string|unique:mail_templates,identifier|max:255',
            'title_en' => 'required|string|max:255',
            'title_hi' => 'nullable|string|max:255',
            'title_gu' => 'nullable|string|max:255',
            'content_en' => 'required|string',
            'content_hi' => 'nullable|string',
            'content_gu' => 'nullable|string',
        ]);

        MailTemplate::create($validated);

        return redirect()->route('mail-templates.index')
            ->with('success', 'Mail template created successfully.');
    }

    public function edit(MailTemplate $mail_template)
    {
        return view('mail-templates.edit', compact('mail_template'));
    }

    public function update(Request $request, MailTemplate $mail_template)
    {
        $validated = $request->validate([
            'identifier' => 'required|string|max:255|unique:mail_templates,identifier,' . $mail_template->mail_template_id . ',mail_template_id',
            'title_en' => 'required|string|max:255',
            'title_hi' => 'nullable|string|max:255',
            'title_gu' => 'nullable|string|max:255',
            'content_en' => 'required|string',
            'content_hi' => 'nullable|string',
            'content_gu' => 'nullable|string',
        ]);

        $mail_template->update($validated);

        return redirect()->route('mail-templates.index')
            ->with('success', 'Mail template updated successfully.');
    }

    public function destroy(MailTemplate $mail_template)
    {
        $mail_template->delete();

        return redirect()->route('mail-templates.index')
            ->with('success', 'Mail template deleted successfully.');
    }
}
