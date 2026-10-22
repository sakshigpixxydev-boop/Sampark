@extends('layouts.app')

@section('title', 'Bulk Upload Students')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Bulk Upload Students</h2>
                <p class="mt-1 text-sm text-gray-600">Upload multiple students via CSV file</p>
            </div>
        </div>

        <!-- Instructions Card -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <h3 class="text-sm font-medium text-blue-900 mb-2">CSV Upload Instructions</h3>
            <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                <li>Maximum 500 students per upload.</li>
                <li>Ensure the sheet contains legacy student records formatted properly.</li>
                <li>Email is required for every student.</li>
                <li>Enrolment No will be automatically generated if not present.</li>
                <li>Date format: DD-MM-YYYY (e.g., 15-01-2000)</li>
            </ul>
        </div>

        <!-- Upload Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Upload Errors</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('migrated-students.upload.process') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-6">
                    <label for="csv_file" class="block text-sm font-semibold text-gray-700 mb-2">
                        Upload File (CSV or Excel) <span class="text-red-500">*</span>
                    </label>
                    <div class="flex justify-center px-6 pt-8 pb-8 border-2 border-gray-300 border-dashed rounded-lg hover:border-[#00bde0] transition-colors bg-gray-50">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                    viewBox="0 0 48 48" aria-hidden="true">
                                    <path
                                        d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="csv_file"
                                        class="relative cursor-pointer bg-white rounded-md font-medium text-[#00bde0] hover:text-[#0099b8] focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-[#00bde0]">
                                        <span>Upload a file</span>
                                        <input id="csv_file" name="csv_file" type="file" accept=".csv, .xlsx, .xls"
                                            class="sr-only" required>
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">CSV/Excel up to 10MB</p>
                                <p id="file-name" class="text-sm text-gray-900 font-medium mt-2"></p>
                            </div>
                        </div>
                    </div>


                <!-- Action Buttons -->
                <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.registrations.index') }}"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-4 py-2 bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium rounded-lg transition">
                        Upload and Preview
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // File upload name display
                const fileInput = document.getElementById('csv_file');
                const fileNameDisplay = document.getElementById('file-name');

                fileInput.addEventListener('change', function () {
                    if (this.files && this.files.length > 0) {
                        fileNameDisplay.textContent = this.files[0].name;
                    } else {
                        fileNameDisplay.textContent = '';
                    }
                });
            });
        </script>
    @endpush
@endsection