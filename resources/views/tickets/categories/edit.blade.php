@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <a href="{{ route('ticket-categories.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Categories
        </a>
        <h1 class="text-2xl font-semibold text-gray-900 mt-2">Edit Ticket Category</h1>
        <p class="text-sm text-gray-600 mt-1">Update the category name and its notification email list</p>
    </div>

    <x-card>
        <form action="{{ route('ticket-categories.update', $ticketCategory->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $ticketCategory->name) }}" required placeholder="e.g. Account, IT, Student" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors @error('name') border-red-300 focus:ring-red-500 @enderror">
                    @error('name')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div id="email-container">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notification Emails <span class="text-red-500">*</span></label>
                    <div class="space-y-3" id="email-list">
                        @php
                            $emails = old('emails', $ticketCategory->emails);
                            if (is_string($emails)) {
                                $emails = array_map('trim', explode(',', $emails));
                            }
                        @endphp

                        @forelse($emails as $email)
                            <div class="flex gap-2 email-row">
                                <input type="email" name="emails[]" value="{{ $email }}" required placeholder="support@example.com" 
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">
                                <button type="button" class="remove-email p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors {{ $loop->first && count($emails) == 1 ? 'invisible' : '' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        @empty
                            <div class="flex gap-2 email-row">
                                <input type="email" name="emails[]" required placeholder="support@example.com" 
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">
                                <span class="p-2 w-9"></span>
                            </div>
                        @endforelse
                    </div>
                    <button type="button" id="add-email" class="mt-3 text-primary-500 hover:text-primary-600 font-medium text-sm flex items-center gap-1 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Add Another Email
                    </button>
                    @error('emails')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                @push('scripts')
                <script>
                    document.getElementById('add-email').addEventListener('click', function() {
                        const list = document.getElementById('email-list');
                        const row = document.createElement('div');
                        row.className = 'flex gap-2 email-row';
                        row.innerHTML = `
                            <input type="email" name="emails[]" required placeholder="support@example.com" 
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">
                            <button type="button" class="remove-email p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        `;
                        list.appendChild(row);
                        updateRemoveButtons();
                    });

                    document.getElementById('email-list').addEventListener('click', function(e) {
                        const btn = e.target.closest('.remove-email');
                        if (btn) {
                            btn.closest('.email-row').remove();
                            updateRemoveButtons();
                        }
                    });

                    function updateRemoveButtons() {
                        const rows = document.querySelectorAll('.email-row');
                        rows.forEach((row, index) => {
                            const btn = row.querySelector('.remove-email');
                            if (btn) {
                                if (rows.length === 1) {
                                    btn.classList.add('invisible');
                                } else {
                                    btn.classList.remove('invisible');
                                }
                            }
                        });
                    }
                </script>
                @endpush

                <div class="pt-6 border-t border-gray-200 flex justify-end gap-3">
                    <a href="{{ route('ticket-categories.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-medium text-sm">Cancel</a>
                    <button type="submit" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-lg transition font-medium text-sm shadow-sm">Update Category</button>
                </div>
            </div>
        </form>
    </x-card>
</div>
@endsection
