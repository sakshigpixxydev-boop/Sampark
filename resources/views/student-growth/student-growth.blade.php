@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 bg-white p-8 rounded-3xl shadow-lg border border-gray-100">
        <div class="flex items-center space-x-6">
            <div class="w-16 h-16 rounded-full bg-linear-to-br from-primary-500 to-[#083344] flex items-center justify-center text-white text-3xl font-black shadow-xl">
                {{ substr($student->fname, 0, 1) }}{{ substr($student->lname, 0, 1) }}
            </div>
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 leading-tight">{{ $student->fname }} {{ $student->lname }}</h1>
                <p class="text-sm font-bold text-gray-400 mt-1 uppercase tracking-widest">{{ $student->enrolment_no }} | Student Profile Growth</p>
                <div class="mt-2 flex items-center text-xs font-semibold text-gray-500 uppercase">
                    <svg class="w-4 h-4 mr-1 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    {{ $student->school?->schoolname ?? 'Not Assigned' }}
                </div>
            </div>
        </div>
        <div class="mt-6 md:mt-0 flex space-x-3">
            @canAccess(10502)
            <a href="{{ route('student-growth.create', ['student_id' => $student->student_id, 'school_id' => $student->school_id]) }}" class="bg-primary-500 hover:bg-primary-600 text-white font-bold py-3 px-6 rounded-2xl shadow-lg hover:shadow-xl transition-all flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Record New Growth Entry
            </a>
            @endcanAccess
        </div>
    </div>

    <!-- Growth Timeline Visualization -->
    <div class="mb-10">
        <h2 class="text-xl font-black text-[#083344] mb-6 flex items-center">
            <span class="w-8 h-8 rounded-lg bg-primary-500 text-white flex items-center justify-center mr-3 shadow-md">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
            </span>
            Growth Timeline History
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($records as $record)
                @php
                    $totalMarks = 0;
                    $count = 0;
                    foreach ($criteria as $criterion) {
                        $totalMarks += $record->scores[$criterion->criterion_id] ?? 0;
                        $count++;
                    }
                    $avgMarks = $count > 0 ? $totalMarks / $count : 0;
                    $overallColor = \App\Models\StudentGrowthRecord::getColorInfo($avgMarks);
                @endphp
                <div class="bg-white rounded-3xl shadow-lg overflow-hidden border border-gray-100 hover:shadow-2xl transition-all duration-300 group">
                    <div class="h-2 w-full {{ $overallColor['bg_class'] }}"></div>
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ $record->entry_date->format('d M Y') }}</p>
                                <p class="text-lg font-black text-[#083344] mt-1 group-hover:text-primary-500 transition-colors">Growth Card</p>
                            </div>
                            <div class="w-12 h-12 rounded-2xl {{ $overallColor['bg_class'] }} flex items-center justify-center text-white text-xl font-bold shadow-md">
                                {{ number_format($avgMarks, 1) }}
                            </div>
                        </div>

                        <div class="space-y-3 mb-8">
                             @foreach($criteria->take(4) as $criterion)
                                @php
                                    $m = $record->scores[$criterion->criterion_id] ?? 0;
                                    $fieldColor = \App\Models\StudentGrowthRecord::getColorInfo($m);
                                @endphp
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-gray-500 uppercase">{{ str_replace('Behaviour', 'Behav.', $criterion->criterion_name) }}</span>
                                    <div class="flex items-center">
                                        <div class="w-20 bg-gray-100 h-1.5 rounded-full mr-2 overflow-hidden border border-gray-50 shadow-inner">
                                            <div class="h-full {{ $fieldColor['bg_class'] }}" style="width: {{ ($m * 10) }}%"></div>
                                        </div>
                                        <span class="font-black text-gray-700 w-4 text-right">{{ $m }}</span>
                                    </div>
                                </div>
                             @endforeach
                        </div>

                        <a href="{{ route('student-growth.show', $record->growth_id) }}" class="block w-full text-center py-3 bg-[#f8fafc] text-sm font-bold text-[#083344] rounded-2xl hover:bg-primary-500 hover:text-white transition-all shadow-sm">
                            View Detailed Analysis
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-3xl p-20 text-center shadow-lg border border-gray-100">
                    <div class="w-20 h-20 bg-gray-50 text-gray-300 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">No Growth entries yet</h3>
                    <p class="text-gray-500 max-w-xs mx-auto mb-8">Start recording student progress to see the growth visualization here.</p>
                    @canAccess(10502)
                    <a href="{{ route('student-growth.create', ['student_id' => $student->student_id, 'school_id' => $student->school_id]) }}" class="inline-flex items-center bg-primary-500 hover:bg-primary-600 text-white font-bold py-3 px-8 rounded-2xl shadow-lg transition">
                        Add First Entry
                    </a>
                    @endcanAccess
                </div>
            @endforelse

        </div>
    </div>
</div>
@endsection
