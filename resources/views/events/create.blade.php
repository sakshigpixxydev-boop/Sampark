@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Add New Event</h1>
        <p class="text-sm text-gray-600 mt-1">Fill in the details for the new event</p>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('events.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Template Selection -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Use Template (Optional)</label>
                    <select id="template_id" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" onchange="window.location.href = '{{ route('events.create') }}?template_id=' + this.value">
                        <option value="">No Template</option>
                        @foreach($templates as $template)
                            <option value="{{ $template->id }}" {{ ($selectedTemplate && $selectedTemplate->id == $template->id) ? 'selected' : '' }}>
                                {{ $template->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Event Title -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Event Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $selectedTemplate?->title ?? ($reusedEvent->title ?? '')) }}" required maxlength="255"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>

                <!-- Notification Settings -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Send Notification To <span class="text-red-500">*</span></label>
                    <select name="send_to" required class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="All" {{ old('send_to', $reusedEvent->send_to ?? 'All') == 'All' ? 'selected' : '' }}>All</option>
                        <option value="Schools" {{ old('send_to', $reusedEvent->send_to ?? '') == 'Schools' ? 'selected' : '' }}>School Admins</option>
                        <option value="Students" {{ old('send_to', $reusedEvent->send_to ?? '') == 'Students' ? 'selected' : '' }}>Students</option>
                        <option value="Guardians" {{ old('send_to', $reusedEvent->send_to ?? '') == 'Guardians' ? 'selected' : '' }}>Guardians</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Communication Type <span class="text-red-500">*</span></label>
                    <select name="communication_type" required class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="Notification" {{ old('communication_type', $reusedEvent->communication_type ?? 'Notification') == 'Notification' ? 'selected' : '' }}>In-App Notification</option>
                        <option value="Email" {{ old('communication_type', $reusedEvent->communication_type ?? '') == 'Email' ? 'selected' : '' }}>Email</option>
                        <option value="Both" {{ old('communication_type', $reusedEvent->communication_type ?? '') == 'Both' ? 'selected' : '' }}>Both</option>
                    </select>
                    <p class="text-[10px] text-gray-500 mt-1">Note: Emails will be sent to the targeted audience based on filters below.</p>
                </div>

                <!-- Area Filters -->
                <div class="md:col-span-2 border-t pt-4 mt-2">
                    <h3 class="text-sm font-semibold text-gray-800 mb-4">Target Audience / Area Filters</h3>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                    <select name="state_id" id="state_id" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="">All States</option>
                        @foreach($states as $state)
                            <option value="{{ $state->state_id }}" {{ old('state_id', $reusedEvent->state_id ?? '') == $state->state_id ? 'selected' : '' }}>
                                {{ $state->sname }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">District</label>
                    <select name="dist_id" id="dist_id" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="">All Districts</option>
                        @foreach($districts as $dist)
                            <option value="{{ $dist->dist_id }}" {{ old('dist_id', $reusedEvent->dist_id ?? '') == $dist->dist_id ? 'selected' : '' }}>
                                {{ $dist->dname }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Taluka</label>
                    <select name="taluka_id" id="taluka_id" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="">All Talukas</option>
                        @foreach($talukos as $taluko)
                            <option value="{{ $taluko->takula_id }}" {{ old('taluka_id', $reusedEvent->taluka_id ?? '') == $taluko->takula_id ? 'selected' : '' }}>
                                {{ $taluko->tname }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- School Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">School (Specific)</label>
                    <select name="school_id" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="">All Schools in selected area</option>
                        @php 
                            $default_school_id = old('school_id', ($selectedTemplate?->filters['school_id'] ?? ($reusedEvent->school_id ?? '')));
                        @endphp
                        @foreach($schools as $school)
                            <option value="{{ $school->school_id }}" {{ $default_school_id == $school->school_id ? 'selected' : '' }}>
                                {{ $school->schoolname }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Event Dates -->
                <div class="border-t md:col-span-2 pt-4"></div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Event Date <span class="text-red-500">*</span></label>
                    <input type="text" name="event_date" value="{{ old('event_date', $reusedEvent?->event_date?->format('d/m/Y') ?? '') }}" required
                           class="date-picker w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" placeholder="DD/MM/YYYY">
                </div>

                <!-- Reminder Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reminder Date (Optional)</label>
                    <input type="text" name="reminder_date" value="{{ old('reminder_date', $reusedEvent?->reminder_date?->format('d/m/Y') ?? '') }}"
                           class="date-picker w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" placeholder="DD/MM/YYYY">
                </div>

                <!-- Photo Upload -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Event Image (Max 2MB)</label>
                    <div class="mt-1 flex flex-col items-center justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-primary-500 transition-colors">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" id="upload-icon" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label class="relative cursor-pointer bg-white rounded-md font-medium text-primary-500 hover:text-primary-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-500">
                                    <span>Upload a file</span>
                                    <input name="photo" type="file" id="photo-input" class="sr-only" accept="image/*">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                        </div>
                        <div id="file-preview" class="hidden mt-4 text-center">
                            <p id="file-name" class="text-sm text-gray-600 font-medium"></p>
                            <button type="button" onclick="removeFile()" class="mt-2 text-xs text-red-600 hover:text-red-800 font-medium">Remove</button>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <x-rich-text-editor name="notes" :value="old('notes', $selectedTemplate?->content ?? ($reusedEvent->notes ?? ''))" />
                </div>

                <!-- Feedback -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Feedback (Optional)</label>
                    <textarea name="feedback" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">{{ old('feedback', $reusedEvent->feedback ?? '') }}</textarea>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <a href="{{ route('events.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white font-medium rounded-lg transition shadow-sm">
                    Create Event
                </button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const photoInput = document.getElementById('photo-input');
        const filePreview = document.getElementById('file-preview');
        const fileNameDisplay = document.getElementById('file-name');
        const uploadIcon = document.getElementById('upload-icon');
        const uploadArea = photoInput.closest('.flex.flex-col.items-center.justify-center');

        function previewFile() {
            if (photoInput.files && photoInput.files[0]) {
                const file = photoInput.files[0];
                const fileSize = file.size / 1024 / 1024; // in MB
                
                if (fileSize > 2) {
                    alert('The uploaded file is too large. Internal limit is 2MB.');
                    removeFile();
                    return;
                }

                fileNameDisplay.textContent = file.name;
                filePreview.classList.remove('hidden');
                uploadArea.querySelector('.space-y-1').classList.add('hidden'); // Hide the upload prompt
            }
        }

        function removeFile() {
            photoInput.value = '';
            filePreview.classList.add('hidden');
            uploadArea.querySelector('.space-y-1').classList.remove('hidden'); // Show the upload prompt
        }

        photoInput.addEventListener('change', previewFile);

        // Initial check if a file was previously selected (e.g., on validation error)
        if (photoInput.files && photoInput.files[0]) {
            previewFile();
        }

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
                updateSelect(distSelect, [], null, 'All Districts');
                updateSelect(talukaSelect, [], null, 'All Talukas');
                return;
            }

            const res = await fetch(`/locations/api/districts/${stateId}`);
            const data = await res.json();
            updateSelect(distSelect, data.map(d => ({ value: d.dist_id, text: d.dname })), selectedId, 'All Districts');
        }

        async function loadTalukos(distId, selectedId = null) {
            if (!distId) {
                updateSelect(talukaSelect, [], null, 'All Talukas');
                return;
            }

            const res = await fetch(`/locations/api/talukos/${distId}`);
            const data = await res.json();
            updateSelect(talukaSelect, data.map(t => ({ value: t.takula_id, text: t.tname })), selectedId, 'All Talukas');
        }

        async function handleDistrictChange(distId, skipTalukaLoad = false) {
            if (!distId) {
                updateSelect(talukaSelect, [], null, 'All Talukas');
                return;
            }
            const res = await fetch(`/locations/api/district-details/${distId}`);
            const district = await res.json();
            if (district && district.state_id && stateSelect.value != district.state_id) {
                if (stateSelect.tomselect) stateSelect.tomselect.addItem(district.state_id);
                else stateSelect.value = district.state_id;
            }
            if (!skipTalukaLoad) {
                loadTalukos(distId);
            }
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
                    await handleDistrictChange(taluka.dist_id, true); // Load districts, but don't re-load talukas
                    if (distSelect.tomselect) distSelect.tomselect.addItem(taluka.dist_id);
                    else distSelect.value = taluka.dist_id;
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

        // The backend already handles initial population of Districts and Talukos via blade.
        // There is no need to fire the onchange events during page load as it would
        // overwrite the pre-selected values with empty selections.
    });
</script>
@endpush
@endsection
