@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Send Announcement</h1>
                <p class="text-sm text-gray-600 mt-1">Send Email or WhatsApp messages to students</p>
            </div>
            <a href="{{ route('students.announcements.index') }}"
                class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition border border-gray-300">
                Back to History
            </a>
        </div>

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
                {{ session('error') }}
            </div>
        @endif

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <form action="{{ route('students.announcements.create') }}" method="GET" id="filter-form" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <!-- Template -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Template</label>
                        <select name="template_id" id="template_id_select"
                            class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">Choose a Template</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}" {{ request('template_id') == $template->id ? 'selected' : '' }}>
                                    {{ $template->title }} ({{ $template->type }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- School Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">School</label>
                        <select name="school_id" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">All Schools</option>
                            @foreach($schools as $school)
                                <option value="{{ $school->school_id }}" {{ request('school_id') == $school->school_id ? 'selected' : '' }}>
                                    {{ $school->schoolname }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- District Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">District</label>
                        <select name="dist_id" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">All Districts</option>
                            @foreach($districts as $district)
                                <option value="{{ $district->dist_id }}" {{ request('dist_id') == $district->dist_id ? 'selected' : '' }}>
                                    {{ $district->dname }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Language Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Language</label>
                        <select name="language" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">All Languages</option>
                            @foreach($languages as $lang)
                                <option value="{{ $lang->lg_id }}" {{ request('language') == $lang->lg_id ? 'selected' : '' }}>
                                    {{ $lang->languagename }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Initiator Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Initiator</label>
                        <select name="initiator_contact" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">All Initiators</option>
                            @foreach($initiators as $initiator)
                                <option value="{{ $initiator->phone }}" {{ request('initiator_contact') == $initiator->phone ? 'selected' : '' }}>
                                    {{ $initiator->fname }} {{ $initiator->lname }} ({{ $initiator->phone }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit"
                        class="bg-primary-500 hover:bg-primary-600 text-white font-medium px-4 py-2 rounded-lg transition">
                        Apply Filters
                    </button>
                    <a href="{{ route('students.announcements.create') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                        Clear Filters
                    </a>
                </div>
            </form>
        </div>

        <form action="{{ route('students.announcements.store') }}" method="POST" id="announcement-form" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="template_id" value="{{ request('template_id') }}">
            <input type="hidden" name="school_id" value="{{ request('school_id') }}">
            <input type="hidden" name="dist_id" value="{{ request('dist_id') }}">
            <input type="hidden" name="language" value="{{ request('language') }}">
            <input type="hidden" name="initiator_contact" value="{{ request('initiator_contact') }}">
 
            <!-- Message Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Message Content</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Communication Type <span class="text-red-500">*</span></label>
                        <select name="communication_type" id="communication_type" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="Email" {{ (request('communication_type') ?? old('communication_type')) == 'Email' ? 'selected' : '' }}>Email</option>
                            <option value="SMS" {{ (request('communication_type') ?? old('communication_type')) == 'SMS' ? 'selected' : '' }}>SMS</option>
                            <option value="WhatsApp" {{ (request('communication_type') ?? old('communication_type')) == 'WhatsApp' ? 'selected' : '' }}>WhatsApp</option>
                        </select>
                    </div>
                    
                    <div id="subject-container">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subject (Email Only)</label>
                        <input type="text" name="subject" value="{{ old('subject', $selectedTemplate ? $selectedTemplate->title : ($reusedAnnouncement ? $reusedAnnouncement->subject : '')) }}" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" placeholder="Enter message subject">
                    </div>

                    <div id="attachment-container">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Attachment (Email Only)</label>
                        <input type="file" name="attachment" 
                            class="w-full px-3 py-[5px] border border-gray-300 rounded-lg file:mr-4 file:py-1 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 transition">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Message Content <span class="text-red-500">*</span></label>
                    <div id="editor-container">
                        <textarea name="message" id="editor" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" rows="10">{{ old('message', $selectedTemplate ? $selectedTemplate->content : ($reusedAnnouncement ? $reusedAnnouncement->message : '')) }}</textarea>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">You can modify the loaded template content before sending if needed.</p>
                </div>
            </div>

            <!-- Student Selection Grid -->
            <div id="ajax-grid">
                @include('students.announcements.partials.student_grid')
            </div>
        </form>
    </div>

    @push('scripts')
    <x-rich-text-editor :assetsOnly="true" />
    <script>
        $(document).ready(function() {
            // Summernote Handler
            const editorHandler = window.RichTextEditor.setupConditionalEditor('#editor', '#communication_type', {
                activeValues: ['Email'],
                onToggle: function(val, isActive) {
                    const subjectContainer = $('#subject-container');
                    const attachmentContainer = $('#attachment-container');
                    if (isActive) {
                        subjectContainer.css('opacity', '1');
                        subjectContainer.find('input').prop('disabled', false).attr('required', 'required');
                        attachmentContainer.css('opacity', '1');
                        attachmentContainer.find('input').prop('disabled', false);
                    } else {
                        subjectContainer.css('opacity', '0.5');
                        subjectContainer.find('input').prop('disabled', true).removeAttr('required');
                        attachmentContainer.css('opacity', '0.5');
                        attachmentContainer.find('input').prop('disabled', true);
                    }
                }
            });

            // Persistence Logic
            let selectedIds = new Set();
            let excludedIds = new Set();
            let isSelectAllPages = false;
            const GRID_CONTAINER_ID = 'ajax-grid';
            const STORE_FORM_ID = 'announcement-form';

            function updateUI() {
                const currentCheckboxes = document.querySelectorAll('.student-checkbox');
                let matchesOnPage = 0;
                currentCheckboxes.forEach(cb => {
                    const val = String(cb.value).trim();
                    cb.checked = isSelectAllPages ? !excludedIds.has(val) : selectedIds.has(val);
                    if (cb.checked) matchesOnPage++;
                });
                
                const visibleCb = document.getElementById('select-visible');
                const allPagesCb = document.getElementById('select-all-pages');
                if (allPagesCb) allPagesCb.checked = isSelectAllPages;
                if (visibleCb) visibleCb.checked = (currentCheckboxes.length > 0 && matchesOnPage === currentCheckboxes.length);
                
                updateButtonState();
            }

            function updateButtonState() {
                const hasSelection = isSelectAllPages || selectedIds.size > 0;
                const btns = [document.getElementById('send-btn'), document.getElementById('send-btn-mobile')];
                btns.forEach(btn => {
                    if (btn) {
                        btn.disabled = !hasSelection;
                        btn.classList.toggle('opacity-50', !hasSelection);
                        btn.classList.toggle('cursor-not-allowed', !hasSelection);
                        if (btn.id === 'send-btn') {
                            btn.classList.toggle('hidden', !hasSelection && window.innerWidth >= 768);
                        }
                    }
                });
            }

            // AJAX Pagination
            async function loadGrid(url) {
                try {
                    const container = document.getElementById(GRID_CONTAINER_ID);
                    container.style.opacity = '0.5';
                    container.style.pointerEvents = 'none';
                    const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    const html = await response.text();
                    container.innerHTML = html;
                    container.style.opacity = '1';
                    container.style.pointerEvents = 'auto';
                    window.history.pushState({}, '', url);
                    updateUI();
                } catch (error) {
                    console.error('Grid load error:', error);
                }
            }

            // Selection Event Listeners
            $(document).on('change', '#select-visible', function() {
                $('.student-checkbox').each(function() {
                    this.checked = $('#select-visible').is(':checked');
                    const val = String(this.value).trim();
                    if (isSelectAllPages) this.checked ? excludedIds.delete(val) : excludedIds.add(val);
                    else this.checked ? selectedIds.add(val) : selectedIds.delete(val);
                });
                updateUI();
            });

            $(document).on('change', '#select-all-pages', function() {
                isSelectAllPages = this.checked;
                selectedIds.clear();
                excludedIds.clear();
                updateUI();
            });

            $(document).on('change', '.student-checkbox', function() {
                const val = String(this.value).trim();
                if (isSelectAllPages) this.checked ? excludedIds.delete(val) : excludedIds.add(val);
                else this.checked ? selectedIds.add(val) : selectedIds.delete(val);
                updateUI();
            });

            $(document).on('click', '.pagination-container a, .page-link', function(e) {
                if (this.href) {
                    e.preventDefault();
                    loadGrid(this.href);
                }
            });

            // Template AJAX
            const templateSelect = $('#template_id_select');
            const commTypeSelect = $('#communication_type');
            
            templateSelect.on('change', async function() {
                const templateId = this.value;
                if (!templateId) return;

                try {
                    const response = await fetch(`/students/announcements-templates/api/${templateId}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    });
                    const data = await response.json();

                    // Subject
                    $('input[name="subject"]').val(data.title || '');

                    // Communication Type
                    if (commTypeSelect.length) {
                        const newType = data.type === 'Both' ? 'Email' : (data.type === 'Event' ? 'Email' : data.type);
                        commTypeSelect.val(newType).trigger('change');
                        if (commTypeSelect[0].tomselect) commTypeSelect[0].tomselect.sync();
                    }

                    // Editor Content
                    if (editorHandler.isActive()) {
                        $('#editor').summernote('code', data.content || '');
                    } else {
                        $('#editor').val(data.content || '');
                    }

                    // Refresh grid if filters exist in template
                    if (data.filters && Object.keys(data.filters).length > 0) {
                        this.form.submit();
                    }
                } catch (error) {
                    console.error('Template load fail', error);
                }
            });

            // Form Submit - Add IDs
            $('#announcement-form').on('submit', function() {
                $(this).find('input[name="selected_students[]"], input[name="excluded_students[]"]').remove();
                if (isSelectAllPages) {
                    excludedIds.forEach(id => {
                        $('<input>').attr({type: 'hidden', name: 'excluded_students[]', value: id}).appendTo(this);
                    });
                } else {
                    selectedIds.forEach(id => {
                        $('<input>').attr({type: 'hidden', name: 'selected_students[]', value: id}).appendTo(this);
                    });
                }
            });

            // Initial State
            updateUI();
        });
    </script>
    @endpush
@endsection
