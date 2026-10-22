@extends('layouts.app')

@section('title', 'Enquiry')

@php
    $badgeVariant = [
        'New' => 'info',
        'Contacted' => 'warning',
        'Converted' => 'success',
        'Closed' => 'default',
    ];
    $languages = ['en' => 'English', 'hi' => 'हिंदी', 'gu' => 'ગુજરાતી'];
@endphp

@section('content')
<div class="py-6" x-data="{ showPayload: false }">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        @if (session('success'))
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-r-lg shadow-sm">
                <span class="text-sm font-medium text-green-800">{{ session('success') }}</span>
            </div>
        @endif

        <div class="mb-6">
            <a href="{{ route('enquiries.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-500 hover:text-gray-900">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Enquiries
            </a>
        </div>

        <x-page-header
            :title="$enquiry->full_name"
            :subtitle="'Enquiry #' . $enquiry->enquiry_id . ' · received ' . ($enquiry->created_at ? $enquiry->created_at->format('d M Y, h:i A') : '')" />

        <div class="mb-6 flex flex-wrap items-center gap-3">
            <x-badge :variant="$badgeVariant[$enquiry->status] ?? 'default'">{{ $enquiry->status }}</x-badge>
            <x-badge variant="info">{{ $languages[$enquiry->selected_language] ?? $enquiry->selected_language }}</x-badge>
            <span class="text-xs font-semibold text-gray-500">Source: {{ $enquiry->source }}</span>
            @if($enquiry->contacted_at)
                <span class="text-xs font-semibold text-gray-500">Contacted {{ $enquiry->contacted_at->format('d M Y') }}</span>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">

                <!-- Contact -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider mb-4">Contact</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</dt>
                            <dd class="mt-0.5 text-gray-900 font-medium break-all">
                                @if($enquiry->email)<x-masked-email :email="$enquiry->email" />@else — @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Phone</dt>
                            <dd class="mt-0.5 text-gray-900 font-medium">
                                @if($enquiry->phone)<x-masked-phone :number="$enquiry->phone" />@else — @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">WhatsApp</dt>
                            <dd class="mt-0.5 text-gray-900 font-medium">
                                @if($enquiry->whatsapp_no)<x-masked-phone :number="$enquiry->whatsapp_no" />@else — @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Gender</dt>
                            <dd class="mt-0.5 text-gray-900 font-medium">
                                {{ $enquiry->gender === 'M' ? 'Male' : ($enquiry->gender === 'F' ? 'Female' : ($enquiry->gender ? 'Other' : '—')) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Date of Birth</dt>
                            <dd class="mt-0.5 text-gray-900 font-medium">{{ $enquiry->birthdate ? $enquiry->birthdate->format('d M Y') : '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Academic -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider mb-4">Academic Details</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Standard</dt>
                            <dd class="mt-0.5 text-gray-900 font-medium">{{ optional($enquiry->standard)->stdname ?? $enquiry->standard_name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Stream</dt>
                            <dd class="mt-0.5 text-gray-900 font-medium">{{ $enquiry->stream ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Board</dt>
                            <dd class="mt-0.5 text-gray-900 font-medium">{{ optional($enquiry->educationalBoard)->boardname ?? $enquiry->board_name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Medium</dt>
                            <dd class="mt-0.5 text-gray-900 font-medium">{{ $enquiry->medium ?? '—' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">School / Institute</dt>
                            <dd class="mt-0.5 text-gray-900 font-medium">{{ $enquiry->school_name ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Guardian -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider mb-4">Guardian</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</dt>
                            <dd class="mt-0.5 text-gray-900 font-medium">{{ $enquiry->guardian_name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Contact</dt>
                            <dd class="mt-0.5 text-gray-900 font-medium">
                                @if($enquiry->guardian_contact)<x-masked-phone :number="$enquiry->guardian_contact" />@else — @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Relation</dt>
                            <dd class="mt-0.5 text-gray-900 font-medium">{{ $enquiry->guardian_relation ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Location -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider mb-4">Location</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">City / Village</dt>
                            <dd class="mt-0.5 text-gray-900 font-medium">{{ $enquiry->city_village ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Taluka</dt>
                            <dd class="mt-0.5 text-gray-900 font-medium">{{ $enquiry->taluka_name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">District</dt>
                            <dd class="mt-0.5 text-gray-900 font-medium">{{ $enquiry->district_name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">State</dt>
                            <dd class="mt-0.5 text-gray-900 font-medium">{{ $enquiry->state_name ?? '—' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Address</dt>
                            <dd class="mt-0.5 text-gray-900 font-medium">{{ $enquiry->address ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pincode</dt>
                            <dd class="mt-0.5 text-gray-900 font-medium">{{ $enquiry->pincode ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Message -->
                @if($enquiry->message)
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                        <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider mb-3">Message</h3>
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $enquiry->message }}</p>
                    </div>
                @endif

                <!-- Raw payload (debugging what the external site sent) -->
                @if($enquiry->raw_payload)
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                        <button type="button" @click="showPayload = !showPayload"
                                class="flex items-center justify-between w-full text-left">
                            <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider">Raw Payload</h3>
                            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': showPayload }"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <pre x-show="showPayload" x-cloak
                             class="mt-4 p-4 bg-gray-900 text-gray-100 rounded-xl text-xs overflow-x-auto">{{ json_encode($enquiry->raw_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                @endif
            </div>

            <!-- Workflow -->
            <div class="space-y-4">
                @canAccess(11706)
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider mb-4">Update</h3>

                    <form method="POST" action="{{ route('enquiries.update-status', $enquiry->enquiry_id) }}" class="space-y-4">
                        @csrf

                        <div>
                            <label for="status" class="block text-sm font-semibold text-gray-700 mb-1.5">Status</label>
                            <select name="status" id="status" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] text-sm">
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}" {{ $enquiry->status === $status ? 'selected' : '' }}>{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="assigned_to" class="block text-sm font-semibold text-gray-700 mb-1.5">Assign to</label>
                            <select name="assigned_to" id="assigned_to" class="searchable-select block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] text-sm">
                                <option value="">Unassigned</option>
                                @foreach($assignees as $assignee)
                                    <option value="{{ $assignee->sysuser_id }}" {{ $enquiry->assigned_to == $assignee->sysuser_id ? 'selected' : '' }}>
                                        {{ trim($assignee->fname . ' ' . $assignee->lname) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="remarks" class="block text-sm font-semibold text-gray-700 mb-1.5">Remarks</label>
                            <textarea name="remarks" id="remarks" rows="4" maxlength="2000"
                                      class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] text-sm">{{ old('remarks', $enquiry->remarks) }}</textarea>
                        </div>

                        <button type="submit"
                                class="w-full px-4 py-2.5 bg-[#00bde0] hover:bg-[#0099b8] text-white rounded-xl font-bold uppercase tracking-wider text-xs transition-colors">
                            Save
                        </button>
                    </form>

                    <form method="POST" action="{{ route('enquiries.destroy', $enquiry->enquiry_id) }}" class="mt-3"
                          onsubmit="return confirm('Remove this enquiry?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="w-full px-4 py-2.5 bg-white border border-red-200 hover:bg-red-50 text-red-600 rounded-xl font-bold uppercase tracking-wider text-xs transition-colors">
                            Delete Enquiry
                        </button>
                    </form>
                </div>
                @endcanAccess

                <div class="bg-gray-50 rounded-2xl border border-gray-200 p-5 space-y-3 text-xs">
                    <div>
                        <p class="font-black text-gray-500 uppercase tracking-wider">IP Address</p>
                        <p class="mt-0.5 font-semibold text-gray-800">{{ $enquiry->ip_address ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="font-black text-gray-500 uppercase tracking-wider">User Agent</p>
                        <p class="mt-0.5 font-medium text-gray-700 break-all">{{ $enquiry->user_agent ?? '—' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
