@extends('layouts.app')

@section('title', 'Create Mail Template')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <x-page-header title="Create Mail Template" subtitle="Create a new system mail template" />

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <form action="{{ route('mail-templates.store') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="identifier" class="block text-sm font-medium text-gray-700 mb-1">Template Identifier <span class="text-red-500">*</span></label>
                        <input type="text" name="identifier" id="identifier" value="{{ old('identifier') }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                            placeholder="e.g., student_welcome">
                        @error('identifier') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mb-2 p-3 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-600">
                    <strong>Dynamic Placeholders:</strong> Use double curly braces to insert dynamic details, e.g., <code>&lcub;&lcub;fname&rcub;&rcub;</code>, <code>&lcub;&lcub;school_name&rcub;&rcub;</code>, <code>&lcub;&lcub;reason&rcub;&rcub;</code>.
                </div>

                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button type="button" onclick="switchLanguageTab('en')" id="tab-btn-en" class="language-tab-btn border-primary-500 text-primary-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            English
                        </button>
                        <button type="button" onclick="switchLanguageTab('hi')" id="tab-btn-hi" class="language-tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Hindi
                        </button>
                        <button type="button" onclick="switchLanguageTab('gu')" id="tab-btn-gu" class="language-tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Gujarati
                        </button>
                    </nav>
                </div>

                <div id="tab-content-en" class="language-tab-content space-y-6">
                    <div>
                        <label for="title_en" class="block text-sm font-medium text-gray-700 mb-1">Email Subject (English) <span class="text-red-500">*</span></label>
                        <input type="text" name="title_en" id="title_en" value="{{ old('title_en') }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                            placeholder="e.g. Welcome to PRAMUKH SANKALP Programme!">
                        @error('title_en') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="content_en" class="block text-sm font-medium text-gray-700 mb-1">Email Content (English) <span class="text-red-500">*</span></label>
                        <x-rich-text-editor name="content_en" id="content_en" value="{{ old('content_en') }}" />
                        @error('content_en') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div id="tab-content-hi" class="language-tab-content space-y-6 hidden">
                    <div>
                        <label for="title_hi" class="block text-sm font-medium text-gray-700 mb-1">Email Subject (Hindi)</label>
                        <input type="text" name="title_hi" id="title_hi" value="{{ old('title_hi') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                            placeholder="Subject line in Hindi">
                        @error('title_hi') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="content_hi" class="block text-sm font-medium text-gray-700 mb-1">Email Content (Hindi)</label>
                        <x-rich-text-editor name="content_hi" id="content_hi" value="{{ old('content_hi') }}" />
                        @error('content_hi') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div id="tab-content-gu" class="language-tab-content space-y-6 hidden">
                    <div>
                        <label for="title_gu" class="block text-sm font-medium text-gray-700 mb-1">Email Subject (Gujarati)</label>
                        <input type="text" name="title_gu" id="title_gu" value="{{ old('title_gu') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                            placeholder="Subject line in Gujarati">
                        @error('title_gu') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="content_gu" class="block text-sm font-medium text-gray-700 mb-1">Email Content (Gujarati)</label>
                        <x-rich-text-editor name="content_gu" id="content_gu" value="{{ old('content_gu') }}" />
                        @error('content_gu') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t">
                    <a href="{{ route('mail-templates.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white font-medium rounded-lg transition shadow-sm">
                        Create Template
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function switchLanguageTab(lang) {
            // Hide all tab contents
            document.querySelectorAll('.language-tab-content').forEach(function(el) {
                el.classList.add('hidden');
            });
            // Show selected tab content
            document.getElementById('tab-content-' + lang).classList.remove('hidden');

            // Reset all tab button styles
            document.querySelectorAll('.language-tab-btn').forEach(function(btn) {
                btn.className = 'language-tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm';
            });
            // Set active tab button style
            document.getElementById('tab-btn-' + lang).className = 'language-tab-btn border-primary-500 text-primary-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm';
        }
    </script>
@endsection
