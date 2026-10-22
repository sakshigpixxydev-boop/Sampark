@extends('layouts.app')

@section('content')
@php
use Illuminate\Support\Facades\Storage;
@endphp
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Schools</h1>
            <p class="text-sm text-gray-600 mt-1">Manage school information</p>
        </div>
        <div class="flex space-x-3">
            @canAccess(10032)
            <a href="{{ route('students.bulk-upload.physical-form') }}"
                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#00bde0] focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Download Form
            </a>
            <a href="{{ route('schools.create') }}" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition text-sm">
                Add New School
            </a>
            @endcanAccess
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
            {{ session('error') }}
        </div>
    @endif

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <form method="GET" action="{{ route('schools.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="School name, admin, email..." 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                </div>

                <!-- School Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">School Type</label>
                    <select name="schooltype_id" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                        <option value="">All Types</option>
                        @foreach($schoolTypes as $type)
                            <option value="{{ $type->schooltype_id }}" {{ request('schooltype_id') == $type->schooltype_id ? 'selected' : '' }}>
                                {{ $type->schooltypename }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Educational Board Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Educational Board</label>
                    <select name="eduboard_id" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                        <option value="">All Boards</option>
                        @foreach($educationalBoards as $board)
                            <option value="{{ $board->eduboard_id }}" {{ request('eduboard_id') == $board->eduboard_id ? 'selected' : '' }}>
                                {{ $board->boardname }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                        <option value="">All Status</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Lead</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Final</option>
                    </select>
                </div>

                <!-- Inactive Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Active Status</label>
                    <select name="inactive" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                        <option value="">All</option>
                        <option value="0" {{ request('inactive') === '0' ? 'selected' : '' }}>Active</option>
                        <option value="1" {{ request('inactive') === '1' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition">
                    Apply Filters
                </button>
                <a href="{{ route('schools.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                    Clear Filters
                </a>
            </div>
        </form>
    </div>

    <!-- Schools Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            School Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Type / Board
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Location
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Admin Contact
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($schools as $school)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    @if($school->logourl)
                                        <img src="{{ Storage::url($school->logourl) }}" alt="{{ $school->schoolname }}" class="h-10 w-10 rounded-full object-cover mr-3">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                                            <span class="text-gray-500 text-sm font-medium">{{ substr($school->schoolname, 0, 2) }}</span>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $school->schoolname }}
                                        </div>
                                        @if($school->email)
                                            <div class="text-sm text-gray-500">{{ $school->email }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $school->schoolType->schooltypename ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-500">{{ $school->educationalBoard->boardname ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $school->city_village ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-500">
                                    {{ $school->taluka->tname ?? 'N/A' }}, 
                                    {{ $school->taluka->district->state->sname ?? 'N/A' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $school->adminname }}</div>
                                <div class="text-sm text-gray-500 flex items-center gap-2 group mt-1">
                                    @if($school->admincontact)
                                        <x-phone-link :number="$school->admincontact" />
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col gap-1">
                                    @if($school->status == 1)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Final
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Lead
                                        </span>
                                    @endif
                                    @if($school->inactive)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Inactive
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('schools.agreement.show', $school->school_id) }}" class="text-indigo-600 hover:text-indigo-900">
                                        Agreement
                                    </a>
                                    @canAccess(10032)
                                    <a href="{{ route('schools.edit', $school->school_id) }}" class="text-[#00bde0] hover:text-[#0099b8]">
                                        Edit
                                    </a>
                                    @endcanAccess
                                    @if($school->status == 0)
                                        @canAccess(10032)
                                            @if(!Auth::user()->hasRole('School'))
                                                <a href="{{ route('schools.approve-form', $school->school_id) }}"
                                                   class="inline-flex items-center px-3 py-1.5 bg-[#00bde0] hover:bg-[#0099b8] text-white rounded-md transition">
                                                    Approve
                                                </a>
                                            @endif
                                        @endcanAccess
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                No schools found. <a href="{{ route('schools.create') }}" class="text-[#00bde0] hover:text-[#0099b8]">Create one now</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($schools->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $schools->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
