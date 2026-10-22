@extends('layouts.app')

@section('title', 'Generate Certificate')
@section('page-title', 'Generate Certificate')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Generate Certificate</h1>
            <p class="text-sm text-gray-600 mt-1">Create a new certificate for a student, event, or semester</p>
        </div>
        <a href="{{ route('certificates.index') }}" class="text-gray-500 hover:text-gray-700 font-medium">Back to List</a>
    </div>

    <form action="{{ route('certificates.store') }}" method="POST" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-6" x-data="{
        type: 'STUDENT',
        recipientName: '',
        updateRecipient() {
            const select = $refs.referenceSelect;
            const option = select.options[select.selectedIndex];
            if (option && this.type === 'STUDENT') {
                this.recipientName = option.text;
            }
        }
    }">
        @csrf

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
                 <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Certificate Type -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Certificate Type <span class="text-red-500">*</span></label>
                <select name="type" x-model="type" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" required>
                    <option value="STUDENT">Student Certificate</option>
                    <option value="EVENT">Event Participation Certificate</option>
                    <option value="PROGRAM">Program Certificate</option>
                </select>
            </div>

            <!-- Program/Semester (Crucial for dates) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Select Program (Batch) <span class="text-red-500">*</span></label>
                <select name="sembatches_id" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" required>
                    <option value="">Select Batch</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->sembatches_id }}">{{ $program->standard->stdname ?? 'N/A' }} ({{ $program->start_date ? $program->start_date->format('M Y') : 'N/A' }} - {{ $program->end_date ? $program->end_date->format('M Y') : 'N/A' }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Reference ID -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Select Awardee / Reference <span class="text-red-500">*</span></label>
                
                <!-- Student Select -->
                <div x-show="type === 'STUDENT'">
                    <select name="reference_id_student" x-ref="referenceSelect" @change="updateRecipient()" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" x-bind:required="type === 'STUDENT'">
                        <option value="">Select Student</option>
                        @foreach($students as $student)
                            <option value="{{ $student->student_id }}">{{ $student->fname }} {{ $student->lname }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Event Select -->
                <div x-show="type === 'EVENT'" x-cloak>
                    <select name="reference_id_event" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" x-bind:required="type === 'EVENT'">
                        <option value="">Select Event</option>
                        @foreach($events as $event)
                            <option value="{{ $event->event_id }}">{{ $event->title }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Recipient Name (On Certificate) <span class="text-red-500">*</span></label>
                <input type="text" name="recipient_name" x-model="recipientName" placeholder="e.g. Recipient Name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Generated Date <span class="text-red-500">*</span></label>
                <input type="text" name="generated_date" value="{{ date('d/m/Y') }}" required class="date-picker w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" placeholder="DD/MM/YYYY">
            </div>
        </div>

        <div class="flex justify-end pt-6 border-t border-gray-200">
            <button type="submit" class="bg-primary-500 hover:bg-primary-600 text-white font-bold px-8 py-3 rounded-lg transition shadow-md flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Generate & Save Certificate
            </button>
        </div>
    </form>
</div>
@endsection
