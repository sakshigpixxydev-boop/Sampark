@extends('layouts.app')

@section('title', 'Registration Control')

@section('content')
<div class="py-6" x-data="{ open: {{ $registrationOpen ? 'true' : 'false' }} }">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        @if (session('success'))
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-r-lg flex items-center shadow-sm">
                <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm font-medium text-green-800">{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg shadow-sm">
                <ul class="text-sm font-medium text-red-800 list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <x-page-header
            title="Registration Control"
            subtitle="Open or close the public student registration form. This does not affect school registration links — those keep using each school's own start and end dates." />

        @unless($registrationOpen)
            <div class="mb-6 p-4 bg-amber-50 border-l-4 border-amber-500 rounded-r-lg shadow-sm">
                <p class="text-sm font-semibold text-amber-900">Regular registration is currently CLOSED.</p>
                <p class="mt-1 text-sm text-amber-800">
                    Visitors can still fill the form; their details are saved as Student Leads and they are told
                    the current semester has already started.
                </p>
            </div>
        @endunless

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Settings form -->
            <div class="lg:col-span-2">
                <form method="POST" action="{{ route('settings.registration.update') }}"
                      class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <h3 class="text-base font-bold text-gray-900 mb-3">Public registration form</h3>

                        <div class="space-y-3">
                            <label class="flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition-colors"
                                   :class="open ? 'border-primary-500 bg-primary-50' : 'border-gray-200 hover:bg-gray-50'">
                                <input type="radio" name="registration_open" value="1"
                                       @change="open = true" {{ $registrationOpen ? 'checked' : '' }}
                                       class="mt-1 text-primary-500 focus:ring-primary-500">
                                <span>
                                    <span class="block text-sm font-bold text-gray-900">Open</span>
                                    <span class="block text-xs text-gray-600 mt-0.5">
                                        Students register normally: OTP verification, plan selection and payment.
                                    </span>
                                </span>
                            </label>

                            <label class="flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition-colors"
                                   :class="!open ? 'border-amber-500 bg-amber-50' : 'border-gray-200 hover:bg-gray-50'">
                                <input type="radio" name="registration_open" value="0"
                                       @change="open = false" {{ !$registrationOpen ? 'checked' : '' }}
                                       class="mt-1 text-amber-500 focus:ring-amber-500">
                                <span>
                                    <span class="block text-sm font-bold text-gray-900">Closed &mdash; capture leads</span>
                                    <span class="block text-xs text-gray-600 mt-0.5">
                                        The form still collects details, but saves them as Student Leads and shows the
                                        &ldquo;semester already started&rdquo; message. No OTP, plan or payment.
                                    </span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-6">
                        <h3 class="text-base font-bold text-gray-900">Next batch note <span class="font-normal text-gray-500 text-sm">(optional)</span></h3>
                        <p class="text-xs text-gray-600 mt-1 mb-4">
                            Shown to leads on the confirmation page and included in the payment-link email.
                            For example: &ldquo;The next batch is expected to open in June 2026.&rdquo;
                            Leave a language blank to fall back to English.
                        </p>

                        <div class="space-y-4">
                            @foreach (['en' => 'English', 'hi' => 'हिंदी (Hindi)', 'gu' => 'ગુજરાતી (Gujarati)'] as $code => $label)
                                <div>
                                    <label for="next_batch_note_{{ $code }}" class="block text-sm font-semibold text-gray-700 mb-1.5">{{ $label }}</label>
                                    <textarea id="next_batch_note_{{ $code }}" name="next_batch_note_{{ $code }}" rows="2" maxlength="1000"
                                              class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm"
                                              placeholder="Optional">{{ old('next_batch_note_' . $code, $notes[$code] ?? '') }}</textarea>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-6 flex justify-end">
                        <button type="submit"
                                class="px-6 py-2.5 bg-primary-500 hover:bg-primary-600 text-white rounded-xl font-bold uppercase tracking-wider text-xs transition-colors">
                            Save Settings
                        </button>
                    </div>
                </form>
            </div>

            <!-- Captured leads summary -->
            <div class="space-y-4">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4">Captured Student Leads</h3>

                    <div class="space-y-4">
                        <div>
                            <p class="text-3xl font-black text-gray-900">{{ number_format($leadCount) }}</p>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total leads</p>
                        </div>
                        <div>
                            <p class="text-3xl font-black text-amber-600">{{ number_format($pendingLinkCount) }}</p>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Awaiting payment link</p>
                        </div>
                    </div>

                    @canAccess(11701)
                        <a href="{{ route('student-leads.index') }}"
                           class="mt-6 block w-full text-center px-4 py-2.5 bg-gray-900 hover:bg-gray-800 text-white rounded-xl font-bold uppercase tracking-wider text-xs transition-colors">
                            View Student Leads
                        </a>
                    @endcanAccess
                </div>

                <div class="bg-sky-50 rounded-2xl border border-sky-100 p-5">
                    <h4 class="text-xs font-black text-sky-900 uppercase tracking-wider mb-2">When you reopen</h4>
                    <p class="text-xs font-medium text-sky-800 leading-relaxed">
                        Send the captured leads a payment link from the Student Leads screen. They resume at plan
                        selection and pay without filling the form again.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
