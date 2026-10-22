@extends('layouts.app')

@section('content')
@php
use Illuminate\Support\Facades\Storage;
@endphp
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <a href="{{ route('events.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center mb-2">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Events
        </a>
        <h1 class="text-3xl font-bold text-gray-900">{{ $event->title }}</h1>
        <div class="mt-2 flex items-center text-sm text-gray-500">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            {{ $event->event_date->format('F d, Y') }}
        </div>
    </div>

    <x-card>
        <div class="space-y-8">
            @if($event->photo)
            <div class="rounded-xl overflow-hidden shadow-lg border border-gray-100">
                <img src="{{ asset('storage/' . $event->photo) }}" alt="{{ $event->title }}" class="w-full h-auto object-cover max-h-[500px]">
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="md:col-span-2 space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-100 pb-2 mb-4">Event Details</h3>
                        <div class="prose prose-blue max-w-none text-gray-700 leading-relaxed">
                            {!! nl2br(e($event->notes)) !!}
                        </div>
                    </div>

                    @if($event->feedback)
                    <div class="bg-gray-50 rounded-xl p-6 border border-gray-100">
                        <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-3">Feedback / Follow-up</h4>
                        <p class="text-gray-700 italic">"{{ $event->feedback }}"</p>
                    </div>
                    @endif
                </div>

                <div class="space-y-6">
                    <div class="bg-blue-50 rounded-xl p-6 border border-blue-100">
                        <h4 class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-4">Quick Info</h4>
                        <div class="space-y-4">
                            <div>
                                <p class="text-xs text-blue-400">School</p>
                                <p class="text-sm font-semibold text-blue-900">{{ $event->school->schoolname ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-blue-400">Reminder Date</p>
                                <p class="text-sm font-semibold text-blue-900">
                                    {{ $event->reminder_date ? $event->reminder_date->format('d M, Y') : 'No reminder' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-blue-400">Created By</p>
                                <p class="text-sm font-semibold text-blue-900">{{ $event->createdby ?? 'System' }}</p>
                            </div>
                        </div>
                    </div>

                    @if(auth()->user()->hasPermission(10402))
                    <div class="flex flex-col gap-2">
                        <a href="{{ route('events.edit', $event->event_id) }}" 
                           class="w-full bg-primary-500 hover:bg-primary-600 text-white text-center py-2.5 rounded-lg text-sm font-bold transition shadow-sm">
                            Edit Event
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </x-card>
</div>
@endsection
