@extends('layouts.app')

@section('title', 'Create Agreement Template')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <x-page-header title="Create Agreement Template" subtitle="Create a new terms and conditions template" />

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <form action="{{ route('agreement-templates.store') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Template Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug <span class="text-red-500">*</span></label>
                        <input type="text" name="slug" id="slug" value="{{ old('slug') }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                            placeholder="e.g., student_registration">
                        @error('slug') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="md:col-span-2">
                        <label for="content_en" class="block text-sm font-medium text-gray-700 mb-1">Content (English) <span class="text-red-500">*</span></label>
                        <x-rich-text-editor name="content_en" id="content_en" value="{{ old('content_en') }}" />
                        @error('content_en') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="content_hi" class="block text-sm font-medium text-gray-700 mb-1">Content (Hindi)</label>
                        <x-rich-text-editor name="content_hi" id="content_hi" value="{{ old('content_hi') }}" />
                        @error('content_hi') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="content_gu" class="block text-sm font-medium text-gray-700 mb-1">Content (Gujarati)</label>
                        <x-rich-text-editor name="content_gu" id="content_gu" value="{{ old('content_gu') }}" />
                        @error('content_gu') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <input type="checkbox" name="is_active" id="is_active" checked
                        class="h-4 w-4 rounded border-gray-300 text-primary-500 focus:ring-primary-500">
                    <label for="is_active" class="text-sm font-medium text-gray-700">Is Active?</label>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t">
                    <a href="{{ route('agreement-templates.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white font-medium rounded-lg transition shadow-sm">
                        Create Template
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
