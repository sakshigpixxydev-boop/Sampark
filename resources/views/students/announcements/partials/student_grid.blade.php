<div id="grid-container" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="p-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
        <div class="flex items-center gap-6">
            <div class="flex items-center">
                <input type="checkbox" id="select-visible" class="h-4 w-4 text-primary-500 focus:ring-primary-500 border-gray-300 rounded mr-2">
                <span class="text-sm font-medium text-gray-700">Select Visible ({{ $students->count() }})</span>
            </div>
            <div class="flex items-center">
                <input type="checkbox" id="select-all-pages" name="select_all_pages" value="1" class="h-4 w-4 text-primary-500 focus:ring-primary-500 border-gray-300 rounded mr-2" {{ request('select_all_pages') ? 'checked' : '' }}>
                <span class="text-sm font-medium text-gray-700">Select All (Across All Pages: {{ $students->total() }})</span>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-sm text-gray-600 font-medium">Total Filtered: {{ $students->total() }}</span>
            <button type="submit" id="send-btn" disabled
                class="bg-primary-500 hover:bg-primary-600 text-white font-medium px-6 py-2 rounded-lg transition opacity-50 cursor-not-allowed hidden md:inline-flex">
                Send Announcement
            </button>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left w-10"></th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">School</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($students as $student)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <input type="checkbox" name="selected_students[]" value="{{ $student->student_id }}" 
                                class="student-checkbox h-4 w-4 text-primary-500 border-gray-300 rounded focus:ring-primary-500">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-full bg-primary-500 bg-opacity-10 flex items-center justify-center mr-3">
                                    <span class="text-primary-500 text-sm font-medium">
                                        {{ substr($student->fname, 0, 1) }}{{ substr($student->lname, 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $student->fname }} {{ $student->lname }}</div>
                                    <div class="text-xs text-gray-500">ID: {{ $student->student_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $student->school->schoolname ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($student->contact_no)
                                <div class="text-sm text-gray-900 flex items-center gap-2 group"><x-phone-link :number="$student->contact_no" /></div>
                            @endif
                            @if($student->email)
                                <div class="text-sm text-gray-500">{{ $student->email }}</div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                            No students found with the applied filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t border-gray-200 flex justify-between items-center sm:hidden">
        <button type="submit" id="send-btn-mobile" disabled
            class="w-full bg-primary-500 hover:bg-primary-600 text-white font-medium px-6 py-3 rounded-lg transition opacity-50 cursor-not-allowed">
            Send Announcement
        </button>
    </div>

    @if($students->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 pagination-container">
            {{ $students->links() }}
        </div>
    @endif
</div>
