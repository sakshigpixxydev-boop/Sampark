@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Guardians</h1>
                <p class="text-sm text-gray-600 mt-1">Manage guardian information</p>
            </div>
            <div class="flex gap-3">
                @if(auth()->user()->hasPermission(10213))
                <a href="{{ route('guardians.messages.index') }}"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-4 py-2 rounded-lg transition border border-gray-300">
                    Message History
                </a>
                @endif
                <!-- <a href="{{ route('guardians.create') }}"
                    class="bg-primary-500 hover:bg-primary-600 text-white font-medium px-4 py-2 rounded-lg transition">
                    Add New Guardian
                </a> -->
            </div>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
                {{ session('error') }}
            </div>
        @endif

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <form method="GET" action="{{ route('guardians.index') }}" id="filter-form" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Name, email, or phone..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>

                    <!-- School Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">School</label>
                        <select name="school_id"
                            class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">All Schools</option>
                            @foreach($schools as $school)
                                <option value="{{ $school->school_id }}" {{ request('school_id') == $school->school_id ? 'selected' : '' }}>
                                    {{ $school->schoolname }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Student Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Student</label>
                        <select name="student_id"
                            class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">All Students</option>
                            @foreach($students as $student)
                                <option value="{{ $student->student_id }}" {{ request('student_id') == $student->student_id ? 'selected' : '' }}>
                                    {{ $student->fname }} {{ $student->mname }} {{ $student->lname }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Relation Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Relation</label>
                        <select name="relation_id"
                            class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">All Relations</option>
                            @foreach($relations as $relation)
                                <option value="{{ $relation->relation_id }}" {{ request('relation_id') == $relation->relation_id ? 'selected' : '' }}>
                                    {{ $relation->relation }}
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
                    <a href="{{ route('guardians.index') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                        Clear Filters
                    </a>
                </div>
            </form>
        </div>

        <!-- Guardians Table -->
        <form id="bulk-message-form" method="POST" action="{{ route('guardians.bulk-message') }}">
            @csrf
            <div id="ajax-grid">
                @include('guardians.partials.guardian_grid')
            </div>
        </form>
    </div>

    <!-- Hidden form for deletion -->
    <form id="delete-form" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    <script>
        function confirmDelete(url) {
            if (confirm('Are you sure you want to delete this guardian?')) {
                const form = document.getElementById('delete-form');
                form.action = url;
                form.submit();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // In-memory Selection Storage
            let selectedIds = new Set();
            let excludedIds = new Set();
            let isSelectAllPages = false;
            const GRID_CONTAINER_ID = 'ajax-grid';
            const STORE_FORM_ID = 'bulk-message-form';

            function updateUI() {
                const currentCheckboxes = document.querySelectorAll('.guardian-checkbox');
                const visibleCb = document.getElementById('select-visible');
                const allPagesCb = document.getElementById('select-all-pages');
                
                if (allPagesCb) allPagesCb.checked = isSelectAllPages;
                
                let matchesOnPage = 0;
                currentCheckboxes.forEach(cb => {
                    const val = String(cb.value).trim();
                    if (isSelectAllPages) {
                        cb.checked = !excludedIds.has(val);
                    } else {
                        cb.checked = selectedIds.has(val);
                    }
                    if (cb.checked) matchesOnPage++;
                });
                
                if (visibleCb) {
                    visibleCb.checked = (currentCheckboxes.length > 0 && matchesOnPage === currentCheckboxes.length);
                }
                updateButtonState();
            }

            function updateButtonState() {
                const hasSelection = isSelectAllPages || selectedIds.size > 0;
                const sendButton = document.getElementById('send-bulk-message');
                
                if (sendButton) {
                    sendButton.disabled = !hasSelection;
                    sendButton.classList.toggle('opacity-50', !hasSelection);
                    sendButton.classList.toggle('cursor-not-allowed', !hasSelection);
                }
            }

            // AJAX Pagination Logic
            async function loadGrid(url) {
                try {
                    const container = document.getElementById(GRID_CONTAINER_ID);
                    container.style.opacity = '0.5';
                    container.style.pointerEvents = 'none';

                    const response = await fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const html = await response.text();
                    container.innerHTML = html;
                    container.style.opacity = '1';
                    container.style.pointerEvents = 'auto';
                    
                    window.history.pushState({}, '', url);
                    updateUI();
                } catch (error) {
                    console.error('Failed to load grid:', error);
                    window.location.href = url;
                }
            }

            document.addEventListener('click', function(e) {
                const link = e.target.closest('.pagination-container a, .page-link');
                if (link && link.href) {
                    e.preventDefault();
                    loadGrid(link.href);
                }
            });

            // Selection Event Listeners
            document.addEventListener('change', function(e) {
                if (e.target.id === 'select-visible') {
                    const currentCheckboxes = document.querySelectorAll('.guardian-checkbox');
                    currentCheckboxes.forEach(cb => {
                        cb.checked = e.target.checked;
                        const val = String(cb.value).trim();
                        if (isSelectAllPages) {
                            if (e.target.checked) excludedIds.delete(val);
                            else excludedIds.add(val);
                        } else {
                            if (e.target.checked) selectedIds.add(val);
                            else selectedIds.delete(val);
                        }
                    });
                    updateUI();
                }

                if (e.target.id === 'select-all-pages') {
                    isSelectAllPages = e.target.checked;
                    selectedIds.clear();
                    excludedIds.clear();
                    updateUI();
                }

                if (e.target.classList.contains('guardian-checkbox')) {
                    const val = String(e.target.value).trim();
                    if (isSelectAllPages) {
                        if (e.target.checked) excludedIds.delete(val);
                        else excludedIds.add(val);
                    } else {
                        if (e.target.checked) selectedIds.add(val);
                        else selectedIds.delete(val);
                    }
                    updateUI();
                }
            });

            // Form Submit Logic
            const storeForm = document.getElementById(STORE_FORM_ID);
            if (storeForm) {
                storeForm.addEventListener('submit', function(e) {
                    storeForm.querySelectorAll('input[type="hidden"][name="selected_guardians[]"], input[type="hidden"][name="excluded_guardians[]"]').forEach(el => el.remove());

                    if (isSelectAllPages) {
                        excludedIds.forEach(id => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'excluded_guardians[]';
                            input.value = id;
                            storeForm.appendChild(input);
                        });
                    } else {
                        selectedIds.forEach(id => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'selected_guardians[]';
                            input.value = id;
                            storeForm.appendChild(input);
                        });
                    }
                });
            }

            const filterForm = document.getElementById('filter-form');
            if (filterForm) {
                filterForm.addEventListener('submit', function() {
                    selectedIds.clear();
                    excludedIds.clear();
                    isSelectAllPages = false;
                });
            }

            // Initial state
            updateUI();
        });
    </script>
@endsection