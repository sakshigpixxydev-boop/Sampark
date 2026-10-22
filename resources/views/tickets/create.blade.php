@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ ticketCategoryId: '{{ old('ticket_category_id') }}', priority: '{{ old('priority', 'Medium') }}' }">
    <div class="mb-6">
        <a href="{{ route('tickets.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Tickets
        </a>
        <h1 class="text-2xl font-semibold text-gray-900 mt-2">Raise Support Ticket</h1>
        <p class="text-sm text-gray-600 mt-1">Provide details about the issue you're facing</p>
    </div>

    <x-card>
        <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="space-y-6">
                @if(auth()->user()->hasRole('School') && count($schools) == 1)
                <input type="hidden" name="school_id" value="{{ $schools[0]->school_id }}">
                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Raising Ticket For</p>
                    <p class="text-sm font-medium text-gray-900 mt-1">{{ $schools[0]->schoolname }}</p>
                </div>
                @else
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Select School
                        @if(auth()->user()->hasRole('School'))
                            <span class="text-red-500">*</span>
                        @else
                            <span class="text-gray-400 font-normal">(Optional - Leave blank for General Issue / Tasks)</span>
                        @endif
                    </label>
                    <select name="school_id" {{ auth()->user()->hasRole('School') ? 'required' : '' }} class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('school_id') border-red-500 @enderror">
                        <option value="">-- General Issue / Not School Specific --</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->school_id }}" {{ old('school_id') == $school->school_id ? 'selected' : '' }}>{{ $school->schoolname }}</option>
                        @endforeach
                    </select>
                    @error('school_id')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ticket Category <span class="text-red-500">*</span></label>
                    @if($categories->isEmpty())
                        <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg flex items-center gap-3 text-amber-800">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium">No ticket categories available.</p>
                                <p class="text-xs mt-0.5">Please <a href="{{ route('ticket-categories.index') }}" class="underline font-bold hover:text-amber-900">add a category</a> before raising a ticket.</p>
                            </div>
                        </div>
                    @else
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            @foreach($categories as $category)
                            <label class="relative flex flex-col items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition border-gray-200"
                                    :class="ticketCategoryId == '{{ $category->id }}' ? 'border-primary-500 bg-blue-50 ring-1 ring-primary-500' : 'border-gray-200'">
                                <input type="radio" name="ticket_category_id" value="{{ $category->id }}" 
                                    class="sr-only" required
                                    @change="ticketCategoryId = '{{ $category->id }}'"
                                    {{ old('ticket_category_id') == $category->id ? 'checked' : '' }}>
                                <span class="text-sm font-medium" :class="ticketCategoryId == '{{ $category->id }}' ? 'text-blue-700' : 'text-gray-900'">{{ $category->name }}</span>
                            </label>
                            @endforeach
                        </div>
                    @endif
                    @error('ticket_category_id')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Priority <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach(['Low', 'Medium', 'High', 'Critical'] as $p)
                        <label class="relative flex flex-col items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition border-gray-200"
                                :class="priority == '{{ $p }}' ? 'border-primary-500 bg-blue-50 ring-1 ring-primary-500' : 'border-gray-200'">
                            <input type="radio" name="priority" value="{{ $p }}" 
                                class="sr-only" required
                                @change="priority = '{{ $p }}'"
                                {{ old('priority', 'Medium') == $p ? 'checked' : '' }}>
                            <span class="text-sm font-medium" :class="priority == '{{ $p }}' ? 'text-blue-700' : 'text-gray-900'">{{ $p }}</span>
                        </label>
                        @endforeach
                    </div>
                    @error('priority')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <x-input label="Ticket Title" name="title" value="{{ old('title') }}" required placeholder="Brief summary of the issue" :error="$errors->first('title')" />

                <div class="space-y-1">
                    <label for="description" class="block text-sm font-medium text-gray-700">Detailed Description <span class="text-red-500">*</span></label>
                    <x-rich-text-editor name="description" id="description" value="{!! old('description') !!}" placeholder="Describe the problem in detail..." />
                    @error('description')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Attachment (Optional)</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-primary-500 transition-colors">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label for="attachment" class="relative cursor-pointer bg-white rounded-md font-medium text-primary-500 hover:text-primary-600 focus-within:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                    <span>Upload a file</span>
                                    <input id="attachment" name="attachment" type="file" class="sr-only">
                                </label>
                                <p class="pl-1 text-gray-500">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-400">PNG, JPG, PDF, DOC up to 5MB</p>
                        </div>
                    </div>
                    @error('attachment')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-6 border-t border-gray-200 flex justify-end gap-3">
                    <a href="{{ route('tickets.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-medium text-sm">Cancel</a>
                    <button type="submit" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-lg transition font-medium text-sm shadow-sm">Submit Ticket</button>
                </div>
            </div>
        </form>
    </x-card>
</div>
@endsection
