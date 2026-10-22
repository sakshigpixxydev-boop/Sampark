@extends('layouts.app')

@section('title', 'Review Student Registration')

@section('content')

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8 flex flex-col xl:flex-row xl:items-start justify-between gap-6">
                <div class="flex items-center gap-4 shrink-0">
                    <a href="{{ route('admin.registrations.index') }}"
                        class="p-2 bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-all text-gray-500 hover:text-[#00bde0]">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <div>
                        <h2 class="text-3xl font-bold text-gray-900">Review Application</h2>
                        <p class="text-sm text-gray-500">Reviewing details for #{{ $student->student_id }}</p>
                    </div>
                </div>

                <div class="flex flex-col gap-3 items-end">
                    <!-- Row 1: Payment Actions -->
                    @if($student->fees_status !== 'Paid')
                        @if(auth()->user()->canRunRegistrationOps())
                            <div class="flex flex-wrap gap-3 items-center justify-end">
                                <form action="{{ route('admin.registrations.sync-payment', $student->student_id) }}" method="POST"
                                    class="flex items-center gap-2">
                                    @csrf
                                    <input type="text" name="payment_id" placeholder="Optional Payment ID (pay_...)"
                                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#00bde0] w-52 shadow-sm transition-all">
                                    <button type="submit"
                                        class="px-6 py-2 bg-[#00bde0] hover:bg-[#0099b8] text-white rounded-lg font-semibold shadow-md transition-all flex items-center gap-1.5 whitespace-nowrap">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89H17v5h5M11 12H9m2 0h2m-2 0v2m0-2v-2">
                                            </path>
                                        </svg>
                                        Sync Razorpay Status
                                    </button>
                                </form>

                                <form action="{{ route('admin.registrations.send-reminder', $student->student_id) }}" method="POST"
                                    class="inline">
                                    @csrf
                                    <button type="submit"
                                        class="px-6 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg font-semibold shadow-md transition-all flex items-center gap-1.5 whitespace-nowrap">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                        Send Payment Reminder
                                    </button>
                                </form>
                            </div>
                        @endif
                    @endif

                    <!-- Row 2: Application Actions -->
                    <div class="flex flex-wrap gap-3 items-center justify-end">
                        @if(auth()->user()->hasPermission(10042) || auth()->user()->hasPermission(11201) || auth()->user()->hasPermission(10001) || auth()->user()->hasPermission(11602))
                            <a href="{{ route('students.edit', $student->student_id) }}"
                                class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-all flex items-center gap-1.5 shadow-sm whitespace-nowrap">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                    </path>
                                </svg>
                                Edit Details
                            </a>
                        @endif

                        @if($student->application_status === 'Enrolled')
                            <span
                                class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-green-50 text-green-700 border border-green-200 whitespace-nowrap">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Enrolled
                            </span>

                        @elseif($student->application_status === 'Rejected')
                            <span
                                class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-red-50 text-red-700 border border-red-200 whitespace-nowrap">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Rejected
                            </span>
                        @else
                            @if(auth()->user()->hasRole('School') || auth()->user()->hasRole('Educator'))
                                @if($student->school_approval_status === 'Pending')
                                    @if(auth()->user()->hasRole('School') || auth()->user()->hasPermission(11606))
                                    <button onclick="openRejectModal()"
                                        class="px-6 py-2 bg-white border border-red-200 text-red-600 rounded-lg font-semibold hover:bg-red-50 transition-all whitespace-nowrap">
                                        Reject Application
                                    </button>
                                    @endif
                                    @if(auth()->user()->hasRole('School') || auth()->user()->hasPermission(11605))
                                    <form action="{{ route('admin.registrations.approve', $student->student_id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-6 py-2 bg-gradient-to-br from-[#00bde0] to-[#0099b8] text-white rounded-lg font-semibold shadow-md hover:shadow-lg transition-all whitespace-nowrap">
                                            Approve Registration
                                        </button>
                                    </form>
                                    @endif
                                @elseif($student->school_approval_status === 'Approved')
                                    <span class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-green-50 text-green-700 border border-green-200 whitespace-nowrap">
                                        Approved (School Level)
                                    </span>
                                @elseif($student->school_approval_status === 'Rejected')
                                    <span class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-red-50 text-red-700 border border-red-200 whitespace-nowrap">
                                        Rejected (School Level)
                                    </span>
                                @endif
                            @else
                                @if($student->application_status === 'Approved')
                                    <span class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-green-50 text-green-700 border border-green-200 whitespace-nowrap hover:bg-green-100 transition-all cursor-default">
                                        Approved (School Level)
                                    </span>
                                @endif
                                <button onclick="openRejectModal()"
                                    class="px-6 py-2 bg-white border border-red-200 text-red-600 rounded-lg font-semibold hover:bg-red-50 transition-all whitespace-nowrap">
                                    Reject Application
                                </button>
                                <button onclick="openEnrollModal()"
                                    class="px-6 py-2 bg-gradient-to-br from-[#00bde0] to-[#0099b8] text-white rounded-lg font-semibold shadow-md hover:shadow-lg transition-all whitespace-nowrap">
                                    Review & Enroll
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-lg shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-lg shadow-sm">
                    {!! session('error') !!}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Student Profile Column -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Section 1: Personal Details -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden font-sans">
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
                                <p class="text-xs font-bold text-gray-400 uppercase font-sans">Middle Name</p>
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
                                <p class="text-gray-900 font-semibold font-sans">{{ $student->birthdate ? $student->birthdate->format('d M, Y') : 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase">Email Address</p>
                                <p class="text-gray-900 font-semibold font-sans">{{ $student->email ?: 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase font-sans">Primary Contact No</p>
                                <p class="text-gray-900 font-semibold font-sans">
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
                                <p class="text-xs font-bold text-gray-400 uppercase font-sans font-sans">Selected Form Language</p>
                                <p class="text-gray-900 font-semibold font-sans font-sans">{{ $student->selected_language ?: 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Address Information -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden font-sans">
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
                            <div class="space-y-4">
                                <h4 class="text-sm font-bold text-gray-900 pb-2 border-b border-gray-100 flex items-center gap-1.5 font-sans">
                                    <span class="w-1.5 h-3 bg-[#00bde0] rounded-full"></span>
                                    Residential / Permanent Address
                                </h4>
                                <div>
                                    <p class="text-xs font-bold text-gray-400 uppercase">Address Room/House/Street</p>
                                    <p class="text-gray-900 font-semibold text-sm leading-relaxed">{{ $student->address ?: 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-gray-400 uppercase font-sans">City / Village</p>
                                    <p class="text-gray-900 font-semibold font-sans">{{ $student->city_village ?: 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-gray-400 uppercase font-sans">Taluka</p>
                                    <p class="text-gray-900 font-semibold font-sans">{{ $student->taluka->tname ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-gray-400 uppercase font-sans">District</p>
                                    <p class="text-gray-900 font-semibold font-sans">{{ $student->district->dname ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-gray-400 uppercase font-sans font-sans">State & Country</p>
                                    <p class="text-gray-900 font-semibold font-sans font-sans">{{ $student->state->sname ?? 'N/A' }} ({{ $student->country->cname ?? 'India' }})</p>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-gray-400 uppercase font-sans">Pincode</p>
                                    <p class="text-gray-900 font-semibold font-mono">{{ $student->pincode ?: 'N/A' }}</p>
                                </div>
                            </div>

                            <div class="space-y-4 md:pl-8 pt-6 md:pt-0">
                                <h4 class="text-sm font-bold text-gray-900 pb-2 border-b border-gray-100 flex items-center gap-1.5 font-sans">
                                    <span class="w-1.5 h-3 bg-[#00bde0] rounded-full"></span>
                                    Postal / Communication Address
                                </h4>
                                <div>
                                    <p class="text-xs font-bold text-gray-400 uppercase font-sans">Address Room/House/Street</p>
                                    <p class="text-gray-900 font-semibold text-sm leading-relaxed">{{ $student->address_postal ?: 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-gray-400 uppercase font-sans">City / Village</p>
                                    <p class="text-gray-900 font-semibold font-sans">{{ $student->city_postal ?: 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-gray-400 uppercase font-sans font-sans">Taluka</p>
                                    <p class="text-gray-900 font-semibold font-sans font-sans">
                                        {{ $student->taluka_id_postal ? (\DB::table('ms_taluko')->where('taluka_id', $student->taluka_id_postal)->value('tname') ?? 'N/A') : 'N/A' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-gray-400 uppercase">Pincode</p>
                                    <p class="text-gray-900 font-semibold font-mono">{{ $student->secondary_pincode ?: 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Academic Details -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden font-sans">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-[#00bde0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l9-5-9-5-9 5 9 5zm0 0v6m0-6L3 9m18 0l-9 5-9-5m9 5v6c0 1.105.895 2 2 2h4a2 2 0 002-2v-6z"></path>
                                </svg>
                                Academic Details & Batch
                            </h3>
                            @if(auth()->user()->isMasterAdmin() || auth()->user()->hasPermission(10001))
                                <button onclick="openChangeBatchModal()" type="button" class="px-3 py-1.5 bg-white border border-[#00bde0] text-[#00bde0] hover:bg-[#00bde0] hover:text-white text-xs font-semibold rounded-lg shadow-sm transition-all flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                    </svg>
                                    Change Batch
                                </button>
                            @endif
                        </div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase font-sans">Standard / Grade</p>
                                <p class="text-gray-900 font-semibold">{{ $student->standard->stdname ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase">Stream</p>
                                <p class="text-gray-900 font-semibold">{{ $student->stream ?: 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase">Education Medium</p>
                                <p class="text-gray-900 font-semibold font-sans">{{ $educationMediumMap[$student->educationmedium] ?? $student->educationmedium ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase font-sans">Educational Board</p>
                                <p class="text-gray-900 font-semibold font-sans">
                                    @if($student->eduboard_id)
                                        {{ $student->educationalBoard->boardname ?? ($student->educationalBoard->eduboardname ?? 'N/A') }}
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
                                    <a href="{{ $student->geo_tagging_school_address }}" target="_blank" class="mt-1.5 inline-flex items-center text-xs text-[#00bde0] font-bold hover:underline gap-1 bg-[#00bde0]/10 px-2.5 py-1 rounded-full font-sans">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        </svg>
                                        View School Location (Google Maps)
                                    </a>
                                @endif
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-xs font-bold text-gray-400 uppercase">Institution / Learning Center</p>
                                <p class="text-gray-900 font-semibold font-sans">{{ $student->institute ?: 'N/A' }}</p>
                            </div>
                            <div class="pt-4 border-t border-gray-100">
                                <p class="text-xs font-bold text-gray-400 uppercase">Selected Plan</p>
                                <span class="text-[#00bde0] font-extrabold text-sm font-sans">{{ $student->subscription ?: 'N/A' }}</span>
                            </div>
                            <div class="pt-4 border-t border-gray-100 font-sans">
                                @php
                                    $activeBatch = $student->semesterBatches->where('pivot.status', 0)->first();
                                @endphp
                                <p class="text-xs font-bold text-gray-400 uppercase font-sans">Assigned Semester Batch</p>
                                <span class="text-gray-900 font-bold text-sm font-sans">{{ $activeBatch ? ($activeBatch->display_name ?: 'Semester ' . $activeBatch->sembatches_id) : 'No Active Batch Assigned' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Section 4: Satsang & Background Details -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden font-sans">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-[#00bde0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21.196 21H3.804A1.803 1.803 0 012 19.196V11.8a1.8 1.8 0 01.787-1.488l8.2-5.466a1.8 1.8 0 012.026 0l8.2 5.466A1.8 1.8 0 0122 11.8v7.396A1.803 1.803 0 0120.196 21z"></path>
                                </svg>
                                BAPS Satsang Background & Activities
                            </h3>
                        </div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase">Satsang Mandal</p>
                                <p class="text-gray-900 font-semibold font-sans font-sans">
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
                                <p class="text-xs font-bold text-gray-400 uppercase font-sans">Shikhar Baddha Mandir</p>
                                <p class="text-gray-900 font-semibold font-sans">
                                    {{ $student->shikharm_id ? (\DB::table('ms_shikharmandir')->where('shikharm_id', $student->shikharm_id)->value('mname') ?? 'N/A') : 'N/A' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase">How did you learn about us?</p>
                                <p class="text-gray-900 font-semibold">{{ $learnAboutMap[$student->learnabout] ?? $student->learnabout ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase font-sans">Satsang Karyakar Name</p>
                                <p class="text-gray-900 font-semibold font-sans">{{ $student->satsang_karyakar_name ?: 'N/A' }}</p>
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
                                <p class="text-xs font-bold text-gray-400 uppercase font-sans">School Teacher/Educator Name</p>
                                <p class="text-gray-900 font-semibold font-sans">{{ $student->school_educator_name ?: 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase font-sans">School Teacher/Educator Contact</p>
                                <p class="text-gray-900 font-semibold font-sans font-sans">
                                    @if($student->school_educator_no)
                                        <x-phone-link :number="$student->school_educator_no" class="hover:text-[#00bde0] transition-colors" />
                                    @else
                                        N/A
                                    @endif
                                </p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-xs font-bold text-gray-400 uppercase font-sans">Satsang Achievements</p>
                                <p class="text-gray-900 font-semibold text-sm leading-relaxed">{{ $student->achievement_stsang ?: 'None' }}</p>
                            </div>
                            <div class="md:col-span-2 font-sans">
                                <p class="text-xs font-bold text-gray-400 uppercase">Academic Achievements</p>
                                <p class="text-gray-900 font-semibold text-sm leading-relaxed">{{ $student->achievement_academic ?: 'None' }}</p>
                            </div>
                            <div class="md:col-span-2 font-sans font-sans">
                                <p class="text-xs font-bold text-gray-400 uppercase">Other Extracurricular Activities</p>
                                <p class="text-gray-900 font-semibold text-sm leading-relaxed font-sans">{{ $student->activities ?: 'None' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Section 5: Guardian Details -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden font-sans">
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
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 {{ $index > 0 ? 'pt-6 font-sans' : '' }}">
                                            <div>
                                                <p class="text-xs font-bold text-gray-400 uppercase font-sans">Relation</p>
                                                <p class="text-gray-900 font-bold font-sans">{{ $guardian->relation->relationname ?? 'Guardian' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-gray-400 uppercase">Guardian Full Name</p>
                                                <p class="text-gray-900 font-semibold">{{ $guardian->fname }} {{ $guardian->mname }} {{ $guardian->lname }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-gray-400 uppercase">Contact Number</p>
                                                <p class="text-gray-900 font-semibold font-sans">
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
                                <p class="text-sm text-gray-500 italic text-center py-4 font-sans">No guardian information loaded.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Migration History -->
                    @if($student->histories && $student->histories->count() > 0)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-[#00bde0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Migration History
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="flow-root">
                                    <ul class="-mb-8">
                                        @foreach($student->histories->sortByDesc('created_at') as $history)
                                            <li>
                                                <div class="relative pb-8">
                                                    @if(!$loop->last)
                                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                                    @endif
                                                    <div class="relative flex space-x-3">
                                                        <div>
                                                            <span class="h-8 w-8 rounded-full bg-blue-50 flex items-center justify-center ring-8 ring-white">
                                                                <svg class="h-5 w-5 text-[#00bde0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                                                </svg>
                                                            </span>
                                                        </div>
                                                        <div class="flex-1 min-w-0 pt-1.5 flex justify-between space-x-4">
                                                            <div>
                                                                <p class="text-sm text-gray-500">
                                                                    Migrated from <span class="font-semibold text-gray-900">{{ $history->fromBatch->display_name ?? 'N/A' }}</span> to <span class="font-semibold text-gray-900">{{ $history->toBatch->display_name }}</span>
                                                                    @if($history->remarks)
                                                                        <span class="block text-xs text-gray-400 mt-0.5">Remarks: {{ $history->remarks }}</span>
                                                                    @endif
                                                                </p>
                                                            </div>
                                                            <div class="text-right text-xs whitespace-nowrap text-gray-500">
                                                                <time datetime="{{ $history->created_at }}">{{ $history->created_at->format('d M, Y H:i') }}</time>
                                                                <span class="block text-[10px] text-gray-400">By: {{ $history->executor->fname ?? 'System' }} {{ $history->executor->lname ?? '' }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Payment & Invoice History -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-[#00bde0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                </svg>
                                Payment & Invoice History
                            </h3>
                        </div>
                        <div class="p-6">
                            @if($student->invoices && $student->invoices->count() > 0)
                                <div class="space-y-6">
                                    @foreach($student->invoices->sortByDesc('invoice_date') as $invoice)
                                        <div class="border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                                            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex flex-wrap justify-between items-center gap-2">
                                                <div>
                                                    <span class="text-xs font-bold text-gray-400 uppercase">Invoice #{{ $invoice->invoice_id }}</span>
                                                    <span class="block text-xs text-gray-500">Date: {{ $invoice->invoice_date ? $invoice->invoice_date->format('d M, Y') : 'N/A' }}</span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    @if($invoice->status === 'PAID')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                                            Paid
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                                            {{ $invoice->status }}
                                                        </span>
                                                    @endif
                                                    <span class="text-sm font-bold text-gray-800">₹{{ number_format($invoice->total_due, 2) }}</span>
                                                </div>
                                            </div>
                                            
                                            <!-- Invoice Items -->
                                            <div class="p-4 border-b border-gray-100 bg-white">
                                                <p class="text-xs font-bold text-gray-400 uppercase mb-2">Billing Items</p>
                                                <ul class="divide-y divide-gray-100">
                                                    @foreach($invoice->items as $item)
                                                        <li class="py-2 flex justify-between items-center text-sm">
                                                            <div class="text-gray-700 font-medium">
                                                                {{ $item->description }}
                                                                <span class="text-xs text-gray-400 font-normal"> (Qty: {{ $item->qty }})</span>
                                                            </div>
                                                            <div class="text-gray-900 font-bold">₹{{ number_format($item->line_amount, 2) }}</div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>

                                            <!-- Financial Summary -->
                                            @php
                                                $paidOnInvoice = $invoice->payments ? $invoice->payments->where('status', '1')->sum('amount') : 0;
                                                $pendingBalance = $invoice->total_due - $paidOnInvoice;
                                            @endphp
                                            <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex justify-between items-center text-xs">
                                                <div class="flex items-center gap-2.5">
                                                    <div class="bg-green-50 text-green-700 px-2.5 py-1 rounded-md border border-green-100 font-semibold shadow-sm flex items-center gap-1">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                                        Paid: ₹{{ number_format($paidOnInvoice, 2) }}
                                                    </div>
                                                    @if($pendingBalance > 0)
                                                        <div class="bg-red-50 text-red-700 px-2.5 py-1 rounded-md border border-red-100 font-semibold shadow-sm flex items-center gap-1 animate-pulse">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                                            Pending: ₹{{ number_format($pendingBalance, 2) }}
                                                        </div>
                                                    @endif
                                                </div>
                                                @if($pendingBalance > 0 && (Auth::user()->hasRole('Admin') || Auth::user()->hasRole('School Coordinator') || Auth::user()->hasRole('Accountant')))
                                                    <form action="{{ route('invoices.mark-as-paid', $invoice->invoice_id) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="px-3 py-1.5 bg-[#00bde0] hover:bg-[#0099b8] text-white font-bold rounded-lg hover:shadow-md shadow-sm transition-all text-xs">
                                                            Mark as Complete Paid
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>

                                            <!-- Payment Transactions -->
                                            <div class="p-4 bg-gray-50/50">
                                                <p class="text-xs font-bold text-gray-400 uppercase mb-2">Transactions</p>
                                                @if($invoice->payments && $invoice->payments->count() > 0)
                                                    <div class="space-y-3">
                                                        @foreach($invoice->payments as $payment)
                                                            <div class="flex justify-between items-start text-xs border border-gray-100 bg-white p-3 rounded-lg shadow-sm">
                                                                <div class="space-y-1">
                                                                    <div class="flex items-center gap-1.5">
                                                                        <span class="font-bold text-gray-700">Txn ID:</span>
                                                                        <code class="px-1.5 py-0.5 bg-gray-100 text-gray-800 rounded font-mono text-[10px]">{{ $payment->txnid }}</code>
                                                                    </div>
                                                                    <div>
                                                                        <span class="font-bold text-gray-400 uppercase">Mode:</span>
                                                                        <span class="text-gray-600 font-medium">{{ $payment->paymentMode->paymodename ?? 'Online' }}</span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="font-bold text-gray-400 uppercase">Date:</span>
                                                                        <span class="text-gray-600 font-medium">{{ $payment->processdatetime ? $payment->processdatetime->format('d M, Y H:i A') : 'N/A' }}</span>
                                                                    </div>
                                                                </div>
                                                                <div class="text-right">
                                                                    <div class="text-sm font-extrabold text-[#00bde0]">₹{{ number_format($payment->amount, 2) }}</div>
                                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $payment->status == '1' ? 'bg-green-50 text-green-700 border border-green-100' : 'bg-red-50 text-red-700 border border-red-100' }}">
                                                                        {{ $payment->status == '1' ? 'Success' : 'Failed' }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    @if($invoice->status === 'PAID')
                                                        <div class="flex items-center gap-2 p-3 bg-blue-50/70 border border-blue-100 rounded-lg text-xs text-blue-800">
                                                            <svg class="w-4 h-4 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                            <span>Paid via multi-semester plan / batch migration (No separate payment transaction record required for this invoice).</span>
                                                        </div>
                                                    @else
                                                        <p class="text-xs text-gray-500 italic">No transactions logged for this invoice.</p>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-6 text-gray-400">
                                    <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <p class="text-sm font-medium">No payment history found</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Photo & Actions Column -->
                <div class="space-y-8">
                    @if($student->registered_via_school_link)
                        <!-- School Payment Verification Card -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                                <h3 class="text-lg font-bold text-gray-900">School Payment</h3>
                                @if($student->school_payment_status === 'Approved')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Approved
                                    </span>
                                @elseif($student->school_payment_status === 'Rejected')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Rejected
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Pending Review
                                    </span>
                                @endif
                            </div>
                            <div class="p-6 space-y-4">
                                <div>
                                    <p class="text-xs font-bold text-gray-400 uppercase">Fee Plan</p>
                                    <p class="text-gray-900 font-semibold">
                                        {{ $student->school_payment_plan_name ?? 'Not Provided' }}
                                        @if($student->school_payment_amount !== null)
                                            <span class="text-[#0099b8]">&mdash; ₹{{ number_format((float) $student->school_payment_amount, 2) }}</span>
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-gray-400 uppercase">Payment Method</p>
                                    <p class="text-gray-900 font-semibold">{{ $student->school_payment_method ?? 'Not Provided' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-gray-400 uppercase">Transaction/UPI ID</p>
                                    <p class="text-gray-900 font-semibold">{{ $student->school_payment_upi_id ?? 'Not Provided' }}</p>
                                </div>
                                @if($student->school_payment_rejection_reason)
                                    <div class="bg-red-50 p-3 rounded-lg border border-red-100">
                                        <p class="text-xs font-bold text-red-700 uppercase">Rejection Reason</p>
                                        <p class="text-sm font-semibold text-red-900 mt-1">{{ $student->school_payment_rejection_reason }}</p>
                                    </div>
                                @endif
                                @if($student->verified_by)
                                    <div>
                                        <p class="text-xs font-bold text-gray-400 uppercase">Verified By</p>
                                        <p class="text-gray-900 font-semibold">{{ $student->verified_by }}</p>
                                    </div>
                                @endif
                                <div>
                                    <p class="text-xs font-bold text-gray-400 uppercase mb-2">Receipt / Screenshot</p>
                                    @if($student->school_payment_screenshot)
                                        <a href="{{ Storage::url($student->school_payment_screenshot) }}" target="_blank" class="block group relative rounded-lg overflow-hidden border border-gray-200">
                                            <img src="{{ Storage::url($student->school_payment_screenshot) }}" alt="Receipt" class="w-full h-auto object-cover max-h-48 group-hover:opacity-90 transition-opacity">
                                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity text-white text-xs font-bold">
                                                View Full Receipt
                                            </div>
                                        </a>
                                    @else
                                        <p class="text-sm text-gray-500 italic">No screenshot uploaded</p>
                                    @endif
                                </div>

                                @if(auth()->user()->hasRole(['Superadmin', 'Admin', 'Accountant', 'School']) || auth()->user()->hasPermission(11601))
                                    @if($student->school_payment_status !== 'Approved')
                                        <div class="pt-4 border-t border-gray-100 flex gap-2">
                                            <button type="button" onclick="openRejectSchoolPaymentModal()" class="flex-1 px-4 py-2 border border-red-200 text-red-600 rounded-lg text-sm font-semibold hover:bg-red-50 transition-colors">
                                                Reject
                                            </button>
                                            <form action="{{ route('admin.registrations.approve-school-payment', $student->student_id) }}" method="POST" class="flex-1">
                                                @csrf
                                                <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition-colors shadow-sm">
                                                    Approve
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Photo Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900">Student Photo</h3>
                        </div>
                        <div class="p-6 flex flex-col items-center">
                            @if($document && $document->photo)
                                <img src="{{ route('admin.registrations.photo', $student->student_id) }}" alt="Student Photo"
                                    class="w-full h-auto rounded-lg shadow-sm border border-gray-100">
                                <a href="{{ route('admin.registrations.photo', $student->student_id) }}" target="_blank"
                                    class="mt-4 text-sm font-semibold text-[#00bde0] hover:underline">View Full Image</a>
                            @else
                                <div
                                    class="w-full aspect-square bg-gray-100 rounded-lg flex flex-col items-center justify-center text-gray-400">
                                    <svg class="w-16 h-16 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    <p class="text-sm">No photo uploaded</p>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Rejection Modal -->
    <x-modal name="reject-application" title="Reject Application" maxWidth="lg">
        <form action="{{ route('admin.registrations.reject', $student->student_id) }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div class="flex items-start gap-4 p-1">
                    <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 14c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-500 mb-4">Please provide a reason for rejecting this application. This
                            will be sent to the student via email.</p>
                        <textarea name="reason" rows="4" required
                            class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-red-500 focus:border-red-500 transition-all"
                            placeholder="e.g. Photo is blurry, invalid details..."></textarea>
                    </div>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close-modal', 'reject-application')"
                    class="px-5 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 shadow-md transition-all">
                    Confirm Rejection
                </button>
            </div>
        </form>
    </x-modal>

    <!-- Enroll Modal -->
    <x-modal name="enroll-student" title="Review & Enroll Student" maxWidth="lg">
        <form action="{{ route('admin.registrations.approve', $student->student_id) }}" method="POST"
            x-data="{ loading: false }" x-on:submit="loading = true">
            @csrf
            <div class="space-y-4 p-1">
                @php
                    $feePlan = \App\Models\FeePlan::where('fee_name', $student->subscription)
                        ->where(function($q) use ($student) {
                            $q->where('school_id', $student->school_id)
                              ->orWhereNull('school_id');
                        })
                        ->orderByRaw('school_id DESC')
                        ->first();

                    $allPlans = \App\Models\FeePlan::where(function($q) use ($student) {
                        $q->where('school_id', $student->school_id)
                          ->orWhereNull('school_id');
                    })->orderBy('fee_name', 'asc')->get();
                @endphp

                @if($feePlan && $feePlan->semesterBatches()->exists())
                    <p class="text-sm text-gray-500 mb-4">This student will be enrolled in the following Edmingle courses based
                        on their selected fee plan ({{ $feePlan->fee_name }}):</p>

                    <div class="bg-gray-50 rounded-lg border border-gray-200 overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Semester</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Edmingle Batch
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($feePlan->semesterBatches as $batch)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900 font-medium">Sem {{ $batch->semester }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            @if($batch->edmingle_course_id)
                                                <span class="block text-[#00bde0] font-medium">{{ $batch->display_name }}</span>
                                                <span class="block text-xs mt-0.5">Course ID: {{ $batch->edmingle_course_id }}</span>
                                                @if($batch->edmingle_batch_id)
                                                    <span class="block text-xs">Batch ID: {{ $batch->edmingle_batch_id }}</span>
                                                @endif
                                            @else
                                                <span class="text-red-500 text-xs">No Edmingle course assigned</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-10 h-10 bg-amber-500 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 14c-.77 1.333.192 3 1.732 3z">
                                    </path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-amber-900">Fee Plan Missing Semester Batches</p>
                                <p class="text-xs text-amber-700 mt-1">
                                    @if(empty($student->subscription) || $student->subscription === 'Not Selected')
                                        The student does not have any subscription plan selected.
                                    @else
                                        The fee plan "{{ $student->subscription }}" does not have any semester batches assigned.
                                    @endif
                                    Please assign a valid fee plan below to continue:
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Select Fee Plan</label>
                        <select name="fee_plan_name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#00bde0] bg-white">
                            <option value="">-- Choose Fee Plan --</option>
                            @foreach($allPlans as $plan)
                                @if($plan->semesterBatches()->exists())
                                    <option value="{{ $plan->fee_name }}">{{ $plan->fee_name }} (₹{{ number_format($plan->amount, 2) }})</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" :disabled="loading" x-on:click="$dispatch('close-modal', 'enroll-student')"
                    class="px-5 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2 text-sm font-semibold text-white bg-gradient-to-br from-[#00bde0] to-[#0099b8] rounded-lg hover:shadow-md shadow-sm transition-all flex items-center gap-2">
                    <svg x-show="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    Enroll & Approve
                </button>
            </div>
        </form>
    </x-modal>

    <!-- Reject School Payment Modal -->
    <x-modal name="reject-school-payment" title="Reject School Payment" maxWidth="lg">
        <form action="{{ route('admin.registrations.reject-school-payment', $student->student_id) }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div class="flex items-start gap-4 p-1">
                    <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-500 mb-4">Please provide a reason for rejecting this payment submission. This reason will be shown to the student when they return to re-upload their payment screenshot.</p>
                        <textarea name="reason" rows="4" required
                            class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-red-500 focus:border-red-500 transition-all"
                            placeholder="e.g. Transaction ID does not match, screenshot is blank..."></textarea>
                    </div>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close-modal', 'reject-school-payment')"
                    class="px-5 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 shadow-md transition-all">
                    Confirm Rejection
                </button>
            </div>
        </form>
    </x-modal>

    <!-- Change Batch Modal -->
    @if(auth()->user()->isMasterAdmin() || auth()->user()->hasPermission(10001))
    <x-modal name="change-batch-modal" title="Change Student Semester Batch" maxWidth="lg">
        <form action="{{ route('admin.registrations.update-batch', $student->student_id) }}" method="POST">
            @csrf
            <div class="space-y-4 p-1">
                <p class="text-sm text-gray-500">
                    Update the active semester batch for <strong class="text-gray-900">{{ $student->fname }} {{ $student->lname }}</strong>. The subscription fee plan will be auto-assigned from the selected batch.
                </p>

                <!-- Current Financial & Plan Status Box -->
                <div class="p-3.5 bg-gray-50 border border-gray-200 rounded-lg text-xs space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500 font-bold uppercase">Current Subscription Plan</span>
                        <span class="font-bold text-[#00bde0] text-sm">{{ $student->subscription ?: 'Not Selected' }}</span>
                    </div>
                    @php
                        $paidInvoice = $student->invoices->where('status', 'PAID')->first();
                        $paidAmount = $paidInvoice ? (float)$paidInvoice->total_due : 0;
                    @endphp
                    <div class="flex justify-between items-center pt-1.5 border-t border-gray-200">
                        <span class="text-gray-500 font-bold uppercase">Fee Payment Status</span>
                        <span class="font-bold {{ $student->fees_status === 'Paid' ? 'text-green-700 bg-green-50 px-2 py-0.5 rounded border border-green-200' : 'text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200' }}">
                            {{ $student->fees_status }} @if($paidAmount > 0)(₹{{ number_format($paidAmount, 2) }})@endif
                        </span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Target Semester Batch</label>
                    <select name="target_sembatch_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#00bde0] bg-white">
                        <option value="">-- Select Semester Batch --</option>
                        @if(isset($batches))
                            @foreach($batches as $b)
                                @php
                                    $isCurrent = isset($activeBatch) && $activeBatch && $activeBatch->sembatches_id == $b->sembatches_id;
                                    $matchedPlan = $b->feePlans->where('fee_name', $student->subscription)->first() 
                                                ?? ($student->school_id ? $b->feePlans->where('school_id', $student->school_id)->first() : null)
                                                ?? $b->feePlans->whereNull('school_id')->first();
                                    $planName = $matchedPlan ? $matchedPlan->fee_name : 'No Fee Plan';
                                    $planAmt = $matchedPlan ? '₹' . number_format($matchedPlan->amount, 2) : 'N/A';
                                @endphp
                                <option value="{{ $b->sembatches_id }}" {{ $isCurrent ? 'selected' : '' }}>
                                    {{ $b->display_name ?: 'Semester ' . $b->sembatches_id }} — Plan: {{ $planName }} ({{ $planAmt }}) {{ $isCurrent ? '[Current]' : '' }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                @if($student->fees_status === 'Paid')
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox" name="confirm_fee_mismatch" value="1" class="mt-0.5 rounded text-[#00bde0] focus:ring-[#00bde0]">
                            <span class="text-xs text-amber-900 font-medium leading-relaxed">
                                <strong>Accountant Audit Check:</strong> Confirm batch update even if the target batch fee plan amount differs from the student's paid invoice amount (₹{{ number_format($paidAmount, 2) }}).
                            </span>
                        </label>
                    </div>
                @endif
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close-modal', 'change-batch-modal')"
                    class="px-5 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2 text-sm font-semibold text-white bg-gradient-to-br from-[#00bde0] to-[#0099b8] rounded-lg shadow-md hover:shadow-lg transition-all">
                    Save Batch Update
                </button>
            </div>
        </form>
    </x-modal>
    @endif

    @push('scripts')
        <script>
            function openRejectModal() {
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'reject-application' }));
            }

            function openEnrollModal() {
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'enroll-student' }));
            }

            function openRejectSchoolPaymentModal() {
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'reject-school-payment' }));
            }

            function openChangeBatchModal() {
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'change-batch-modal' }));
            }
        </script>
    @endpush
@endsection