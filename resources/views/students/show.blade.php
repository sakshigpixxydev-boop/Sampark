@extends('layouts.app')

@section('title', 'Student Details')

@section('content')

<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4 shrink-0">
                <a href="{{ route('students.index') }}"
                    class="p-2 bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-all text-gray-500 hover:text-[#00bde0]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <h2 class="text-3xl font-bold text-gray-900">Student Profile</h2>
                    <p class="text-sm text-gray-500">Comprehensive details for {{ $student->fname }} {{ $student->lname }}</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 items-center justify-end">
                @if(auth()->user()->hasPermission(10042) || auth()->user()->hasPermission(11201) || auth()->user()->hasPermission(10001) || auth()->user()->hasPermission(11602))
                    <a href="{{ route('students.edit', $student->student_id) }}"
                        class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-all flex items-center gap-1.5 shadow-sm whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z font-bold">
                            </path>
                        </svg>
                        Edit Student
                    </a>
                    
                    @if($student->trashed())
                        <form action="{{ route('students.restore', $student->student_id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold shadow-md transition-all flex items-center gap-1.5 whitespace-nowrap">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89H17v5H5"></path>
                                </svg>
                                Restore Student
                            </button>
                        </form>
                    @else
                        <button type="button" onclick="openDeleteModal('{{ $student->student_id }}', '{{ route('students.destroy', $student->student_id) }}')"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold shadow-md transition-all flex items-center gap-1.5 whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4m0 0a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete Student
                        </button>
                    @endif
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-lg shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-lg shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        @if($student->trashed())
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-800 rounded-r-lg shadow-sm">
                <h4 class="font-bold text-red-900">Archived Student Record</h4>
                <p class="text-sm mt-1">This student profile was deleted/archived on <strong>{{ $student->deleted_at->format('d M, Y \a\t H:i') }}</strong>.</p>
                @if($student->deleted_reason)
                    <p class="text-sm mt-2 p-3 bg-red-100/60 rounded border border-red-200">
                        <strong class="text-red-900 block text-xs uppercase font-extrabold mb-1">Reason for Deletion:</strong>
                        {{ $student->deleted_reason }}
                    </p>
                @endif
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left 2 Columns: Structured Details -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Section 1: Personal Details -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#00bde0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Personal Information
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">First Name</p>
                            <p class="text-gray-900 font-semibold">{{ $student->fname }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">Middle Name</p>
                            <p class="text-gray-900 font-semibold">{{ $student->mname ?: 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">Last Name</p>
                            <p class="text-gray-900 font-semibold">{{ $student->lname }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">Gender</p>
                            <p class="text-gray-900 font-semibold">
                                {{ $student->gender == 'M' ? 'Male' : ($student->gender == 'F' ? 'Female' : ($student->gender ? $student->gender : 'Other')) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">Birth Date</p>
                            <p class="text-gray-900 font-semibold">{{ $student->birthdate ? $student->birthdate->format('d M, Y') : 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">Email Address</p>
                            <p class="text-gray-900 font-semibold">{{ $student->email ?: 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">Primary Contact No</p>
                            <p class="text-gray-900 font-semibold">
                                @if($student->contact_no)
                                    <x-phone-link :number="$student->contact_no" class="hover:text-[#00bde0] transition-colors" />
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">WhatsApp Number</p>
                            <p class="text-gray-900 font-semibold">
                                @if($student->whatsapp_no)
                                    <x-phone-link :number="$student->whatsapp_no" class="hover:text-[#00bde0] transition-colors" />
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">Preferred Language</p>
                            <p class="text-gray-900 font-semibold">{{ $languagesMap[$student->materiallanguage] ?? $student->materiallanguage ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">Selected Form Language</p>
                            <p class="text-gray-900 font-semibold">{{ $student->selected_language ?: 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Address Information (Residential vs Postal) -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#00bde0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Address Details
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8 divide-y md:divide-y-0 md:divide-x divide-gray-100">
                        <!-- Residential Address -->
                        <div class="space-y-4">
                            <h4 class="text-sm font-bold text-gray-900 pb-2 border-b border-gray-100 flex items-center gap-1.5">
                                <span class="w-1.5 h-3 bg-[#00bde0] rounded-full"></span>
                                Residential / Permanent Address
                            </h4>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase">Address Room/House/Street</p>
                                <p class="text-gray-900 font-semibold text-sm leading-relaxed">{{ $student->address ?: 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase">City / Village</p>
                                <p class="text-gray-900 font-semibold">{{ $student->city_village ?: 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase">Taluka</p>
                                <p class="text-gray-900 font-semibold">{{ $student->taluka->tname ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase">District</p>
                                <p class="text-gray-900 font-semibold">{{ $student->district->dname ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase">State & Country</p>
                                <p class="text-gray-900 font-semibold">{{ $student->state->sname ?? 'N/A' }} ({{ $student->country->cname ?? 'India' }})</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase">Pincode</p>
                                <p class="text-gray-900 font-semibold">{{ $student->pincode ?: 'N/A' }}</p>
                            </div>
                        </div>

                        <!-- Postal Address -->
                        <div class="space-y-4 md:pl-8 pt-6 md:pt-0">
                            <h4 class="text-sm font-bold text-gray-900 pb-2 border-b border-gray-100 flex items-center gap-1.5">
                                <span class="w-1.5 h-3 bg-[#00bde0] rounded-full"></span>
                                Postal / Communication Address
                            </h4>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase">Address Room/House/Street</p>
                                <p class="text-gray-900 font-semibold text-sm leading-relaxed">{{ $student->address_postal ?: 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase">City / Village</p>
                                <p class="text-gray-900 font-semibold">{{ $student->city_postal ?: 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase">Taluka</p>
                                <p class="text-gray-900 font-semibold">
                                    {{ $student->taluka_id_postal ? (\DB::table('ms_taluko')->where('taluka_id', $student->taluka_id_postal)->value('tname') ?? 'N/A') : 'N/A' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase">Pincode</p>
                                <p class="text-gray-900 font-semibold">{{ $student->secondary_pincode ?: 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Academic Details -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#00bde0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l9-5-9-5-9 5 9 5zm0 0v6m0-6L3 9m18 0l-9 5-9-5m9 5v6c0 1.105.895 2 2 2h4a2 2 0 002-2v-6z"></path>
                            </svg>
                            Academic Details
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">Standard / Grade</p>
                            <p class="text-gray-900 font-semibold">{{ $student->standard->stdname ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">Stream</p>
                            <p class="text-gray-900 font-semibold">{{ $student->stream ?: 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">Education Medium</p>
                            <p class="text-gray-900 font-semibold">{{ $educationMediumMap[$student->educationmedium] ?? $student->educationmedium ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">Educational Board</p>
                            <p class="text-gray-900 font-semibold">
                                @if($student->eduboard_id)
                                    {{ $student->educationalBoard->boardname ?? 'N/A' }}
                                @else
                                    {{ $student->other_eduboard_name ?: 'N/A' }}
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">School (Assigned)</p>
                            <p class="text-gray-900 font-semibold">{{ $student->school->schoolname ?? 'Not Assigned' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">School Name (Form Input)</p>
                            <p class="text-gray-900 font-semibold">{{ $student->school_name ?: 'N/A' }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-xs font-bold text-gray-400 uppercase">School Address / Geolocation Link</p>
                            <p class="text-gray-900 font-semibold text-sm leading-relaxed">{{ $student->school_address ?: 'N/A' }}</p>
                            @if($student->geo_tagging_school_address)
                                <a href="{{ $student->geo_tagging_school_address }}" target="_blank" class="mt-1.5 inline-flex items-center text-xs text-[#00bde0] font-bold hover:underline gap-1 bg-[#00bde0]/10 px-2.5 py-1 rounded-full">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    </svg>
                                    View School Location (Google Maps)
                                </a>
                            @endif
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-xs font-bold text-gray-400 uppercase">Institution / Learning Center</p>
                            <p class="text-gray-900 font-semibold">{{ $student->institute ?: 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Satsang & Background Details -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#00bde0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21.196 21H3.804A1.803 1.803 0 012 19.196V11.8a1.8 1.8 0 01.787-1.488l8.2-5.466a1.8 1.8 0 012.026 0l8.2 5.466A1.8 1.8 0 0122 11.8v7.396A1.803 1.803 0 0120.196 21z"></path>
                            </svg>
                            Satsang Background & Activities
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">Satsang Mandal</p>
                            <p class="text-gray-900 font-semibold">
                                {{ $student->satsangmandal_id ? (\DB::table('ms_satsangmandal')->where('satsangmandal_id', $student->satsangmandal_id)->value('mandalname') ?? 'N/A') : 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">Kshetra Name</p>
                            <p class="text-gray-900 font-semibold">
                                {{ $student->kshetra_id ? (\DB::table('ms_kshetra')->where('kshetra_id', $student->kshetra_id)->value('kshetraname') ?? 'N/A') : 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">Shikhar Baddha Mandir</p>
                            <p class="text-gray-900 font-semibold">
                                {{ $student->shikharm_id ? (\DB::table('ms_shikharmandir')->where('shikharm_id', $student->shikharm_id)->value('mname') ?? 'N/A') : 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">How did you learn about us?</p>
                            <p class="text-gray-900 font-semibold">{{ $learnAboutMap[$student->learnabout] ?? $student->learnabout ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">Satsang Karyakar Name</p>
                            <p class="text-gray-900 font-semibold">{{ $student->satsang_karyakar_name ?: 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">Satsang Karyakar Contact</p>
                            <p class="text-gray-900 font-semibold">
                                @if($student->satsang_karyakar_contact)
                                    <x-phone-link :number="$student->satsang_karyakar_contact" class="hover:text-[#00bde0] transition-colors" />
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">School Teacher/Educator Name</p>
                            <p class="text-gray-900 font-semibold">{{ $student->school_educator_name ?: 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">School Teacher/Educator Contact</p>
                            <p class="text-gray-900 font-semibold">
                                @if($student->school_educator_no)
                                    <x-phone-link :number="$student->school_educator_no" class="hover:text-[#00bde0] transition-colors" />
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-xs font-bold text-gray-400 uppercase">Satsang Achievements</p>
                            <p class="text-gray-900 font-semibold text-sm leading-relaxed">{{ $student->achievement_stsang ?: 'None' }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-xs font-bold text-gray-400 uppercase">Academic Achievements</p>
                            <p class="text-gray-900 font-semibold text-sm leading-relaxed">{{ $student->achievement_academic ?: 'None' }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-xs font-bold text-gray-400 uppercase">Other Extracurricular Activities</p>
                            <p class="text-gray-900 font-semibold text-sm leading-relaxed">{{ $student->activities ?: 'None' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Section 5: Guardian Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#00bde0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Guardian Information
                        </h3>
                    </div>
                    <div class="p-6">
                        @if($student->guardians && $student->guardians->count() > 0)
                            <div class="space-y-6 divide-y divide-gray-100">
                                @foreach($student->guardians as $index => $guardian)
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 {{ $index > 0 ? 'pt-6' : '' }}">
                                        <div>
                                            <p class="text-xs font-bold text-gray-400 uppercase">Guardian Relation</p>
                                            <p class="text-gray-900 font-bold">{{ $guardian->relation->relationname ?? 'Guardian' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-gray-400 uppercase">Guardian Full Name</p>
                                            <p class="text-gray-900 font-semibold">{{ $guardian->fname }} {{ $guardian->mname }} {{ $guardian->lname }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-gray-400 uppercase">Guardian Contact Number</p>
                                            <p class="text-gray-900 font-semibold">
                                                @if($guardian->phone)
                                                    <x-phone-link :number="$guardian->phone" class="hover:text-[#00bde0] transition-colors" />
                                                @else
                                                    N/A
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 italic text-center py-4">No guardian information loaded.</p>
                        @endif
                    </div>
                </div>

            </div>

            <!-- Right 1 Column: Summary, Photo, Account Credentials, Payments -->
            <div class="space-y-8">
                
                <!-- Student Photo Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900">Student Photo</h3>
                    </div>
                    <div class="p-6 flex flex-col items-center">
                        @if($student->document && $student->document->photo)
                            <img src="{{ route('admin.registrations.photo', $student->student_id) }}" alt="Student Photo"
                                class="w-full h-auto rounded-lg shadow-sm border border-gray-100 max-h-[300px] object-cover">
                            <a href="{{ route('admin.registrations.photo', $student->student_id) }}" target="_blank"
                                class="mt-4 text-sm font-semibold text-[#00bde0] hover:underline flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                                View Full Image
                            </a>
                        @else
                            <div class="w-full aspect-square bg-gray-100 rounded-lg flex flex-col items-center justify-center text-gray-400">
                                <svg class="w-16 h-16 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <p class="text-sm font-medium">No photo uploaded</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Registration / LMS Account Info Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900">System & Account Info</h3>
                    </div>
                    <div class="p-6 space-y-4 text-sm">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">Enrolment Number</p>
                            <p class="text-gray-900 font-extrabold text-[#00bde0]">{{ $student->enrolment_no ?: 'GEN_PENDING' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">Portal ID</p>
                            <p class="text-gray-900 font-semibold">{{ $student->portal_id ?: 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase font-sans">LMS Username</p>
                            <p class="text-gray-900 font-mono font-semibold">{{ $student->lmsuser ?: 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase font-sans">LMS Password</p>
                            <p class="text-gray-900 font-mono font-semibold">{{ $student->lmspswd ?: 'N/A' }}</p>
                        </div>
                        <div class="pt-3 border-t border-gray-100">
                            <p class="text-xs font-bold text-gray-400 uppercase">Created By</p>
                            <p class="text-gray-900 font-semibold">
                                @if($student->createdby === 'Migration_Engine')
                                    Migration Engine
                                @else
                                    {{ $student->creator->fname ?? 'Public Registration' }} {{ $student->creator->lname ?? '' }}
                                    @if($student->creator && $student->creator->role)
                                        <span class="text-xs text-gray-400">({{ $student->creator->role->rolename }})</span>
                                    @endif
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase font-sans">Registration Date</p>
                            <p class="text-gray-900 font-semibold">{{ $student->created_at ? $student->created_at->format('d M, Y H:i A') : 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Registration / Payment Status Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900">Admission Status</h3>
                    </div>
                    <div class="p-6 space-y-4 text-sm">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase mb-1">Application Status</p>
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-black
                                {{ in_array($student->application_status, ['Approved', 'Enrolled', 'Done']) ? 'bg-green-100 text-green-800' : 'bg-yellow-105 text-yellow-800 bg-yellow-50' }}">
                                {{ $student->application_status ?: 'Initiated' }}
                            </span>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase mb-1">Fees Payment Status</p>
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-black
                                {{ $student->fees_status === 'Paid' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $student->fees_status ?: 'Unpaid' }}
                            </span>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">Selected Plan</p>
                            <p class="text-gray-900 font-extrabold text-base">{{ $student->subscription ?: 'None Selected' }}</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Deletion Reason Modal -->
<x-modal name="delete-student" title="Delete Student Profile" maxWidth="lg">
    <form id="delete-student-form" action="" method="POST">
        @csrf
        @method('DELETE')
        <div class="space-y-4">
            <div class="flex items-start gap-4 p-1">
                <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-red-100">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                        </path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h4 class="text-sm font-bold text-gray-900 mb-2">Are you sure you want to delete this student record?</h4>
                    <p class="text-xs text-gray-500 mb-4 font-normal">This will soft-delete the student, hiding them from active student lists. Please select a reason for deletion:</p>
                    
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Reason for Deletion</label>
                    <select name="reason_select" id="reason_select" onchange="toggleCustomReason()" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#00bde0] bg-white mb-4">
                        <option value="Duplicate Student Account">Duplicate Student Account</option>
                        <option value="Not Interested / Left Course">Not Interested / Left Course</option>
                        <option value="Incorrect Batch / Invalid Admission">Incorrect Batch / Invalid Admission</option>
                        <option value="Other">Other (Write Custom Reason Below)</option>
                    </select>

                    <div id="custom_reason_container" class="hidden">
                        <textarea name="reason" id="custom_reason" rows="3" required
                            class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-red-500 focus:border-red-500 transition-all text-sm"
                            placeholder="Please detail the reason for deletion..."></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-6 flex justify-end gap-3">
            <button type="button" x-on:click="$dispatch('close-modal', 'delete-student')"
                class="px-5 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all">
                Cancel
            </button>
            <button type="submit"
                class="px-5 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 shadow-md transition-all">
                Confirm Delete
            </button>
        </div>
    </form>
</x-modal>

<script>
    function openDeleteModal(studentId, url) {
        const form = document.getElementById('delete-student-form');
        form.action = url;
        
        // Reset defaults
        document.getElementById('reason_select').value = 'Duplicate Student Account';
        document.getElementById('custom_reason').value = 'Duplicate Student Account';
        document.getElementById('custom_reason_container').classList.add('hidden');
        
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'delete-student' }));
    }

    function toggleCustomReason() {
        const select = document.getElementById('reason_select');
        const container = document.getElementById('custom_reason_container');
        const textarea = document.getElementById('custom_reason');
        
        if (select.value === 'Other') {
            container.classList.remove('hidden');
            textarea.value = '';
            textarea.required = true;
            textarea.focus();
        } else {
            container.classList.add('hidden');
            textarea.value = select.value;
            textarea.required = false;
        }
    }
    
    // Add event listener to submit form to make sure custom_reason is filled
    document.getElementById('delete-student-form').addEventListener('submit', function(e) {
        const select = document.getElementById('reason_select');
        const textarea = document.getElementById('custom_reason');
        if (select.value !== 'Other') {
            textarea.value = select.value;
            textarea.required = false;
        }
    });
</script>
@endsection
