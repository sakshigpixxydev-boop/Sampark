@extends('layouts.app')

@section('content')
@php
use Illuminate\Support\Facades\Storage;
@endphp
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Events</h1>
            <p class="text-sm text-gray-600 mt-1">Manage school events and activities</p>
        </div>
        <div class="flex gap-3">
            @if(Auth::user()->hasPermission(10223))
                <a href="{{ route('students.announcements.templates.index') }}"
                    class="bg-primary-500 hover:bg-primary-600 text-white font-medium px-4 py-2 rounded-lg transition">
                    Manage Templates
                </a>
            @endif
            @if(auth()->user()->hasPermission(10402))
            <a href="{{ route('events.create') }}" class="bg-primary-500 hover:bg-primary-600 text-white font-medium px-4 py-2 rounded-lg transition shadow-sm">
                Add New Event
            </a>
            @endif
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
            {{ session('success') }}
        </div>
    @endif

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <form method="GET" action="{{ route('events.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Title..." 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>

                <!-- School Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">School</label>
                    <select name="school_id" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="">All Schools</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->school_id }}" {{ request('school_id') == $school->school_id ? 'selected' : '' }}>
                                {{ $school->schoolname }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- State Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                    <select name="state_id" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="">All States</option>
                        @foreach($states as $state)
                            <option value="{{ $state->state_id }}" {{ request('state_id') == $state->state_id ? 'selected' : '' }}>
                                {{ $state->sname }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- District Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">District</label>
                    <select name="dist_id" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="">All Districts</option>
                        @foreach($districts as $district)
                            <option value="{{ $district->dist_id }}" {{ request('dist_id') == $district->dist_id ? 'selected' : '' }}>
                                {{ $district->dname }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Taluka Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Taluka</label>
                    <select name="taluka_id" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="">All Talukas</option>
                        @foreach($talukas as $taluka)
                            <option value="{{ $taluka->takula_id }}" {{ request('taluka_id') == $taluka->takula_id ? 'selected' : '' }}>
                                {{ $taluka->tname }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-3">
                    <button type="submit" class="bg-primary-500 hover:bg-primary-600 text-white font-medium px-4 py-2 rounded-lg transition h-[42px] flex items-center justify-center">
                        Apply
                    </button>
                    <a href="{{ route('events.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition h-[42px] flex items-center justify-center">
                        Clear
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Events Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Event Info
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            School
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Reminder
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($events as $event)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    @if($event->photo)
                                        <img src="{{ asset('storage/' . $event->photo) }}" alt="{{ $event->title }}" class="h-10 w-10 rounded-lg object-cover mr-3 cursor-pointer" onclick="window.open(this.src)">
                                    @else
                                        <div class="h-10 w-10 rounded-lg bg-gray-100 flex items-center justify-center mr-3">
                                            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $event->title }}
                                        </div>
                                        <div class="text-xs text-gray-500 truncate max-w-xs">{{ $event->notes }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($event->school_id)
                                    <div class="text-sm text-gray-900">{{ $event->school->schoolname }}</div>
                                @else
                                    <div class="text-sm text-gray-900">
                                        {{ $event->state->sname ?? '' }}
                                        {{ $event->district ? ' / ' . $event->district->dname : '' }}
                                        {{ $event->taluka ? ' / ' . $event->taluka->tname : '' }}
                                    </div>
                                    <div class="text-xs text-gray-500">Area-wide</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $event->event_date->format('d M, Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $event->reminder_date ? $event->reminder_date->format('d M, Y') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('events.show', $event->event_id) }}" class="text-gray-600 hover:text-gray-900 font-medium">
                                        View
                                    </a>
                                    @if(auth()->user()->hasPermission(10402))
                                    <a href="{{ route('events.edit', $event->event_id) }}" class="text-primary-500 hover:text-primary-600 font-medium">
                                        Edit
                                    </a>
                                    <a href="{{ route('events.reuse', $event->event_id) }}" class="text-green-600 hover:text-green-800 font-medium">
                                        Reuse
                                    </a>
                                    @endif
                                    
                                    @if(auth()->user()->hasPermission(10403))
                                    <form action="{{ route('events.destroy', $event->event_id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this event?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 font-medium">Delete</button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                No events found. <a href="{{ route('events.create') }}" class="text-primary-500 hover:text-primary-600 font-medium">Create one now</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($events->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $events->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
