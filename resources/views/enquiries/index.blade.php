@extends('layouts.app')

@section('title', 'Enquiries')

@php
    $badgeVariant = [
        'New' => 'info',
        'Contacted' => 'warning',
        'Converted' => 'success',
        'Closed' => 'default',
    ];
@endphp

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        @if (session('success'))
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-r-lg flex items-center shadow-sm">
                <svg class="w-5 h-5 text-green-500 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm font-medium text-green-800">{{ session('success') }}</span>
            </div>
        @endif

        <x-page-header
            title="Enquiry"
            subtitle="Enquiries submitted through the external website.">
            <x-slot:actions>
                <a href="{{ route('enquiries.export', request()->query()) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Export CSV
                </a>
            </x-slot:actions>
        </x-page-header>

        <!-- Status tabs -->
        <div class="flex flex-wrap gap-2 mb-6">
            <a href="{{ route('enquiries.index', array_merge(request()->except(['status', 'page']))) }}"
               class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors {{ !request('status') ? 'bg-[#00bde0] text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                All <span class="ml-1 opacity-70">{{ number_format($totalCount) }}</span>
            </a>
            @foreach($statuses as $status)
                <a href="{{ route('enquiries.index', array_merge(request()->except(['status', 'page']), ['status' => $status])) }}"
                   class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors {{ request('status') === $status ? 'bg-[#00bde0] text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                    {{ $status }} <span class="ml-1 opacity-70">{{ number_format($statusCounts[$status] ?? 0) }}</span>
                </a>
            @endforeach
        </div>

        <!-- Filters -->
        <x-filter-panel action="{{ route('enquiries.index') }}">
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
                    <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-1">Assigned To</label>
                    <select name="assigned_to" id="assigned_to" class="searchable-select block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] text-sm">
                        <option value="">Anyone</option>
                        @foreach($assignees as $assignee)
                            <option value="{{ $assignee->sysuser_id }}" {{ request('assigned_to') == $assignee->sysuser_id ? 'selected' : '' }}>
                                {{ trim($assignee->fname . ' ' . $assignee->lname) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="city_village" class="block text-sm font-medium text-gray-700 mb-1">City / Village</label>
                    <input type="text" name="city_village" id="city_village" value="{{ request('city_village') }}"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] text-sm">
                </div>

                <div>
                    <label for="source" class="block text-sm font-medium text-gray-700 mb-1">Source</label>
                    <input type="text" name="source" id="source" value="{{ request('source') }}" placeholder="e.g. external_api"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] text-sm">
                </div>

                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Received from</label>
                    <input type="text" name="start_date" id="start_date" value="{{ request('start_date') }}" placeholder="DD/MM/YYYY"
                           class="date-picker block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] text-sm">
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Received to</label>
                    <input type="text" name="end_date" id="end_date" value="{{ request('end_date') }}" placeholder="DD/MM/YYYY"
                           class="date-picker block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] text-sm">
                </div>
            </div>
        </x-filter-panel>

        <!-- Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <x-responsive-table-wrapper>
                <x-table :headers="['Name', 'Contact', 'Academic', 'Location', 'Source', 'Status', 'Assigned To', 'Received', '']">
                    @forelse($enquiries as $enquiry)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <a href="{{ route('enquiries.show', $enquiry->enquiry_id) }}" class="text-sm font-semibold text-gray-900 hover:text-[#00bde0]">
                                    {{ $enquiry->full_name }}
                                </a>
                                @if($enquiry->school_name)
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $enquiry->school_name }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                @if($enquiry->email)<x-masked-email :email="$enquiry->email" />@endif
                                @if($enquiry->phone)
                                    <p class="text-xs mt-0.5"><x-masked-phone :number="$enquiry->phone" /></p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ optional($enquiry->standard)->stdname ?? $enquiry->standard_name ?? '—' }}
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ optional($enquiry->educationalBoard)->boardname ?? $enquiry->board_name ?? '—' }}
                                </p>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $enquiry->city_village ?? '—' }}
                                <p class="text-xs text-gray-500 mt-0.5">{{ $enquiry->district_name ?? '' }}</p>
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-500">{{ $enquiry->source }}</td>
                            <td class="px-6 py-4">
                                <x-badge :variant="$badgeVariant[$enquiry->status] ?? 'default'">{{ $enquiry->status }}</x-badge>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $enquiry->assignee ? trim($enquiry->assignee->fname . ' ' . $enquiry->assignee->lname) : '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $enquiry->created_at ? $enquiry->created_at->format('d M Y') : '—' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('enquiries.show', $enquiry->enquiry_id) }}"
                                   class="text-xs font-bold text-[#00bde0] hover:text-[#0099b8] uppercase tracking-wider">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <p class="text-sm font-semibold text-gray-500">No enquiries found.</p>
                                <p class="text-xs text-gray-400 mt-1">Enquiries arrive from the external website via the intake API.</p>
                            </td>
                        </tr>
                    @endforelse
                </x-table>
            </x-responsive-table-wrapper>

            @if($enquiries->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $enquiries->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
