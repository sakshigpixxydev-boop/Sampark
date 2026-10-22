@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">Enter Growth Marks</h1>
                <p class="text-sm text-gray-600 mt-1">Select school and student to record growth criteria marks.</p>
            </div>

    <!-- Error Messages -->
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <form action="{{ route('student-growth.store') }}" method="POST" class="p-8">
            @csrf

            <!-- Selection Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8 pb-8 border-b border-gray-100">
                <!-- School Selection -->
                <div>
                    <label for="school_id" class="block text-sm font-medium text-gray-700 mb-2">School <span class="text-red-500">*</span></label>
                    <select id="school_id" name="school_id" required
                            class="searchable-select w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                            onchange="window.location.href = '{{ route('student-growth.create') }}?school_id=' + this.value">
                        <option value="">Select School</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->school_id }}" {{ $selectedSchoolId == $school->school_id ? 'selected' : '' }}>
                                {{ $school->schoolname }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Student Selection -->
                <div>
                    <label for="student_id" class="block text-sm font-medium text-gray-700 mb-2">Student <span class="text-red-500">*</span></label>
                    <select id="student_id" name="student_id" required
                            class="searchable-select w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                            {{ empty($students) ? 'disabled' : '' }}
                            onchange="window.location.href = '{{ route('student-growth.create') }}?school_id={{ $selectedSchoolId }}&student_id=' + this.value">

                        <option value="">Select Student</option>
                        @foreach($students as $s)
                            <option value="{{ $s->student_id }}" {{ $selectedStudentId == $s->student_id ? 'selected' : '' }}>
                                {{ $s->fname }} {{ $s->lname }}{{ $s->enrolment_no ? " ($s->enrolment_no)" : "" }}
                            </option>
                        @endforeach

                    </select>
                </div>
            </div>

            @if($student)
            <!-- Marks Entry Section -->
            <div class="space-y-8 animate-fade-in">
                <div class="flex justify-between items-center bg-gray-50 p-4 rounded-lg">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Selected Student</p>
                        <h3 class="text-lg font-bold text-[#083344] mt-1">{{ $student->fname }} {{ $student->lname }}</h3>
                    </div>
                    <div>
                        <label for="entry_date" class="block text-xs font-semibold text-gray-500 uppercase tracking-widest mb-1">Entry Date <span class="text-red-500">*</span></label>
                        <input type="text" id="entry_date" name="entry_date" required value="{{ date('d/m/Y') }}"
                               class="date-picker px-4 py-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" placeholder="DD/MM/YYYY">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                    @foreach($criteria as $criterion)
                    @php
                        $minScore = floatval($criterion->min_score ?? 0);
                        $maxScore = floatval($criterion->max_score ?? 10);
                        $defaultScore = $minScore + (($maxScore - $minScore) / 2);
                        $maxScoreDisplay = rtrim(rtrim(number_format($maxScore, 2), '0'), '.');
                    @endphp
                    <div class="marks-input-group" x-data="{ marks: {{ $defaultScore }}, maxScore: {{ $maxScore }} }">
                        <div class="flex justify-between items-center mb-2">
                            <label for="criteria_{{ $criterion->criterion_id }}" class="text-sm font-bold text-gray-700">{{ $criterion->criterion_name }}</label>
                            <span class="text-lg font-black" :class="marks >= (maxScore * 0.85) ? 'text-teal-700' : (marks >= (maxScore * 0.7) ? 'text-emerald-400' : (marks >= (maxScore * 0.5) ? 'text-sky-400' : (marks >= (maxScore * 0.3) ? 'text-yellow-500' : 'text-orange-500')))">
                                <span x-text="marks"></span>/{{ $maxScoreDisplay }}
                            </span>
                        </div>
                        <input type="range" id="criteria_{{ $criterion->criterion_id }}" name="criteria[{{ $criterion->criterion_id }}]" min="{{ $minScore }}" max="{{ $maxScore }}" step="0.5" x-model="marks"
                               class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-primary-500">
                        <div class="flex justify-between text-[10px] text-gray-400 mt-1 uppercase font-bold">
                            <span>Needs Support</span>
                            <span>Growing</span>
                            <span>Inspiring</span>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="mt-8 pt-8 border-t border-gray-100">
                    <h3 class="text-lg font-bold text-[#083344] mb-6 flex items-center">
                        <span class="w-8 h-8 rounded-lg bg-sky-100 text-sky-600 flex items-center justify-center mr-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        </span>
                        Educator Overall Reflection
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="educator_strength" class="block text-sm font-medium text-gray-700 mb-2">Strength Observed</label>
                            <textarea id="educator_strength" name="educator_strength" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all" placeholder="What is the student doing well?"></textarea>
                        </div>
                        <div>
                            <label for="educator_growth_area" class="block text-sm font-medium text-gray-700 mb-2">Area for Growth</label>
                            <textarea id="educator_growth_area" name="educator_growth_area" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all" placeholder="Where can they improve?"></textarea>
                        </div>
                        <div>
                            <label for="educator_next_step" class="block text-sm font-medium text-gray-700 mb-2">Next Step (Actionable)</label>
                            <textarea id="educator_next_step" name="educator_next_step" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all" placeholder="What should they focus on next semester?"></textarea>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-8 border-t border-gray-100 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h3 class="text-lg font-bold text-[#083344] mb-4 flex items-center">
                            <span class="w-8 h-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center mr-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            </span>
                            Guardian Feedback (Optional)
                        </h3>
                        <textarea id="guardian_feedback" name="guardian_feedback" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all" placeholder="Home-based observations, responsibility, or changes noticed..."></textarea>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-[#083344] mb-4 flex items-center">
                            <span class="w-8 h-8 rounded-lg bg-teal-100 text-teal-600 flex items-center justify-center mr-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </span>
                            Student Self-Reflection (Optional)
                        </h3>
                        <textarea id="student_self_reflection" name="student_self_reflection" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all" placeholder="Honest self-awareness, personal growth, next SANKALP goal..."></textarea>
                    </div>
                </div>

                <div class="pt-8 border-t border-gray-100 flex justify-end space-x-4">
                    <a href="{{ route('student-growth.index') }}" class="px-6 py-3 border border-gray-300 rounded-xl text-gray-600 font-medium hover:bg-gray-50 transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-10 py-3 bg-primary-500 hover:bg-primary-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all">
                        Save Growth Record
                    </button>
                </div>

            </div>
            @endif
        </form>
    </div>
</div>

<style>
@keyframes fade-in {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fade-in 0.4s ease-out forwards;
}
input[type=range]::-webkit-slider-thumb {
    width: 20px;
    height: 20px;
    background: var(--color-primary-500, #00bde0);
    cursor: pointer;
    border-radius: 50%;
    border: 3px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>
@endsection
