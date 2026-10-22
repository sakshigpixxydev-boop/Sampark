@extends('layouts.app')

@section('title', 'Student Lead')

@php
    $guardian = $lead->guardians->first();
    $taluka = $lead->taluka;
    $district = optional($taluka)->district;
    $state = optional($district)->state;

    $languages = ['en' => 'English', 'hi' => 'हिंदी', 'gu' => 'ગુજરાતી'];
@endphp

@section('content')
<div class="py-6">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        @if (session('error'))
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg shadow-sm">
                <span class="text-sm font-medium text-red-800">{{ session('error') }}</span>
            </div>
        @endif

        <div class="mb-6">
            <a href="{{ route('student-leads.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-500 hover:text-gray-900">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Student Leads
            </a>
        </div>

        <x-page-header
            :title="trim($lead->fname . ' ' . $lead->mname . ' ' . $lead->lname)"
            subtitle="Captured while regular registration was closed." />

        <!-- Status -->
        <div class="mb-6 flex flex-wrap items-center gap-3">
            @if($lead->lead_payment_link_sent_at)
                <x-badge variant="success">Payment link sent {{ $lead->lead_payment_link_sent_at->format('d M Y, h:i A') }}</x-badge>
            @else
                <x-badge variant="warning">Awaiting payment link</x-badge>
            @endif
            <x-badge variant="info">{{ $languages[$lead->selected_language] ?? $lead->selected_language }}</x-badge>
            @if($lead->lead_captured_at)
                <span class="text-xs font-semibold text-gray-500">Captured {{ $lead->lead_captured_at->format('d M Y, h:i A') }}</span>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">

                <!-- Student -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider mb-4">Student</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</dt>
                            <dd class="mt-0.5 text-gray-900 font-medium break-all"><x-masked-email :email="$lead->email" /></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Primary Contact</dt>
                            <dd class="mt-0.5 text-gray-900 font-medium"><x-masked-phone :number="$lead->contact_no" /></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">WhatsApp</dt>
                            <dd class="mt-0.5 text-gray-900 font-medium"><x-masked-phone :number="$lead->whatsapp_no" /></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Gender</dt>
                            <dd class="mt-0.5 text-gray-900 font-medium">
                                {{ $lead->gender === 'M' ? 'Male' : ($lead->gender === 'F' ? 'Female' : 'Other') }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Date of Birth</dt>
                            <dd class="mt-0.5 text-gray-900 font-medium">{{ $lead->birthdate ? $lead->birthdate->format('d M Y') : '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Academic -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider mb-4">Academic Details</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Standard</dt>
                            <dd class="mt-0.5 text-gray-900 font-medium">{{ optional($lead->standard)->stdname ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Stream</dt>
                            <dd class="mt-0.5 text-gray-900 font-medium">{{ $lead->stream ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Board</dt>
                            <dd class="mt-0.5 text-gray-900 font-medium">
                                {{ optional($lead->educationalBoard)->boardname ?? $lead->other_eduboard_name ?? '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">School / Institute</dt>
                            <dd class="mt-0.5 text-gray-900 font-medium">{{ $lead->school_name ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Guardian -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider mb-4">Guardian</h3>
                    @if($guardian)
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                            <div>
                                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</dt>
                                <dd class="mt-0.5 text-gray-900 font-medium">{{ trim($guardian->fname . ' ' . $guardian->mname . ' ' . $guardian->lname) }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Contact</dt>
                                <dd class="mt-0.5 text-gray-900 font-medium"><x-masked-phone :number="$guardian->phone" /></dd>
                            </div>
                        </dl>
                    @else
                        <p class="text-sm text-gray-500">No guardian details recorded.</p>
                    @endif
                </div>

                <!-- Location -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider mb-4">Location</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">City / Village</dt>
                            <dd class="mt-0.5 text-gray-900 font-medium">{{ $lead->city_village ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Taluka</dt>
                            <dd class="mt-0.5 text-gray-900 font-medium">{{ optional($taluka)->tname ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">District</dt>
                            <dd class="mt-0.5 text-gray-900 font-medium">{{ optional($district)->dname ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">State</dt>
                            <dd class="mt-0.5 text-gray-900 font-medium">{{ optional($state)->sname ?? '—' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Address</dt>
                            <dd class="mt-0.5 text-gray-900 font-medium">{{ $lead->address ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pincode</dt>
                            <dd class="mt-0.5 text-gray-900 font-medium">{{ $lead->pincode ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Actions -->
            <div class="space-y-4">
                @canAccess(11703)
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider mb-3">Send Payment Link</h3>
                    <p class="text-xs text-gray-600 leading-relaxed mb-4">
                        Converts this lead into a pending student and emails them a link to choose a plan and pay.
                        They will not need to fill the form again.
                    </p>

                    <form method="POST" action="{{ route('student-leads.send-payment-link', $lead->student_id) }}"
                          onsubmit="return confirm('Send the payment link to {{ $lead->email }}?');">
                        @csrf
                        <button type="submit"
                                class="w-full px-4 py-2.5 bg-[#00bde0] hover:bg-[#0099b8] text-white rounded-xl font-bold uppercase tracking-wider text-xs transition-colors">
                            Send Payment Link
                        </button>
                    </form>

                    <form method="POST" action="{{ route('student-leads.destroy', $lead->student_id) }}" class="mt-3"
                          onsubmit="return confirm('Remove this lead? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="w-full px-4 py-2.5 bg-white border border-red-200 hover:bg-red-50 text-red-600 rounded-xl font-bold uppercase tracking-wider text-xs transition-colors">
                            Delete Lead
                        </button>
                    </form>
                </div>
                @endcanAccess

                <div class="bg-gray-50 rounded-2xl border border-gray-200 p-5">
                    <h4 class="text-xs font-black text-gray-500 uppercase tracking-wider mb-2">Source</h4>
                    <p class="text-sm font-semibold text-gray-800">{{ $lead->lead_source ?? 'registration_form' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
