@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Edit Template</h1>
                <p class="text-sm text-gray-600 mt-1">Update template message and default filters</p>
            </div>
            <a href="{{ route('students.announcements.templates.index') }}"
                class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition border border-gray-300">
                Back to Templates
            </a>
        </div>

        <form action="{{ route('students.announcements.templates.update', $template->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content Area -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Template Details</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Template Title <span class="text-red-500">*</span></label>
                                <input type="text" name="title" value="{{ old('title', $template->title) }}" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" 
                                    placeholder="e.g., Monthly Progress Report">
                                @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Communication Type <span class="text-red-500">*</span></label>
                                <select name="type" id="communication_type_select" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="Email" {{ old('type', $template->type) == 'Email' ? 'selected' : '' }}>Email</option>
                                    <option value="SMS" {{ old('type', $template->type) == 'SMS' ? 'selected' : '' }}>SMS</option>
                                    <option value="WhatsApp" {{ old('type', $template->type) == 'WhatsApp' ? 'selected' : '' }}>WhatsApp</option>
                                    <option value="Event" {{ old('type', $template->type) == 'Event' ? 'selected' : '' }}>Event</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Message Content <span class="text-red-500">*</span></label>
                                <div id="editor-container">
                                    <textarea name="content" id="editor" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" rows="10">{{ old('content', $template->content) }}</textarea>
                                </div>
                                @error('content') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar for Filters -->
                <div class="space-y-6">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Pre-defined Filters</h2>
                        <p class="text-xs text-gray-500 mb-4">Select filters and they will be automatically applied when this template is chosen.</p>
                        
                        @php $filters = $template->filters ?? []; @endphp
                        
                        <div class="space-y-4">
                            <!-- Area Filters (Visible for Events) -->
                            <div id="area-filters" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                                    <select name="state_id" id="state_id" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        <option value="">No Default State</option>
                                        @foreach($states as $state)
                                            <option value="{{ $state->state_id }}" {{ old('state_id', $filters['state_id'] ?? '') == $state->state_id ? 'selected' : '' }}>
                                                {{ $state->sname }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">District</label>
                                    <select name="dist_id" id="dist_id" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        <option value="">No Default District</option>
                                        @foreach($districts as $district)
                                            <option value="{{ $district->dist_id }}" {{ old('dist_id', $filters['dist_id'] ?? '') == $district->dist_id ? 'selected' : '' }}>
                                                {{ $district->dname }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Taluka</label>
                                    <select name="taluka_id" id="taluka_id" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        <option value="">No Default Taluka</option>
                                        @foreach($talukos as $taluko)
                                            <option value="{{ $taluko->takula_id }}" {{ old('taluka_id', $filters['taluka_id'] ?? '') == $taluko->takula_id ? 'selected' : '' }}>
                                                {{ $taluko->tname }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- School Filter -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Default School</label>
                                <select name="school_id" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="">No Default School</option>
                                    @foreach($schools as $school)
                                        <option value="{{ $school->school_id }}" {{ old('school_id', $filters['school_id'] ?? '') == $school->school_id ? 'selected' : '' }}>
                                            {{ $school->schoolname }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Event Specific Filters -->
                            <div id="event-only-filters" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Send To</label>
                                    <select name="send_to" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        <option value="All" {{ old('send_to', $filters['send_to'] ?? '') == 'All' ? 'selected' : '' }}>All</option>
                                        <option value="Schools" {{ old('send_to', $filters['send_to'] ?? '') == 'Schools' ? 'selected' : '' }}>School Admins</option>
                                        <option value="Students" {{ old('send_to', $filters['send_to'] ?? '') == 'Students' ? 'selected' : '' }}>Students</option>
                                        <option value="Guardians" {{ old('send_to', $filters['send_to'] ?? '') == 'Guardians' ? 'selected' : '' }}>Guardians</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Default Communication Type</label>
                                    <select name="communication_type" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        <option value="Notification" {{ old('communication_type', $filters['communication_type'] ?? '') == 'Notification' ? 'selected' : '' }}>In-App Notification</option>
                                        <option value="Email" {{ old('communication_type', $filters['communication_type'] ?? '') == 'Email' ? 'selected' : '' }}>Email</option>
                                        <option value="Both" {{ old('communication_type', $filters['communication_type'] ?? '') == 'Both' ? 'selected' : '' }}>Both</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Announcement Specific Filters -->
                            <div id="announcement-only-filters" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Default Language</label>
                                    <select name="language" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        <option value="">No Default Language</option>
                                        @foreach($languages as $lang)
                                            <option value="{{ $lang->lg_id }}" {{ old('language', $filters['language'] ?? '') == $lang->lg_id ? 'selected' : '' }}>
                                                {{ $lang->languagename }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Default Initiator</label>
                                    <select name="initiator_contact" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        <option value="">No Default Initiator</option>
                                        @foreach($initiators as $initiator)
                                            <option value="{{ $initiator->phone }}" {{ old('initiator_contact', $filters['initiator_contact'] ?? '') == $initiator->phone ? 'selected' : '' }}>
                                                {{ $initiator->fname }} {{ $initiator->lname }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="flex items-center pt-2">
                                <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $template->is_active) ? 'checked' : '' }}
                                    class="h-4 w-4 text-primary-500 border-gray-300 rounded focus:ring-primary-500 mr-2">
                                <label for="is_active" class="text-sm font-medium text-gray-700">Set as Active</label>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit"
                            class="flex-1 bg-primary-500 hover:bg-primary-600 text-white font-medium px-4 py-3 rounded-lg transition shadow-sm">
                            Update Template
                        </button>
                        <a href="{{ route('students.announcements.templates.index') }}"
                            class="flex-1 bg-white hover:bg-gray-50 text-gray-700 font-medium px-4 py-3 rounded-lg transition border border-gray-300 text-center">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <x-rich-text-editor :assetsOnly="true" />

    <script>
        $(document).ready(function() {
            const typeSelect = $('#communication_type_select');
            const stateSelect = document.getElementById('state_id');
            const distSelect = document.getElementById('dist_id');
            const talukaSelect = document.getElementById('taluka_id');

            function updateSelect(el, options, selectedId = null, placeholder = 'Select Option') {
                if (el.tomselect) {
                    el.tomselect.clearOptions();
                    el.tomselect.addOption({ value: '', text: placeholder });
                    options.forEach(opt => {
                        el.tomselect.addOption({ value: opt.value, text: opt.text });
                    });
                    if (selectedId) el.tomselect.addItem(selectedId);
                    else el.tomselect.addItem('');
                } else {
                    el.innerHTML = `<option value="">${placeholder}</option>`;
                    options.forEach(opt => {
                        const option = new Option(opt.text, opt.value);
                        if (selectedId && opt.value == selectedId) option.selected = true;
                        el.add(option);
                    });
                }
            }

            async function loadDistricts(stateId, selectedId = null) {
                if (!stateId) {
                    updateSelect(distSelect, [], null, 'No Default District');
                    updateSelect(talukaSelect, [], null, 'No Default Taluka');
                    return;
                }

                const res = await fetch(`/locations/api/districts/${stateId}`);
                const data = await res.json();
                updateSelect(distSelect, data.map(d => ({ value: d.dist_id, text: d.dname })), selectedId, 'No Default District');
            }

            async function loadTalukos(distId, selectedId = null) {
                if (!distId) {
                    updateSelect(talukaSelect, [], null, 'No Default Taluka');
                    return;
                }

                const res = await fetch(`/locations/api/talukos/${distId}`);
                const data = await res.json();
                updateSelect(talukaSelect, data.map(t => ({ value: t.takula_id, text: t.tname })), selectedId, 'No Default Taluka');
            }

            async function handleDistrictChange(distId) {
                if (!distId) return;
                const res = await fetch(`/locations/api/district-details/${distId}`);
                const district = await res.json();
                if (district && district.state_id && stateSelect.value != district.state_id) {
                    if (stateSelect.tomselect) stateSelect.tomselect.addItem(district.state_id);
                    else stateSelect.value = district.state_id;
                }
                loadTalukos(distId);
            }

            async function handleTalukaChange(talukaId) {
                if (!talukaId) return;
                const res = await fetch(`/locations/api/taluka-details/${talukaId}`);
                const taluka = await res.json();
                if (taluka && taluka.dist_id) {
                    if (distSelect.value != taluka.dist_id) {
                        if (taluka.district && stateSelect.value != taluka.district.state_id) {
                            if (stateSelect.tomselect) stateSelect.tomselect.addItem(taluka.district.state_id);
                            else stateSelect.value = taluka.district.state_id;
                        }
                        await loadDistricts(stateSelect.value, taluka.dist_id);
                    }
                }
            }

            stateSelect.addEventListener('change', function() {
                loadDistricts(this.value);
            });

            distSelect.addEventListener('change', function() {
                handleDistrictChange(this.value);
            });

            talukaSelect.addEventListener('change', function() {
                handleTalukaChange(this.value);
            });

            function toggleFilters(val) {
                const areaFilters = $('#area-filters');
                const eventFilters = $('#event-only-filters');
                const announcementFilters = $('#announcement-only-filters');
                
                if (val === 'Event') {
                    areaFilters.show();
                    eventFilters.show();
                    announcementFilters.hide();
                } else {
                    areaFilters.hide();
                    eventFilters.hide();
                    announcementFilters.show();
                }
            }

            // Trigger initial state
            toggleFilters(typeSelect.val());

            window.RichTextEditor.setupConditionalEditor('#editor', '#communication_type_select', {
                activeValues: ['Email', 'Event'],
                onToggle: function(val, isActive) {
                    toggleFilters(val);
                }
            });
        });
    </script>
    @endpush
@endsection
