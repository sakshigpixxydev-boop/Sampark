@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Announcement Details</h1>
                <p class="text-sm text-gray-600 mt-1">Sent on {{ $announcement->created_at->format('d M Y, h:i A') }}</p>
            </div>
            <div class="flex gap-3">
                <form action="{{ route('students.announcements.reuse', $announcement->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-primary-500 hover:bg-primary-600 text-white font-medium px-4 py-2 rounded-lg transition">
                        Reuse Configuration
                    </button>
                </form>
                <a href="{{ route('students.announcements.index') }}"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                    Back to History
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Details Panel -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Configuration</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase">Communication Type</label>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $announcement->communication_type === 'Email' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                {{ $announcement->communication_type }}
                            </span>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase">Template Used</label>
                            <div class="text-sm text-gray-900">{{ $announcement->template->title ?? 'None / Custom' }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase">Applied Filters</label>
                            <div class="text-sm text-gray-900 space-y-1 mt-1">
                                @if(!empty($filterNames))
                                    @foreach($filterNames as $label => $name)
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">{{ $label }}:</span>
                                            <span class="font-medium">{{ $name }}</span>
                                        </div>
                                    @endforeach
                                @else
                                    <span class="text-gray-500 italic">No filters applied</span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase">Sent By</label>
                            <div class="text-sm text-gray-900">{{ $announcement->created_by }}</div>
                        </div>

                        @if($announcement->attachment)
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase">Attachment</label>
                                <div class="mt-1 flex items-center p-2 bg-gray-50 rounded border border-gray-100">
                                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                    <a href="{{ asset('storage/' . $announcement->attachment) }}" target="_blank" class="text-sm text-primary-600 hover:text-primary-800 font-medium truncate">
                                        {{ basename($announcement->attachment) }}
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Status</h2>
                    @php
                        $sent = $announcement->queue->where('status', 'SENT')->count();
                        $failed = $announcement->queue->where('status', 'FAILED')->count();
                        $pending = $announcement->queue->where('status', 'PENDING')->count();
                        $total = $announcement->queue->count();
                        $percentage = $total > 0 ? ($sent / $total) * 100 : 0;
                    @endphp
                    
                    <div class="space-y-4">
                        <div class="relative pt-1">
                            <div class="flex mb-2 items-center justify-between">
                                <div>
                                    <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-primary-600 bg-primary-200">
                                        Progress
                                    </span>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs font-semibold inline-block text-primary-600">
                                        {{ round($percentage) }}%
                                    </span>
                                </div>
                            </div>
                            <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-primary-200">
                                <div style="width:{{ $percentage }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-primary-500 transition-all duration-500"></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div class="bg-green-50 p-2 rounded">
                                <div class="text-lg font-bold text-green-700">{{ $sent }}</div>
                                <div class="text-xs text-green-600">Sent</div>
                            </div>
                            <div class="bg-red-50 p-2 rounded">
                                <div class="text-lg font-bold text-red-700">{{ $failed }}</div>
                                <div class="text-xs text-red-600">Failed</div>
                            </div>
                            <div class="bg-gray-50 p-2 rounded">
                                <div class="text-lg font-bold text-gray-700">{{ $pending }}</div>
                                <div class="text-xs text-gray-600">Pending</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Panel -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Message Content</h2>
                    
                    <div class="space-y-4">
                        @if($announcement->communication_type === 'Email')
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase">Subject</label>
                                <div class="text-lg font-medium text-gray-900">{{ $announcement->subject }}</div>
                            </div>
                        @endif

                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase">Message Body</label>
                            <div class="mt-2 p-4 bg-gray-50 rounded-lg border border-gray-100 text-gray-800 prose prose-sm max-w-none">
                                {!! nl2br(e($announcement->message)) !!}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-4 border-b border-gray-200 bg-gray-50">
                        <h2 class="text-lg font-medium text-gray-900">Recipients ({{ $total }})</h2>
                    </div>
                    
                    <div class="overflow-x-auto max-h-[500px] overflow-y-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($announcement->queue as $entry)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $entry->student->fname }} {{ $entry->student->lname }}</div>
                                            <div class="text-xs text-gray-500 flex items-center gap-2 group">
                                                @if($entry->student->email)
                                                    {{ $entry->student->email }}
                                                @elseif($entry->student->contact_no)
                                                    <x-phone-link :number="$entry->student->contact_no" />
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($entry->status === 'SENT')
                                                <span class="text-green-600 flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                                    Sent
                                                </span>
                                            @elseif($entry->status === 'FAILED')
                                                <span class="text-red-600 flex items-center" title="{{ $entry->error_message }}">
                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                                                    Failed
                                                </span>
                                            @else
                                                <span class="text-gray-500 flex items-center">
                                                    <svg class="w-4 h-4 mr-1 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                    Pending
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $entry->sent_at ? $entry->sent_at->format('h:i A') : '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
