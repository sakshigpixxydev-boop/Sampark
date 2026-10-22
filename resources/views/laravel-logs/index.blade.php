@extends('layouts.app')

@section('title', 'Laravel Logs')

@section('content')
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Laravel Logs</h2>
        <p class="text-sm text-gray-600 mt-1">Monitor, search, clear and delete Laravel log files</p>
    </div>
</div>

<!-- Filters & Control Panel -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <form method="GET" action="{{ route('laravel-logs.index') }}" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Log File Dropdown -->
            <div>
                <label for="file" class="block text-sm font-medium text-gray-700 mb-1">
                    Log File
                </label>
                <select name="file" id="file" onchange="this.form.submit()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                    @foreach($files as $file)
                        <option value="{{ $file['name'] }}" {{ $selectedFile === $file['name'] ? 'selected' : '' }}>
                            {{ $file['name'] }} ({{ $file['size'] }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Log Level Filter -->
            <div>
                <label for="level" class="block text-sm font-medium text-gray-700 mb-1">
                    Log Level
                </label>
                <select name="level" id="level" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                    <option value="">All Levels</option>
                    @foreach($levels as $level)
                        <option value="{{ $level }}" {{ request('level') === $level ? 'selected' : '' }}>
                            {{ $level }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Search Input -->
            <div class="md:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                    Search Text
                </label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Search in log message or stack trace..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
            </div>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('laravel-logs.index', ['file' => $selectedFile]) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition text-sm">
                Clear Filters
            </a>
            <button type="submit" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition text-sm">
                Apply Filters
            </button>
        </div>
    </form>
</div>

<!-- Log Entries List Card -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h3 class="text-sm font-semibold text-gray-900">
                Viewing: <span class="font-mono text-[#00bde0]">{{ $selectedFile }}</span>
            </h3>
            <p class="text-xs text-gray-500 mt-0.5 font-medium">Showing parsed entries</p>
        </div>
        
        <div class="flex items-center space-x-3">
            @canAccess(11502)
            <form action="{{ route('laravel-logs.clear') }}" method="POST" onsubmit="return confirm('Are you sure you want to clear this log file?');" class="inline">
                @csrf
                <input type="hidden" name="file" value="{{ $selectedFile }}">
                <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-semibold px-3 py-2 rounded-lg transition">
                    Clear Log File
                </button>
            </form>
            @endcanAccess
            
            @canAccess(11503)
            <form action="{{ route('laravel-logs.destroy') }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this log file?');" class="inline">
                @csrf
                @method('DELETE')
                <input type="hidden" name="file" value="{{ $selectedFile }}">
                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white text-xs font-semibold px-3 py-2 rounded-lg transition">
                    Delete Log File
                </button>
            </form>
            @endcanAccess
        </div>
    </div>

    @if($paginatedLogs->count() > 0)
        <div class="divide-y divide-gray-200">
            @foreach($paginatedLogs as $index => $log)
                <div x-data="{ expanded: false }" class="p-6 hover:bg-gray-50 transition">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-2">
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                @if($log['level'] === 'DEBUG') bg-gray-100 text-gray-800
                                @elseif($log['level'] === 'INFO') bg-blue-100 text-blue-800
                                @elseif($log['level'] === 'NOTICE') bg-teal-100 text-teal-800
                                @elseif($log['level'] === 'WARNING') bg-yellow-100 text-yellow-800
                                @elseif($log['level'] === 'ERROR') bg-red-100 text-red-800
                                @elseif(in_array($log['level'], ['CRITICAL', 'ALERT', 'EMERGENCY'])) bg-red-600 text-white
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $log['level'] }}
                            </span>
                            <span class="text-xs text-gray-500 font-mono">{{ $log['timestamp'] }}</span>
                            <span class="text-xs text-gray-400 font-mono">[{{ $log['environment'] }}]</span>
                        </div>
                        
                        @if(!empty($log['stack_trace']))
                            <button @click="expanded = !expanded" class="text-xs text-[#00bde0] hover:text-[#0099b8] font-medium flex items-center self-start md:self-auto focus:outline-none">
                                <span x-text="expanded ? 'Hide Trace' : 'Show Trace'"></span>
                                <svg class="w-4 h-4 ml-1 transition-transform duration-200" :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                        @endif
                    </div>

                    <div class="mt-2 text-sm text-gray-800 font-mono break-all whitespace-pre-wrap">{{ trim($log['message']) }}</div>

                    @if(!empty($log['stack_trace']))
                        <div x-show="expanded" x-cloak class="mt-4 p-4 bg-gray-900 text-gray-100 rounded-lg overflow-x-auto text-xs font-mono max-h-96 whitespace-pre">{{ $log['stack_trace'] }}</div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $paginatedLogs->links() }}
        </div>
    @else
        <div class="p-12 text-center text-gray-500">
            No log entries found.
        </div>
    @endif
</div>
@endsection
