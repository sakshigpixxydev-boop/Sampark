@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Student Growth Cards</h1>
            <p class="text-sm text-gray-600 mt-1">Manage student growth marks and visualizations</p>
        </div>
        <div class="flex space-x-3">
            @canAccess(10504)
            <a href="{{ route('growth-criteria.index') }}" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium px-4 py-2 rounded-lg transition text-sm">
                Manage Criteria
            </a>
            @endcanAccess
            @canAccess(10502)
            <a href="{{ route('student-growth.create') }}" class="bg-primary-500 hover:bg-primary-600 text-white font-medium px-4 py-2 rounded-lg transition text-sm">
                Enter New Marks
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

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <form action="{{ route('student-growth.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">School</label>
                <select name="school_id" onchange="this.form.submit()" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <option value="">All Schools</option>
                    @foreach($schools as $school)
                        <option value="{{ $school->school_id }}" {{ request('school_id') == $school->school_id ? 'selected' : '' }}>
                            {{ $school->schoolname }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <a href="{{ route('student-growth.index') }}" class="text-sm text-gray-500 hover:text-gray-700 mb-2 underline">Clear Filters</a>
            </div>
        </form>
    </div>

    <!-- Growth Records Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">School</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recorded By</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Growth Visualization</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($records as $record)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $record->entry_date->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $record->student->fname }} {{ $record->student->lname }}</div>
                                <div class="text-xs text-gray-500">{{ $record->student->enrolment_no }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $record->school?->schoolname ?? 'Not Assigned' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $record->creator->fname }} {{ $record->creator->lname }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">

                                <div class="inline-flex items-center space-x-1">
                                    @php
                                        $scores = $record->scores ?? [];
                                        $total = 0;
                                        $count = 0;
                                    @endphp
                                    @foreach($scores as $cid => $mark)
                                        @if($count < 6)
                                            @php
                                                $markColor = \App\Models\StudentGrowthRecord::getColorInfo($mark);
                                            @endphp
                                            <div class="w-3 h-3 rounded-full {{ $markColor['bg_class'] }}" title="Criterion {{ $cid }}: {{ $mark }}"></div>
                                        @endif
                                        @php
                                            $total += $mark;
                                            $count++;
                                        @endphp
                                    @endforeach
                                    
                                    @php
                                        $displayAvg = $count > 0 ? $total / $count : 0;
                                        $displayColor = \App\Models\StudentGrowthRecord::getColorInfo($displayAvg);
                                    @endphp
                                    
                                    <span class="ml-2 text-xs font-bold {{ str_replace('bg-', 'text-', $displayColor['bg_class']) }}">
                                        {{ number_format($displayAvg, 1) }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium flex items-center justify-end space-x-4">
                                <a href="{{ route('student-growth.show', $record->growth_id) }}" class="text-primary-500 hover:text-primary-600">View Card</a>
                                <a href="{{ route('student-growth.student', $record->student_id) }}" class="text-gray-600 hover:text-gray-900">History</a>
                                @canAccess(10502)
                                    <a href="{{ route('student-growth.edit', $record->growth_id) }}" class="text-primary-500 hover:text-primary-600" title="Edit">
                                        Edit
                                    </a>
                                    <form action="{{ route('student-growth.destroy', $record->growth_id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this growth record? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 font-medium transition" title="Delete">
                                            Delete
                                        </button>
                                    </form>
                                @endcanAccess
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                No growth records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($records->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $records->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
