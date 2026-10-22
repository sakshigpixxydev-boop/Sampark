@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Student Announcements</h1>
                <p class="text-sm text-gray-600 mt-1">View history of announcements sent to students</p>
            </div>
            <div class="flex gap-3">
                @if(Auth::user()->hasPermission(10223))
                    <a href="{{ route('students.announcements.templates.index') }}"
                        class="bg-primary-500 hover:bg-primary-600 text-white font-medium px-4 py-2 rounded-lg transition">
                        Manage Templates
                    </a>
                @endif
                <a href="{{ route('students.announcements.create') }}"
                    class="bg-primary-500 hover:bg-primary-600 text-white font-medium px-4 py-2 rounded-lg transition">
                    Send New Announcement
                </a>
                <a href="{{ route('students.index') }}"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                    Back to Students
                </a>
            </div>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
                {{ session('success') }}
            </div>
        @endif

        <!-- Announcements Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Subject / Message
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Progress
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($announcements as $announcement)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $announcement->created_at->format('d M Y, h:i A') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $announcement->communication_type === 'Email' ? 'bg-blue-100 text-blue-800' : 
                                           ($announcement->communication_type === 'WhatsApp' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ $announcement->communication_type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    @if($announcement->communication_type === 'Email')
                                        <div class="font-medium">{{ $announcement->subject }}</div>
                                    @endif
                                    <div class="text-gray-500 truncate max-w-xs">{{ Str::limit(strip_tags($announcement->message), 50) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-full bg-gray-200 rounded-full h-2 mr-3 max-w-[100px]">
                                            @php
                                                $percentage = $announcement->queue_count > 0 ? ($announcement->sent_count / $announcement->queue_count) * 100 : 0;
                                            @endphp
                                            <div class="bg-primary-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-600">{{ $announcement->sent_count }}/{{ $announcement->queue_count }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($announcement->status === 'DONE')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Completed
                                        </span>
                                    @elseif($announcement->status === 'PENDING')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Sending...
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Ready
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('students.announcements.show', $announcement->id) }}"
                                            class="text-primary-500 hover:text-primary-600">
                                            View
                                        </a>
                                        <form action="{{ route('students.announcements.reuse', $announcement->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-gray-600 hover:text-gray-900">
                                                Reuse
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    No announcements found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($announcements->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $announcements->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
