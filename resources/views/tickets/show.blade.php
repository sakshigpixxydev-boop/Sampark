@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <a href="{{ route('tickets.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center mb-2">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Tickets
            </a>
            <h1 class="text-2xl font-bold text-gray-900">#TKT-{{ str_pad($ticket->ticket_id, 5, '0', STR_PAD_LEFT) }}: {{ $ticket->title }}</h1>
        </div>
        <div class="flex items-center gap-2">
            @php
                $statusClasses = [
                    'Open' => 'bg-yellow-100 text-yellow-800',
                    'In Progress' => 'bg-blue-100 text-blue-800',
                    'Resolved' => 'bg-green-100 text-green-800',
                    'Closed' => 'bg-gray-100 text-gray-800',
                    'Reopen' => 'bg-red-100 text-red-800',
                ];
                $class = $statusClasses[$ticket->status] ?? 'bg-gray-100 text-gray-800';

                $priorityClasses = [
                    'Low' => 'bg-gray-100 text-gray-800 border border-gray-200',
                    'Medium' => 'bg-blue-50 text-blue-700 border border-blue-100',
                    'High' => 'bg-orange-50 text-orange-700 border border-orange-100',
                    'Critical' => 'bg-red-50 text-red-700 border border-red-100',
                ];
                $pClass = $priorityClasses[$ticket->priority] ?? 'bg-gray-100 text-gray-800';
            @endphp
            <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $pClass }} shadow-sm">Priority: {{ $ticket->priority ?? 'Medium' }}</span>
            <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $class }} shadow-sm">Status: {{ $ticket->status }}</span>
        </div>
    </div>

    @if(session('success'))
        <x-alert type="success" :message="session('success')" class="mb-6" />
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6 min-w-0">
            <!-- Ticket Description -->
            <x-card>
                <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Issue Description</h3>
                    <span class="text-xs text-gray-500">Raised {{ $ticket->created_at->diffForHumans() }}</span>
                </div>
                <div class="prose prose-blue max-w-none text-gray-700 leading-relaxed break-words overflow-hidden">
                    {!! $ticket->description !!}
                </div>
                
                @if($ticket->attachment)
                <div class="mt-8 pt-6 border-t border-gray-100">
                    <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                        </svg>
                        Attachment
                    </h4>
                    <a href="{{ asset('storage/' . $ticket->attachment) }}" target="_blank" 
                       class="inline-flex items-center px-4 py-2 border border-blue-200 rounded-lg text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 transition shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        View Attachment
                    </a>
                </div>
                @endif
            </x-card>

            <!-- Activity / Comments section -->
            <!-- Activity & Discussion -->
            <x-card class="mt-6">
                <div class="border-b border-gray-100 pb-4 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Activity & Discussion</h3>
                </div>

                <!-- Comments List -->
                <div class="space-y-6 max-h-[500px] overflow-y-auto pr-2 mb-6">
                    @forelse($ticket->comments as $comment)
                        <div class="flex gap-4 p-4 rounded-lg bg-gray-50 border border-gray-100 hover:border-gray-200 transition">
                            <div class="shrink-0">
                                <div class="w-10 h-10 rounded-full bg-[#00bde0] text-white flex items-center justify-center font-bold text-sm shadow-sm">
                                    {{ substr($comment->user->fname, 0, 1) }}{{ substr($comment->user->lname, 0, 1) }}
                                </div>
                            </div>
                            <div class="flex-grow min-w-0 space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-bold text-gray-900">{{ $comment->user->fname }} {{ $comment->user->lname }}</span>
                                        @php
                                            $roleBadgeColor = [
                                                'Admin' => 'bg-red-50 text-red-700 border-red-100',
                                                'Developer' => 'bg-indigo-50 text-indigo-700 border-indigo-100',
                                                'Initiator' => 'bg-green-50 text-green-700 border-green-100',
                                                'School' => 'bg-blue-50 text-blue-700 border-blue-100',
                                            ];
                                            $rBadge = $roleBadgeColor[optional($comment->user->role)->rolename] ?? 'bg-gray-50 text-gray-700 border-gray-100';
                                        @endphp
                                        <span class="text-[10px] px-2 py-0.5 font-semibold rounded-full border {{ $rBadge }}">
                                            {{ optional($comment->user->role)->rolename ?? 'User' }}
                                        </span>
                                    </div>
                                    <span class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="text-sm text-gray-700 leading-relaxed break-words overflow-hidden">
                                    {!! $comment->comment !!}
                                </div>

                                @if($comment->attachment)
                                    <div class="mt-3 pt-3 border-t border-gray-200/50">
                                        @php
                                            $extension = pathinfo($comment->attachment, PATHINFO_EXTENSION);
                                            $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                        @endphp
                                        @if($isImage)
                                            <div class="max-w-sm rounded-lg overflow-hidden border border-gray-200 bg-white p-1 shadow-sm">
                                                <a href="{{ asset('storage/' . $comment->attachment) }}" target="_blank">
                                                    <img src="{{ asset('storage/' . $comment->attachment) }}" alt="Comment attachment" class="max-h-48 w-full object-cover hover:opacity-90 transition">
                                                </a>
                                            </div>
                                        @else
                                            <a href="{{ asset('storage/' . $comment->attachment) }}" target="_blank" 
                                               class="inline-flex items-center gap-2 px-3 py-1.5 border border-gray-200 rounded-lg text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 transition shadow-sm">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                Download file ({{ strtoupper($extension) }})
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500 text-sm">
                            No comments yet. Start the discussion below.
                        </div>
                    @endforelse
                </div>

                <!-- Add Comment Form -->
                <form action="{{ route('tickets.comments.store', $ticket->ticket_id) }}" method="POST" enctype="multipart/form-data" class="border-t border-gray-100 pt-6">
                    @csrf
                    <div>
                        <label for="comment" class="block text-sm font-semibold text-gray-700 mb-2">Add a comment</label>
                        <x-rich-text-editor name="comment" id="comment" placeholder="Type your comment, description of fix, or question here..." height="150" />
                        @error('comment')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-center gap-2">
                            <label class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50 hover:text-gray-900 transition">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                </svg>
                                <span>Attach screenshot / file</span>
                                <input type="file" name="attachment" class="hidden" onchange="document.getElementById('file-chosen').textContent = this.files[0] ? this.files[0].name : ''">
                            </label>
                            <span id="file-chosen" class="text-xs text-gray-500 truncate max-w-[200px]"></span>
                        </div>

                        <button type="submit" class="bg-[#00bde0] hover:bg-[#0099b8] text-white px-5 py-2 rounded-lg text-sm font-bold transition shadow-sm">
                            Post Comment
                        </button>
                    </div>
                </form>
            </x-card>
        </div>

        <div class="space-y-6">
            <!-- Ticket Info Card -->
            <x-card class="bg-gray-50">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4 border-b border-gray-200 pb-2">Ticket Details</h3>
                
                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-gray-400">School Name</p>
                        <p class="text-sm font-semibold text-gray-900">{{ optional($ticket->school)->schoolname ?? 'General Issue (No School)' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Raised By</p>
                        <p class="text-sm font-medium text-gray-800">{{ optional($ticket->creator)->fname }} {{ optional($ticket->creator)->lname }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Category / Type</p>
                        <p class="text-sm font-medium text-gray-800">
                            <span class="px-2 py-0.5 bg-gray-200 rounded text-xs">{{ optional($ticket->category)->name ?? $ticket->ticket_type }}</span>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Priority</p>
                        <p class="text-sm font-medium text-gray-800 mt-0.5">
                            <span class="px-2 py-0.5 rounded text-xs border {{ $pClass }}">{{ $ticket->priority ?? 'Medium' }}</span>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Assigned To</p>
                        <div class="mt-1">
                            @if($ticket->assignee)
                                <div class="flex items-center">
                                    <div class="w-6 h-6 bg-[#00bde0] rounded-full flex items-center justify-center text-white text-[10px] mr-2">
                                        {{ substr($ticket->assignee->fname, 0, 1) }}
                                    </div>
                                    <span class="text-sm font-medium text-gray-900">{{ $ticket->assignee->fname }} {{ $ticket->assignee->lname }}</span>
                                </div>
                            @else
                                <span class="text-sm text-gray-400 italic">Unassigned</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Raised On</p>
                        <p class="text-sm font-medium text-gray-800">{{ $ticket->created_at->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}</p>
                    </div>
                </div>
            </x-card>

            <!-- Status & Assignee Controls -->
            @php
                $canUpdate = auth()->user()->hasPermission(10803) && (
                    auth()->user()->hasRole('Admin') || 
                    auth()->user()->hasRole('Developer') || 
                    auth()->user()->hasRole('Initiator')
                );
            @endphp

            @if($canUpdate)
            <x-card>
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">Manage Ticket</h3>
                
                <form action="{{ route('tickets.update-status', $ticket->ticket_id) }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Status</label>
                            <select name="status" class="searchable-select w-full border-gray-300 rounded-lg text-sm focus:ring-[#00bde0] focus:border-[#00bde0] transition-shadow">
                                @foreach(['Open', 'In Progress', 'Resolved', 'Closed', 'Reopen'] as $status)
                                    <option value="{{ $status }}" {{ $ticket->status == $status ? 'selected' : '' }}>{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Priority</label>
                            <select name="priority" class="searchable-select w-full border-gray-300 rounded-lg text-sm focus:ring-[#00bde0] focus:border-[#00bde0] transition-shadow">
                                @foreach(['Low', 'Medium', 'High', 'Critical'] as $priority)
                                    <option value="{{ $priority }}" {{ $ticket->priority == $priority ? 'selected' : '' }}>{{ $priority }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Assignee</label>
                            <select name="assigned_to" class="searchable-select w-full border-gray-300 rounded-lg text-sm focus:ring-[#00bde0] focus:border-[#00bde0] transition-shadow">
                                <option value="">Unassigned</option>
                                @foreach($assignees as $assignee)
                                    <option value="{{ $assignee->sysuser_id }}" {{ $ticket->assigned_to == $assignee->sysuser_id ? 'selected' : '' }}>
                                        {{ $assignee->fname }} {{ $assignee->lname }} ({{ optional($assignee->role)->rolename }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="w-full bg-[#00bde0] hover:bg-[#0099b8] text-white py-2.5 rounded-lg text-sm font-bold transition shadow-sm mt-2">
                            Save Changes
                        </button>
                    </div>
                </form>
            </x-card>
            @endif
        </div>
    </div>
</div>
@endsection
