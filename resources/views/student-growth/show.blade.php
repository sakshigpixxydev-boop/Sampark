@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 print:py-0 print:px-0">
    <!-- Action Bar -->
    <div class="flex justify-between items-center mb-8 print:hidden">
        <a href="{{ route('student-growth.index') }}" class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-primary-500 transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to List
        </a>
        <div class="flex space-x-3 items-center">
            @canAccess(10502)
                <a href="{{ route('student-growth.edit', $record->growth_id) }}" class="bg-white hover:text-primary-500 border border-gray-300 text-primary-600 font-semibold py-2 px-4 rounded-lg shadow-sm transition text-sm h-full">
                    Edit
                </a>
                <form action="{{ route('student-growth.destroy', $record->growth_id) }}" method="POST" class="inline-block m-0 p-0" onsubmit="return confirm('Are you sure you want to delete this growth record? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-white hover:bg-red-50 border border-gray-300 text-red-600 font-semibold py-2 px-4 rounded-lg shadow-sm transition text-sm h-full mr-3">
                        Delete
                    </button>
                </form>
            @endcanAccess
             <button onclick="window.print()" class="bg-gray-100 hover:bg-gray-200 border border-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg flex items-center shadow-sm transition text-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print
            </button>
            <a href="{{ route('student-growth.student', $record->student_id) }}" class="bg-primary-500 hover:bg-primary-600 text-white font-semibold py-2 px-4 rounded-lg shadow-sm transition text-sm">
                Full Growth History
            </a>
        </div>
    </div>

    <!-- Growth Card Visualization -->
    <div class="growth-card-container bg-white rounded-3xl shadow-2xl overflow-hidden print:overflow-visible border border-gray-100">
        <!-- Card Header -->
        <div class="bg-gradient-to-r from-primary-600 to-primary-900 p-10 print:p-8 text-white relative overflow-hidden print:overflow-visible" style="-webkit-print-color-adjust: exact; print-color-adjust: exact;">
            <div class="relative z-10 flex flex-col md:flex-row justify-between items-start">
                <div class="flex flex-col items-start mb-6 md:mb-0">
                    <div class="mb-5 bg-white p-2 rounded-xl shadow-lg border border-white/20 inline-block">
                        <img src="{{ asset('images/sankalp.png') }}" alt="Logo" class="h-10 w-auto object-contain">
                    </div>
                    <div>
                        <p class="text-[#caf0f8] text-xs font-bold uppercase tracking-widest mb-1">Semester Growth Card</p>
                        <h1 class="text-4xl print:text-2xl font-extrabold">{{ $record->student->fname }} {{ $record->student->lname }}</h1>
                        <p class="text-sm italic opacity-90 mt-2 max-w-lg print:text-xs">Every aspirant grows at a different pace. This card reflects personal progress, not comparison.</p>
                        <p class="text-lg print:text-sm opacity-90 mt-4 print:mt-2">{{ $record->school?->schoolname ?? 'Not Assigned' }}</p>
                        
                        <div class="mt-4 flex items-center space-x-2 bg-white/10 backdrop-blur-sm rounded-full px-4 py-1 border border-white/20">
                            <span class="text-xs font-semibold">Evaluator: {{ $record->creator->fname }} {{ $record->creator->lname }}</span>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 inline-block border border-white/20">
                        <p class="text-xs font-semibold opacity-70 uppercase tracking-widest">Entry Date</p>
                        <p class="text-xl font-black">{{ $record->entry_date->format('d F Y') }}</p>
                    </div>
                </div>
            </div>
            <div class="absolute top-[-50px] right-[-50px] w-64 h-64 bg-white opacity-5 rounded-full"></div>
            <div class="absolute bottom-[-100px] left-[-20px] w-48 h-48 bg-primary-500 opacity-20 rounded-full blur-3xl"></div>
        </div>

        @php
            $totalMarks = 0;
            $count = 0;
            $labels = [];
            $data = [];
            
            foreach($criteria as $criterion) {
                $marks = floatval($record->scores[$criterion->criterion_id] ?? 0);
                $minScore = floatval($criterion->min_score ?? 0);
                $maxScore = floatval($criterion->max_score ?? 10);
                
                // Normalize to a 10-point scale for the radar chart and overall index
                $normalizedScore = $maxScore > $minScore ? (($marks - $minScore) / ($maxScore - $minScore)) * 10 : 0;
                
                $totalMarks += $normalizedScore;
                $count++;
                $labels[] = $criterion->criterion_name;
                $data[] = round($normalizedScore, 2);
            }
            $avgMarks = $count > 0 ? $totalMarks / $count : 0;
            $overallColor = \App\Models\StudentGrowthRecord::getColorInfo($avgMarks);
        @endphp

        <div class="p-10 print:p-6 grid grid-cols-1 lg:grid-cols-2 print:grid-cols-2 gap-12 print:gap-10 border-b border-gray-100">
            <!-- Radar Chart (SANKALP Sixer) -->
            <div class="flex flex-col items-center justify-center print:mb-0">
                <h3 class="text-xl print:text-lg font-black text-primary-900 mb-4 text-center">SANKALP Sixer Growth Profile</h3>
                <div class="w-full max-w-md relative h-80 print:h-64">
                    <canvas id="growthRadarChart"></canvas>
                </div>
            </div>

            <!-- Growth Index -->
            <div class="flex flex-col justify-center items-center bg-[#f8fafc] rounded-3xl border border-gray-200 p-8 print:p-6 shadow-inner print:break-inside-avoid" style="-webkit-print-color-adjust: exact; print-color-adjust: exact;">
                <h3 class="text-lg font-bold text-gray-600 mb-6 print:mb-4 uppercase tracking-widest">Semester Growth Index</h3>
                <div class="relative flex items-center justify-center">
                    <div class="w-48 h-48 print:w-40 print:h-40 rounded-full border-12 print:border-8 border-white shadow-xl flex items-center justify-center {{ $overallColor['bg_class'] }}" style="-webkit-print-color-adjust: exact; print-color-adjust: exact;">
                        <div class="text-center text-white">
                            <span class="text-6xl print:text-5xl font-black leading-none">{{ number_format($avgMarks, 1) }}</span>
                        </div>
                    </div>
                    <div class="absolute -z-10 w-52 h-52 rounded-full blur-2xl opacity-30 {{ $overallColor['bg_class'] }}"></div>
                </div>

                <div class="mt-8 text-center">
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-widest">Current Stage</p>
                    <p class="mt-2 text-2xl font-black {{ str_replace('bg-', 'text-', $overallColor['bg_class']) }} uppercase">{{ $overallColor['color'] }}</p>
                </div>

                <!-- Legend -->
                <div class="mt-8 w-full">
                    <div class="grid grid-cols-2 gap-3 text-[10px] font-bold text-gray-500 uppercase">
                        <div class="flex items-center space-x-2"><div class="w-3 h-3 rounded-full bg-orange-400"></div><span>1.0-2.9 Needs Support</span></div>
                        <div class="flex items-center space-x-2"><div class="w-3 h-3 rounded-full bg-yellow-400"></div><span>3.0-4.9 Beginning</span></div>
                        <div class="flex items-center space-x-2"><div class="w-3 h-3 rounded-full bg-sky-400"></div><span>5.0-6.9 Steady</span></div>
                        <div class="flex items-center space-x-2"><div class="w-3 h-3 rounded-full bg-emerald-400"></div><span>7.0-8.4 Strong</span></div>
                        <div class="flex items-center space-x-2 col-span-2 justify-center"><div class="w-3 h-3 rounded-full bg-teal-700"></div><span>8.5-10.0 Inspiring Growth</span></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Reflections Section -->
        <div class="p-10 print:p-6 bg-gray-50/50" style="-webkit-print-color-adjust: exact; print-color-adjust: exact;">
            @php
                $reflections = $record->reflections ?? [];
            @endphp
            
            <!-- Educator Reflection -->
            <div class="mb-10 print:mb-6">
                <h3 class="text-lg font-bold text-primary-900 mb-4 flex items-center">
                    <span class="w-8 h-8 rounded-lg bg-sky-100 text-sky-600 flex items-center justify-center mr-3 shadow-sm">
                        <svg class="w-5 h-5 print:w-4 print:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    </span>
                    Educator Overall Reflection
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 print:grid-cols-3 gap-6 print:gap-4 items-stretch">
                    <div class="bg-white p-6 print:p-4 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition h-full">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Strength Observed</p>
                        <p class="text-gray-700 print:text-sm font-medium break-words">{{ $reflections['educator_strength'] ?? 'No strength noted yet.' }}</p>
                    </div>
                    <div class="bg-white p-6 print:p-4 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition h-full">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Area for Growth</p>
                        <p class="text-gray-700 print:text-sm font-medium break-words">{{ $reflections['educator_growth_area'] ?? 'No area for growth noted yet.' }}</p>
                    </div>
                    <div class="bg-white p-6 print:p-4 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition h-full">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Next Step</p>
                        <p class="text-gray-700 print:text-sm font-medium break-words">{{ $reflections['educator_next_step'] ?? 'No actionable next step noted yet.' }}</p>
                    </div>
                </div>
            </div>

            <!-- Guardian & Student Reflections -->
            <div class="grid grid-cols-1 md:grid-cols-2 print:grid-cols-2 gap-8 print:gap-6 items-stretch">
                <div class="bg-white p-8 print:p-5 rounded-3xl border border-gray-100 shadow-sm relative overflow-hidden group hover:border-orange-200 transition h-full">
                    <div class="absolute top-0 right-0 p-6 opacity-[0.03] group-hover:opacity-10 transition">
                        <svg class="w-24 h-24 print:w-16 print:h-16 text-orange-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <h3 class="text-lg print:text-base font-bold text-orange-600 mb-4 print:mb-2 relative z-10 flex items-center">
                        <span class="w-2 h-2 rounded-full bg-orange-600 mr-2"></span> Guardian Reflection
                    </h3>
                    <p class="text-gray-700 print:text-sm font-medium relative z-10 italic break-words">"{{ $reflections['guardian_feedback'] ?? 'Awaiting guardian feedback or observations.' }}"</p>
                </div>
                
                <div class="bg-white p-8 print:p-5 rounded-3xl border border-gray-100 shadow-sm relative overflow-hidden group hover:border-teal-200 transition h-full">
                    <div class="absolute top-0 right-0 p-6 opacity-[0.03] group-hover:opacity-10 transition">
                        <svg class="w-24 h-24 print:w-16 print:h-16 text-teal-600" fill="currentColor" viewBox="0 0 24 24"><path d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-lg print:text-base font-bold text-teal-600 mb-4 print:mb-2 relative z-10 flex items-center">
                        <span class="w-2 h-2 rounded-full bg-teal-600 mr-2"></span> Student Self-Reflection
                    </h3>
                    <p class="text-gray-700 print:text-sm font-medium relative z-10 italic break-words">"{{ $reflections['student_self_reflection'] ?? 'Awaiting student self-reflection and goal.' }}"</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-6 print:p-4 text-center border-t border-gray-100">
             <p class="text-sm italic text-gray-400 font-medium tracking-wide">A mirror of progress. A guide for the next step. A celebration of transformation.</p>
        </div>

    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('growthRadarChart').getContext('2d');
    
    const data = {
        labels: {!! json_encode($labels) !!},
        datasets: [{
            label: 'Growth Points',
            data: {!! json_encode($data) !!},
            backgroundColor: 'rgba(56, 189, 248, 0.25)', // Sky blue translucent
            borderColor: 'rgba(15, 118, 110, 0.9)', // Deep teal border
            pointBackgroundColor: 'rgba(250, 204, 21, 1)', // Yellow points
            pointBorderColor: '#fff',
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: 'rgba(250, 204, 21, 1)',
            borderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6
        }]
    };

    const config = {
        type: 'radar',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    angleLines: { color: 'rgba(0,0,0,0.05)' },
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    pointLabels: {
                        font: { size: 12, family: "'Inter', 'sans-serif'", weight: 'bold' },
                        color: '#475569'
                    },
                    ticks: {
                        min: 0, max: 10, stepSize: 2, display: false
                    }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(8, 51, 68, 0.9)',
                    titleFont: { size: 13 },
                    bodyFont: { size: 14, weight: 'bold' },
                    padding: 10,
                    cornerRadius: 8,
                    displayColors: false
                }
            }
        },
    };

    new Chart(ctx, config);
});
</script>

<style>
@media print {
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    nav, header, aside, .sidebar-open-btn { display: none !important; }
    .max-w-5xl { max-width: 100% !important; margin: 0 !important; width: 100% !important; padding: 0 !important; }
    
    /* Perfect scale trick to fit on A4 without overlap */
    .growth-card-container {
        zoom: 0.82;
        margin-top: 0 !important;
    }

    html, body {
        height: 100%;
        background: white !important;
        padding: 0 !important; 
    }
    @page { 
        margin: 0.5cm; /* Safe breathable margin */
        size: A4 portrait;
    }
}
</style>
@endsection
