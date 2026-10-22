<div id="grid-container" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="p-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
        <div class="flex items-center gap-6">
            <div class="flex items-center">
                <input type="checkbox" id="select-visible" class="h-4 w-4 text-primary-500 focus:ring-primary-500 border-gray-300 rounded mr-2">
                <span class="text-sm font-medium text-gray-700">Select Visible ({{ $guardians->count() }})</span>
            </div>
            <div class="flex items-center">
                <input type="checkbox" id="select-all-pages" name="select_all_pages" value="1" class="h-4 w-4 text-primary-500 focus:ring-primary-500 border-gray-300 rounded mr-2" {{ request('select_all_pages') ? 'checked' : '' }}>
                <span class="text-sm font-medium text-gray-700">Select All (Across All Pages: {{ $guardians->total() }})</span>
            </div>
        </div>
        <div>
            <button type="submit" id="send-bulk-message" 
                class="bg-primary-500 hover:bg-primary-600 text-white font-medium px-4 py-2 rounded-lg transition opacity-50 cursor-not-allowed" disabled>
                Send Bulk Message
            </button>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left w-10"></th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guardian Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Relation</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Education</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($guardians as $guardian)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <input type="checkbox" name="selected_guardians[]" value="{{ $guardian->guardian_id }}" 
                                class="guardian-checkbox h-4 w-4 text-primary-500 focus:ring-primary-500 border-gray-300 rounded">
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-full bg-primary-500 bg-opacity-10 flex items-center justify-center mr-3">
                                    <span class="text-primary-500 text-sm font-medium">
                                        {{ substr($guardian->fname, 0, 1) }}{{ substr($guardian->lname, 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $guardian->fname }} {{ $guardian->mname }} {{ $guardian->lname }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $guardian->relation->relation ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $guardian->student->fname ?? '' }} {{ $guardian->student->lname ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($guardian->phone)<div class="text-sm text-gray-900 flex items-center gap-2 group"><x-masked-phone :number="$guardian->phone" /></div>@endif
                            @if($guardian->email)<div class="text-sm text-gray-500"><x-masked-email :email="$guardian->email" /></div>@endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ Str::limit($guardian->education, 30) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('guardians.edit', $guardian->guardian_id) }}" class="text-primary-500 hover:text-primary-600">Edit</a>
                                <button type="button" onclick="confirmDelete('{{ route('guardians.destroy', $guardian->guardian_id) }}')" class="text-red-600 hover:text-red-800">Delete</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">No guardians found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($guardians->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 pagination-container">
            {{ $guardians->links() }}
        </div>
    @endif
</div>
