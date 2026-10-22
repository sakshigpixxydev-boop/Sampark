@extends('layouts.app')

@section('title', 'Student Leads')

@section('content')
<div class="py-6" x-data="{ selected: [], confirmBulk: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        @if (session('success'))
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-r-lg flex items-center shadow-sm">
                <svg class="w-5 h-5 text-green-500 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm font-medium text-green-800">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg flex items-center shadow-sm">
                <svg class="w-5 h-5 text-red-500 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm font-medium text-red-800">{{ session('error') }}</span>
            </div>
        @endif

        <x-page-header
            title="Student Leads"
            subtitle="People who filled the registration form while registration was closed. Send them a payment link and they resume at plan selection — no re-registration.">
            <x-slot:actions>
                <a href="{{ route('student-leads.export', request()->query()) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Export CSV
                </a>
            </x-slot:actions>
        </x-page-header>

        @unless($registrationOpen)
            <div class="mb-6 p-4 bg-amber-50 border-l-4 border-amber-500 rounded-r-lg shadow-sm">
                <p class="text-sm font-semibold text-amber-900">Registration is currently closed, so new leads are still arriving.</p>
                <p class="mt-1 text-sm text-amber-800">
                    Reopen registration from
                    @canAccess(11707)
                        <a href="{{ route('settings.registration.edit') }}" class="underline font-bold">Registration Control</a>
                    @else
                        Registration Control
                    @endcanAccess
                    before sending payment links, otherwise leads will land on a closed form.
                </p>
            </div>
        @endunless

        <!-- Summary -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <p class="text-2xl font-black text-gray-900">{{ number_format($totalLeads) }}</p>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mt-1">Total leads</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <p class="text-2xl font-black text-amber-600">{{ number_format($pendingCount) }}</p>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mt-1">Awaiting payment link</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <p class="text-2xl font-black text-green-600">{{ number_format($linkSentCount) }}</p>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mt-1">Link sent</p>
            </div>
        </div>

        <!-- Filters -->
        <x-filter-panel action="{{ route('student-leads.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           placeholder="Name, email, phone or school"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] text-sm">
                </div>

                <x-multi-select
                    name="std_id"
                    label="Standard"
                    :options="$standards->map(fn($s) => ['id' => $s->std_id, 'name' => $s->stdname])->values()->toArray()"
                    :selected="(array) request('std_id', [])"
                    placeholder="All standards" />

                <x-multi-select
                    name="eduboard_id"
                    label="Board"
                    :options="$boards->map(fn($b) => ['id' => $b->eduboard_id, 'name' => $b->boardname])->values()->toArray()"
                    :selected="(array) request('eduboard_id', [])"
                    placeholder="All boards" />

                <div>
                    <label for="state_id" class="block text-sm font-medium text-gray-700 mb-1">State</label>
                    <select name="state_id" id="state_id" class="searchable-select block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] text-sm">
                        <option value="">All states</option>
                        @foreach($states as $state)
                            <option value="{{ $state->state_id }}" {{ request('state_id') == $state->state_id ? 'selected' : '' }}>{{ $state->sname }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="city_village" class="block text-sm font-medium text-gray-700 mb-1">City / Village</label>
                    <input type="text" name="city_village" id="city_village" value="{{ request('city_village') }}"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] text-sm">
                </div>

                <div>
                    <label for="selected_language" class="block text-sm font-medium text-gray-700 mb-1">Language</label>
                    <select name="selected_language" id="selected_language" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] text-sm">
                        <option value="">All languages</option>
                        <option value="en" {{ request('selected_language') === 'en' ? 'selected' : '' }}>English</option>
                        <option value="hi" {{ request('selected_language') === 'hi' ? 'selected' : '' }}>हिंदी</option>
                        <option value="gu" {{ request('selected_language') === 'gu' ? 'selected' : '' }}>ગુજરાતી</option>
                    </select>
                </div>

                <div>
                    <label for="link_sent" class="block text-sm font-medium text-gray-700 mb-1">Payment link</label>
                    <select name="link_sent" id="link_sent" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] text-sm">
                        <option value="">All</option>
                        <option value="no" {{ request('link_sent') === 'no' ? 'selected' : '' }}>Not sent</option>
                        <option value="yes" {{ request('link_sent') === 'yes' ? 'selected' : '' }}>Sent</option>
                    </select>
                </div>

                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Captured from</label>
                    <input type="text" name="start_date" id="start_date" value="{{ request('start_date') }}" placeholder="DD/MM/YYYY"
                           class="date-picker block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] text-sm">
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Captured to</label>
                    <input type="text" name="end_date" id="end_date" value="{{ request('end_date') }}" placeholder="DD/MM/YYYY"
                           class="date-picker block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] text-sm">
                </div>
            </div>
        </x-filter-panel>

        <!-- Bulk action bar -->
        @canAccess(11703)
        <div x-show="selected.length > 0" x-cloak
             class="sticky top-2 z-20 mb-4 flex items-center justify-between gap-4 bg-gray-900 text-white rounded-xl px-5 py-3 shadow-xl">
            <p class="text-sm font-semibold">
                <span x-text="selected.length"></span> lead(s) selected
            </p>
            <div class="flex items-center gap-3">
                <button type="button" @click="selected = []" class="text-xs font-semibold text-gray-300 hover:text-white">Clear</button>
                <button type="button" @click="confirmBulk = true"
                        class="px-4 py-2 bg-[#00bde0] hover:bg-[#0099b8] rounded-lg text-xs font-bold uppercase tracking-wider transition-colors">
                    Send Payment Link
                </button>
            </div>
        </div>

        <!-- Confirmation modal -->
        <div x-show="confirmBulk" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="confirmBulk = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
                <h3 class="text-lg font-black text-gray-900">Send payment link?</h3>
                <p class="mt-2 text-sm text-gray-600">
                    <span x-text="selected.length"></span> lead(s) will be converted into pending students and emailed a
                    link to choose a plan and pay. This cannot be undone.
                </p>
                <form method="POST" action="{{ route('student-leads.bulk-send-payment-link') }}" class="mt-6 flex justify-end gap-3">
                    @csrf
                    <template x-for="id in selected" :key="id">
                        <input type="hidden" name="lead_ids[]" :value="id">
                    </template>
                    <button type="button" @click="confirmBulk = false"
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg text-xs font-bold uppercase tracking-wider">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-[#00bde0] hover:bg-[#0099b8] text-white rounded-lg text-xs font-bold uppercase tracking-wider">
                        Send
                    </button>
                </form>
            </div>
        </div>
        @endcanAccess

        <!-- Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <x-responsive-table-wrapper>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            @canAccess(11703)
                            <th class="px-4 py-4 w-10">
                                <input type="checkbox"
                                       @change="selected = $event.target.checked ? {{ $leads->pluck('student_id')->toJson() }} : []"
                                       class="rounded border-gray-300 text-[#00bde0] focus:ring-[#00bde0]">
                            </th>
                            @endcanAccess
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Contact</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Academic</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Location</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Lang</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Captured</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Link</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($leads as $lead)
                            <tr class="hover:bg-gray-50">
                                @canAccess(11703)
                                <td class="px-4 py-4">
                                    <input type="checkbox" value="{{ $lead->student_id }}" x-model.number="selected"
                                           class="rounded border-gray-300 text-[#00bde0] focus:ring-[#00bde0]">
                                </td>
                                @endcanAccess
                                <td class="px-6 py-4">
                                    <a href="{{ route('student-leads.show', $lead->student_id) }}" class="text-sm font-semibold text-gray-900 hover:text-[#00bde0]">
                                        {{ trim($lead->fname . ' ' . $lead->mname . ' ' . $lead->lname) }}
                                    </a>
                                    @if($lead->school_name)
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $lead->school_name }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <x-masked-email :email="$lead->email" />
                                    <p class="text-xs mt-0.5"><x-masked-phone :number="$lead->contact_no" /></p>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ optional($lead->standard)->stdname ?? '—' }}
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ optional($lead->educationalBoard)->boardname ?? $lead->other_eduboard_name ?? '—' }}
                                    </p>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $lead->city_village ?? '—' }}
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ optional(optional($lead->taluka)->district)->dname ?? '' }}
                                    </p>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 uppercase">{{ $lead->selected_language }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $lead->lead_captured_at ? $lead->lead_captured_at->format('d M Y') : '—' }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($lead->lead_payment_link_sent_at)
                                        <x-badge variant="success">Sent</x-badge>
                                    @else
                                        <x-badge>Not sent</x-badge>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @canAccess(11703)
                                    <form method="POST" action="{{ route('student-leads.send-payment-link', $lead->student_id) }}" class="inline"
                                          onsubmit="return confirm('Send the payment link to {{ $lead->email }}? They will be converted into a pending student.');">
                                        @csrf
                                        <button type="submit" class="text-xs font-bold text-[#00bde0] hover:text-[#0099b8] uppercase tracking-wider">
                                            Send Link
                                        </button>
                                    </form>
                                    @endcanAccess
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center">
                                    <p class="text-sm font-semibold text-gray-500">No student leads found.</p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        Leads are captured automatically while regular registration is closed.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-responsive-table-wrapper>

            @if($leads->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $leads->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
