@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Notifications</h1>
            <p class="text-gray-500 mt-1">Manage your unread updates and announcements</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <form action="{{ route('notifications.index') }}" method="GET">
                <input type="hidden" name="mark_all" value="1">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-all shadow-sm">
                    <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Mark all as read
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded-md shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700 font-medium">
                        {{ session('success') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Sidebar Filter Cards (Optional) -->
        <div class="lg:col-span-1 space-y-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Summary</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center p-3 rounded-xl bg-primary-50">
                        <span class="text-sm font-medium text-primary-900">Total Unread</span>
                        <span class="px-2.5 py-0.5 rounded-full text-sm font-bold bg-primary-500 text-white">
                            {{ $totalUnread }}
                        </span>
                    </div>
                    
                    @php
                        $counts = collect($notifications)->groupBy('type')->map->count();
                    @endphp
                    
                    @foreach(['Blog', 'Event', 'Announcement', 'Ticket'] as $type)
                        <div class="flex justify-between items-center px-3 py-2">
                            <div class="flex items-center gap-3">
                                <span class="w-2 h-2 rounded-full @if($type=='Blog') bg-indigo-400 @elseif($type=='Event') bg-emerald-400 @elseif($type=='Announcement') bg-amber-400 @else bg-rose-400 @endif"></span>
                                <span class="text-sm text-gray-600">{{ $type }}s</span>
                            </div>
                            <span class="text-xs font-semibold text-gray-400">{{ $typeCounts[$type] ?? 0 }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Quick Access Tip -->
            <div class="bg-gradient-to-br from-indigo-500 to-primary-600 rounded-2xl shadow-lg p-6 text-white overflow-hidden relative">
                <div class="relative z-10">
                    <h4 class="font-bold text-lg mb-2">Did you know?</h4>
                    <p class="text-white/80 text-sm leading-relaxed">Viewing any item directly will automatically mark it as read and clear your notification badge.</p>
                </div>
                <div class="absolute -right-4 -bottom-4 opacity-20 transform rotate-12">
                   <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24">
                       <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                   </svg>
                </div>
            </div>
        </div>

        <!-- Main Notifications Feed -->
        <div class="lg:col-span-3">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="divide-y divide-gray-100">
                    @forelse($notifications as $notif)
                        <div class="group p-5 sm:p-6 hover:bg-gray-50 transition-all flex items-start gap-4">
                            <!-- Type Icon -->
                            <div class="flex-shrink-0 mt-1">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center @if($notif['type']=='Blog') bg-indigo-50 text-indigo-600 @elseif($notif['type']=='Event') bg-emerald-50 text-emerald-600 @elseif($notif['type']=='Announcement') bg-amber-50 text-amber-600 @else bg-rose-50 text-rose-600 @endif">
                                    @if($notif['type']=='Blog')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                                    @elseif($notif['type']=='Event')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    @elseif($notif['type']=='Announcement')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                                    @else
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5z"></path></svg>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="text-xs font-bold uppercase tracking-wider @if($notif['type']=='Blog') text-indigo-500 @elseif($notif['type']=='Event') text-emerald-500 @elseif($notif['type']=='Announcement') text-amber-500 @else text-rose-500 @endif">{{ $notif['type'] }}</span>
                                        <h4 class="text-base font-semibold text-gray-900 group-hover:text-primary-600 transition-colors mt-0.5 truncate pr-4">
                                            {{ $notif['title'] }}
                                        </h4>
                                    </div>
                                    <span class="text-xs text-gray-400 whitespace-nowrap">{{ $notif['date']->diffForHumans() }}</span>
                                </div>
                                
                                <div class="mt-3 flex items-center gap-3">
                                    <a href="{{ $notif['link'] }}" class="inline-flex items-center px-4 py-1.5 bg-gray-900 text-white text-xs font-semibold rounded-lg hover:bg-primary-600 transition-colors shadow-sm">
                                        View Details
                                    </a>
                                    <span class="inline-flex items-center px-2 py-1 rounded text-[10px] font-bold bg-blue-50 text-blue-600 border border-blue-100">
                                        New
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-20 text-center">
                            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-50 mb-6">
                                <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">All clear!</h3>
                            <p class="text-gray-500 max-w-sm mx-auto">You've caught up with all your notifications. There are no new items to review at this time.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        <!-- Pagination -->
        @if($notifications->hasPages())
            <div class="mt-8">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
