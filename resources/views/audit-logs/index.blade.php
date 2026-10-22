@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-900">Audit Logs</h2>
    <p class="text-sm text-gray-600 mt-1">View system activity and changes</p>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <form method="GET" action="{{ route('audit-logs.index') }}" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Event Type Filter -->
            <div>
                <label for="event_type" class="block text-sm font-medium text-gray-700 mb-1">
                    Event Type
                </label>
                <select name="event_type" id="event_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                    <option value="">All Types</option>
                    @foreach($eventTypes as $type)
                        <option value="{{ $type }}" {{ request('event_type') == $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Model Type Filter -->
            <div>
                <label for="auditable_type" class="block text-sm font-medium text-gray-700 mb-1">
                    Model Type
                </label>
                <select name="auditable_type" id="auditable_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                    <option value="">All Models</option>
                    @foreach($modelTypes as $type)
                        <option value="{{ $type }}" {{ request('auditable_type') == $type ? 'selected' : '' }}>
                            {{ class_basename($type) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Performed By Filter -->
            <div>
                <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Performed By
                </label>
                <select name="user_id" id="user_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                    <option value="">All Users</option>
                    @foreach($users as $u)
                        <option value="{{ $u->sysuser_id }}" {{ request('user_id') == $u->sysuser_id ? 'selected' : '' }}>
                            {{ $u->firstname }} {{ $u->lastname }} ({{ $u->role->rolename ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Specific Student Filter -->
            <div>
                <label for="student_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Select Student
                </label>
                <select name="student_id" id="student_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                    <option value="">All Students</option>
                    @foreach($students as $st)
                        <option value="{{ $st->student_id }}" {{ request('student_id') == $st->student_id ? 'selected' : '' }}>
                            {{ $st->fname }} {{ $st->lname }} (#{{ $st->student_id }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Student Keywords Search -->
            <div>
                <label for="student_search" class="block text-sm font-medium text-gray-700 mb-1">
                    Student Keyword Search
                </label>
                <input type="text" name="student_search" id="student_search" value="{{ request('student_search') }}" placeholder="Search name/email/contact..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
            </div>

            <!-- Date From -->
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">
                    Date From
                </label>
                <input type="text" name="date_from" id="date_from" value="{{ request('date_from') }}" class="date-picker w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent" placeholder="DD-MM-YYYY">
            </div>

            <!-- Date To -->
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">
                    Date To
                </label>
                <input type="text" name="date_to" id="date_to" value="{{ request('date_to') }}" class="date-picker w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent" placeholder="DD-MM-YYYY">
            </div>

            <!-- Description Search -->
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                    Search Description
                </label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Search in descriptions..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-3">
            <a href="{{ route('audit-logs.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition text-sm">
                Clear Filters
            </a>
            <button type="submit" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition text-sm">
                Apply Filters
            </button>
        </div>
    </form>
</div>

<!-- Audit Logs Table -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    @if($logs->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Timestamp
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Event
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            User
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Description
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Model
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            IP Address
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $log->created_at->format('Y-m-d H:i:s') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                @if($log->event_type === 'CREATE') bg-green-100 text-green-800
                                @elseif($log->event_type === 'UPDATE') bg-blue-100 text-blue-800
                                @elseif($log->event_type === 'DELETE') bg-red-100 text-red-800
                                @elseif(str_contains($log->event_type, 'LOGIN')) bg-purple-100 text-purple-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $log->event_type }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($log->user)
                                {{ $log->user->firstname }} {{ $log->user->lastname }}
                            @else
                                <span class="text-gray-400">System</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ Str::limit($log->description, 50) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            @if($log->auditable_type)
                                {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $log->ip_address }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('audit-logs.show', $log->id) }}" class="text-[#00bde0] hover:text-[#0099b8]">
                                View Details
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $logs->links() }}
        </div>
    @else
        <div class="p-6 text-center text-gray-500">
            No audit logs found.
        </div>
    @endif
</div>
@endsection
