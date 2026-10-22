@extends('layouts.app')

@section('title', 'School Leads')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">School Leads</h2>
                <p class="mt-1 text-sm text-gray-600">Manage school leads and registration URLs</p>
            </div>
            @canAccess(10202)
            <a href="{{ route('leads.create') }}"
                class="inline-flex items-center px-4 py-2 bg-[#00bde0] text-white rounded-lg hover:bg-[#00a8c9] transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create New Lead
            </a>
            @endcanAccess
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <form method="GET" action="{{ route('leads.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="registered" {{ request('status') == 'registered' ? 'selected' : '' }}>Registered</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="School name, email, or phone"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                </div>
                <div class="flex items-end">
                    <button type="submit"
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors mr-2">
                        Filter
                    </button>
                    <a href="{{ route('leads.index') }}"
                        class="px-4 py-2 text-gray-600 hover:text-gray-900 transition-colors">
                        Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Leads Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">School Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estimated Students</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($leads as $lead)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $lead->school_name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><x-masked-email :email="$lead->email" /></div>
                                <div class="text-sm text-gray-500 mt-1 flex items-center gap-2 group">
                                    <x-masked-phone :number="$lead->phone" />
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $lead->estimated_students ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($lead->status === 'pending')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                @elseif($lead->status === 'registered')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Registered</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Expired</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $lead->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('leads.show', $lead->lead_id) }}"
                                    class="text-[#00bde0] hover:text-[#00a8c9] mr-3">View</a>
                                @if($lead->status === 'pending')
                                    @canAccess(10202)
                                    <a href="{{ route('leads.edit', $lead->lead_id) }}"
                                        class="text-gray-600 hover:text-gray-900">Edit</a>
                                    @endcanAccess
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                No leads found. @canAccess(10202)
                                No leads found. <a href="{{ route('leads.create') }}" class="text-[#00bde0] hover:underline">Create your first lead</a>
                                No leads found. @endcanAccess
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($leads->hasPages())
            <div class="mt-4">
                {{ $leads->links() }}
            </div>
        @endif
    </div>
@endsection
