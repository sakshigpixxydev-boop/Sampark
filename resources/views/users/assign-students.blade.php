@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Assign Students</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Assign students to {{ $user->fname }} {{ $user->mname }} {{ $user->lname }} ({{ $user->role->rolename ?? 'N/A' }})
                </p>
            </div>
            <a href="{{ route('users.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                Back to Users
            </a>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-4">
        <div class="flex items-center gap-4">
            <div class="flex-1">
                <input 
                    type="text" 
                    id="searchInput" 
                    placeholder="Search by name or email..." 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent"
                    onkeyup="filterStudents()"
                >
            </div>
            <div class="flex gap-2">
                <button type="button" onclick="selectAll()" class="text-sm text-[#00bde0] hover:text-[#0099b8] whitespace-nowrap">
                    Select All
                </button>
                <span class="text-gray-300">|</span>
                <button type="button" onclick="deselectAll()" class="text-sm text-[#00bde0] hover:text-[#0099b8] whitespace-nowrap">
                    Deselect All
                </button>
            </div>
        </div>
    </div>

    <!-- Assignment Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('users.update-students', $user) }}" method="POST">
            @csrf
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-4">
                    Select Students
                </label>
                    
                @if($students->isEmpty())
                    <p class="text-sm text-gray-500 py-4">No students available.</p>
                @else
                    <div class="border border-gray-300 rounded-lg max-h-96 overflow-y-auto">
                        <div class="divide-y divide-gray-200" id="studentList">
                            @foreach($students as $student)
                                <label class="flex items-start px-4 py-3 hover:bg-gray-50 cursor-pointer student-item" 
                                       data-name="{{ strtolower($student->fname . ' ' . $student->mname . ' ' . $student->lname) }}"
                                       data-email="{{ strtolower($student->email ?? '') }}">
                                    <input 
                                        type="checkbox" 
                                        name="students[]" 
                                        value="{{ $student->student_id }}"
                                        {{ in_array($student->student_id, $assignedStudents) ? 'checked' : '' }}
                                        class="h-4 w-4 text-[#00bde0] focus:ring-[#00bde0] border-gray-300 rounded mt-1 student-checkbox"
                                    >
                                    <div class="ml-3 flex-1">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $student->fname }} {{ $student->mname }} {{ $student->lname }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            @if($student->email)
                                                <span><x-masked-email :email="$student->email" /></span>
                                            @endif
                                            @if($student->school)
                                                <span class="ml-2">• School: {{ $student->school->schoolname }}</span>
                                            @endif
                                            @if($student->standard)
                                                <span class="ml-2">• Standard: {{ $student->standard->stdname }}</span>
                                            @endif
                                            @if($student->educationalBoard)
                                                <span class="ml-2">• Board: {{ $student->educationalBoard->boardname }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <p class="mt-2 text-sm text-gray-500" id="resultCount">
                        Showing {{ $students->count() }} students
                    </p>
                @endif
                
                @error('students')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('users.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                    Cancel
                </a>
                <button type="submit" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition">
                    Save Assignments
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function selectAll() {
    document.querySelectorAll('.student-checkbox').forEach(checkbox => {
        const item = checkbox.closest('.student-item');
        if (item.style.display !== 'none') {
            checkbox.checked = true;
        }
    });
}

function deselectAll() {
    document.querySelectorAll('.student-checkbox').forEach(checkbox => {
        const item = checkbox.closest('.student-item');
        if (item.style.display !== 'none') {
            checkbox.checked = false;
        }
    });
}

function filterStudents() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const items = document.querySelectorAll('.student-item');
    let visibleCount = 0;
    
    items.forEach(item => {
        const name = item.dataset.name;
        const email = item.dataset.email;
        
        if (name.includes(searchTerm) || email.includes(searchTerm)) {
            item.style.display = '';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });
    
    document.getElementById('resultCount').textContent = `Showing ${visibleCount} students`;
}
</script>
@endsection
