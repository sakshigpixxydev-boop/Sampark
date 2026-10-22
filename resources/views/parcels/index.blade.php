@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Parcels</h1>
        @canAccess(10112)
        <a href="{{ route('parcels.create') }}" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition">
            Add New Parcel
        </a>
        @endcanAccess
    </div>

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
        <form method="GET" action="{{ route('parcels.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search Tracking Number</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent"
                           placeholder="Enter tracking number">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Student</label>
                    <select name="student_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                        <option value="">All Students</option>
                        @foreach($students as $student)
                            <option value="{{ $student->student_id }}" {{ request('student_id') == $student->student_id ? 'selected' : '' }}>
                                {{ $student->firstname }} {{ $student->middlename }} {{ $student->lastname }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Parcel Company</label>
                    <select name="parcelcom_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                        <option value="">All Companies</option>
                        @foreach($parcelCompanies as $company)
                            <option value="{{ $company->parcelcom_id }}" {{ request('parcelcom_id') == $company->parcelcom_id ? 'selected' : '' }}>
                                {{ $company->companyname }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="flex items-end">
                    <button type="submit" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition w-full">
                        Apply Filters
                    </button>
                </div>
            </div>
            
            @if(request()->hasAny(['search', 'student_id', 'parcelcom_id']))
                <div>
                    <a href="{{ route('parcels.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                        Clear Filters
                    </a>
                </div>
            @endif
        </form>
    </div>

    <!-- Parcels Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Parcel Company</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tracking Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($parcels as $parcel)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $parcel->student->firstname }} {{ $parcel->student->middlename }} {{ $parcel->student->lastname }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $parcel->parcelCompany->companyname }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex items-center space-x-2">
                                    <span class="text-gray-900">{{ $parcel->trakingnumber }}</span>
                                    @if($parcel->parcelCompany->trackingurl)
                                        <a href="{{ $parcel->parcelCompany->generateTrackingUrl($parcel->trakingnumber) }}" 
                                           target="_blank"
                                           class="text-[#00bde0] hover:text-[#0099b8]"
                                           title="Track Parcel">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $parcel->notes ? Str::limit($parcel->notes, 50) : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($parcel->created_at)->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                @canAccess(10112)
                                <a href="{{ route('parcels.edit', $parcel->parcel_id) }}" class="text-[#00bde0] hover:text-[#0099b8]">Edit</a>
                                @endcanAccess
                                @canAccess(10113)
                                <form action="{{ route('parcels.destroy', $parcel->parcel_id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this parcel?')">Delete</button>
                                </form>
                                @endcanAccess
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                No parcels found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($parcels->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $parcels->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
