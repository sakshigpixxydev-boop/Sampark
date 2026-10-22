@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Ticketing System</h1>
            <p class="text-sm text-gray-600 mt-1">Raise and track support tickets for various issues</p>
        </div>
        <div class="flex gap-3">
            @if(auth()->user()->hasRole('Admin'))
            <a href="{{ route('ticket-categories.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-4 py-2 rounded-lg transition text-sm border border-gray-300">
                Manage Categories
            </a>
            @endif
            <a href="{{ route('tickets.create') }}" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition text-sm">
                Create New Ticket
            </a>
        </div>
    </div>

    @if(session('success'))
        <x-alert type="success" :message="session('success')" class="mb-6" />
    @endif

    <!-- Filters -->
    <x-filter-panel :action="route('tickets.index')">
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                    <option value="">All Statuses</option>
                    <option value="Open" {{ request('status') == 'Open' ? 'selected' : '' }}>Open</option>
                    <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="Resolved" {{ request('status') == 'Resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="Closed" {{ request('status') == 'Closed' ? 'selected' : '' }}>Closed</option>
                    <option value="Reopen" {{ request('status') == 'Reopen' ? 'selected' : '' }}>Reopen</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <select name="ticket_category_id" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('ticket_category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            @if(!auth()->user()->hasRole('School'))
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">School</label>
                <select name="school_id" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                    <option value="">All Schools</option>
                    @foreach($schools as $school)
                        <option value="{{ $school->school_id }}" {{ request('school_id') == $school->school_id ? 'selected' : '' }}>{{ $school->schoolname }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                <select name="priority" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                    <option value="">All Priorities</option>
                    <option value="Low" {{ request('priority') == 'Low' ? 'selected' : '' }}>Low</option>
                    <option value="Medium" {{ request('priority') == 'Medium' ? 'selected' : '' }}>Medium</option>
                    <option value="High" {{ request('priority') == 'High' ? 'selected' : '' }}>High</option>
                    <option value="Critical" {{ request('priority') == 'Critical' ? 'selected' : '' }}>Critical</option>
                </select>
            </div>
        </div>
    </x-filter-panel>

    <!-- Tickets Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket Info</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        @if(!auth()->user()->hasRole('School'))
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">School</th>
                        @endif
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($tickets as $ticket)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">#TKT-{{ str_pad($ticket->ticket_id, 5, '0', STR_PAD_LEFT) }}</div>
                                <div class="text-xs text-gray-500">{{ $ticket->created_at->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 font-medium">{{ $ticket->title }}</div>
                                <div class="text-xs text-gray-500 truncate max-w-xs">{{ Str::limit(strip_tags($ticket->description), 50) }}</div>
                            </td>
                            @if(!auth()->user()->hasRole('School'))
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 font-semibold text-gray-700">{{ optional($ticket->school)->schoolname ?? 'General Issue' }}</div>
                                <div class="text-xs text-gray-500">{{ optional($ticket->creator)->fname }} {{ optional($ticket->creator)->lname }}</div>
                            </td>
                            @endif
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    {{ optional($ticket->category)->name ?? $ticket->ticket_type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @php
                                    $priorityClasses = [
                                        'Low' => 'bg-gray-100 text-gray-800 border border-gray-200',
                                        'Medium' => 'bg-blue-50 text-blue-700 border border-blue-100',
                                        'High' => 'bg-orange-50 text-orange-700 border border-orange-100',
                                        'Critical' => 'bg-red-50 text-red-700 border border-red-100',
                                    ];
                                    $pClass = $priorityClasses[$ticket->priority] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $pClass }}">
                                    {{ $ticket->priority ?? 'Medium' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @php
                                    $statusClasses = [
                                        'Open' => 'bg-yellow-100 text-yellow-800',
                                        'In Progress' => 'bg-blue-100 text-blue-800',
                                        'Resolved' => 'bg-green-100 text-green-800',
                                        'Closed' => 'bg-gray-100 text-gray-800',
                                        'Reopen' => 'bg-red-100 text-red-800',
                                    ];
                                    $class = $statusClasses[$ticket->status] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $class }}">
                                    {{ $ticket->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('tickets.show', $ticket->ticket_id) }}" class="text-[#00bde0] hover:text-[#0099b8]">View Details</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->hasRole('School') ? 6 : 7 }}" class="px-6 py-8 text-center text-gray-500">
                                No tickets found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tickets->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
