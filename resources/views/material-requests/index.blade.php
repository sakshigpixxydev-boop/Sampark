@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Material Requests</h1>
            <p class="text-sm text-gray-600 mt-1">Track and manage material delivery requests</p>
        </div>
        @canAccess(10612)
        <a href="{{ route('material-requests.create') }}" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition text-sm">
            Request Materials
        </a>
        @endcanAccess
    </div>

    <!-- Filters -->
    <x-filter-panel :action="route('material-requests.index')">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
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
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="dispatched" {{ request('status') === 'dispatched' ? 'selected' : '' }}>Dispatched</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
        </div>
    </x-filter-panel>

    <!-- Requests Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request Info</th>
                        @if(!auth()->user()->hasRole('School'))
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">School</th>
                        @endif
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Material</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty/Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($requests as $req)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">#REQ-{{ str_pad($req->id, 5, '0', STR_PAD_LEFT) }}</div>
                                <div class="text-xs text-gray-500">{{ $req->created_at->format('d M Y, h:i A') }}</div>
                            </td>
                            @if(!auth()->user()->hasRole('School'))
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $req->school->schoolname }}</div>
                                <div class="text-xs text-gray-500">{{ $req->school->taluka->tname ?? '' }}</div>
                            </td>
                            @endif
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $req->material->name }}</div>
                                <div class="text-xs text-gray-500 capitalize">{{ $req->delivery_type }} Delivery</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $req->quantity }} Units</div>
                                <div class="text-xs font-medium text-[#00bde0]">₹{{ number_format($req->total_amount, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $req->status_color }} capitalize">
                                    {{ $req->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('material-requests.show', $req->id) }}" class="text-[#00bde0] hover:text-[#0099b8]">View Details</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->hasRole('School') ? 5 : 6 }}" class="px-6 py-8 text-center text-gray-500">
                                No material requests found.
                                @canAccess(10612)
                                <a href="{{ route('material-requests.create') }}" class="text-[#00bde0] hover:text-[#0099b8]">Submit a request</a>
                                @endcanAccess
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
