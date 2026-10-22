@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('schools.index') }}" class="text-gray-500 hover:text-gray-700 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Agreement Workflow</h1>
            </div>
            <p class="text-sm text-gray-600 mt-1">Manage registration agreement and approvals for <span class="font-semibold text-gray-800">{{ $school->schoolname }}</span></p>
        </div>
        
        <div>
            @if($school->agreement_status === 'pending')
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800 border border-yellow-200 shadow-sm">
                    <span class="w-2.5 h-2.5 rounded-full bg-yellow-500 mr-2 animate-pulse"></span>
                    Pending PDF Upload
                </span>
            @elseif($school->agreement_status === 'uploaded')
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-blue-100 text-blue-800 border border-blue-200 shadow-sm">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500 mr-2 animate-pulse"></span>
                    Signed PDF Uploaded
                </span>
            @elseif($school->agreement_status === 'initiator_approved')
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-indigo-100 text-indigo-800 border border-indigo-200 shadow-sm">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 mr-2 animate-pulse"></span>
                    Pending Coordinator Review
                </span>
            @elseif($school->agreement_status === 'coordinator_approved')
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-green-100 text-green-800 border border-green-200 shadow-sm">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-500 mr-2"></span>
                    Finalized & Approved (Read-Only)
                </span>
            @endif
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl mb-6 shadow-sm flex items-start gap-3">
            <svg class="w-5 h-5 text-green-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl mb-6 shadow-sm flex items-start gap-3">
            <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Stepper Progress -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
        <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-400 mb-6">Workflow Progress</h2>
        <div class="relative">
            <!-- Line Behind Steps -->
            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                <div class="w-full border-t-2 border-gray-200"></div>
            </div>
            
            <div class="relative flex justify-between">
                <!-- Step 1: Assign & Upload -->
                <div class="text-center bg-white px-2">
                    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full border-2 {{ in_array($school->agreement_status, ['uploaded', 'initiator_approved', 'coordinator_approved']) ? 'bg-[#00bde0] border-[#00bde0] text-white' : 'border-[#00bde0] text-[#00bde0] font-semibold' }}">
                        @if(in_array($school->agreement_status, ['uploaded', 'initiator_approved', 'coordinator_approved']))
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                            </svg>
                        @else
                            1
                        @endif
                    </div>
                    <span class="mt-2 block text-xs font-semibold {{ in_array($school->agreement_status, ['uploaded', 'initiator_approved', 'coordinator_approved']) ? 'text-gray-900' : 'text-[#00bde0]' }}">Signed Upload</span>
                    <span class="block text-[10px] text-gray-500">Initiator Uploads PDF</span>
                </div>

                <!-- Step 2: Initiator Approval -->
                <div class="text-center bg-white px-2">
                    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full border-2 {{ in_array($school->agreement_status, ['initiator_approved', 'coordinator_approved']) ? 'bg-[#00bde0] border-[#00bde0] text-white' : ($school->agreement_status === 'uploaded' ? 'border-[#00bde0] text-[#00bde0] font-semibold' : 'border-gray-200 bg-gray-50 text-gray-400') }}">
                        @if(in_array($school->agreement_status, ['initiator_approved', 'coordinator_approved']))
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                            </svg>
                        @else
                            2
                        @endif
                    </div>
                    <span class="mt-2 block text-xs font-semibold {{ in_array($school->agreement_status, ['initiator_approved', 'coordinator_approved']) ? 'text-gray-900' : ($school->agreement_status === 'uploaded' ? 'text-[#00bde0]' : 'text-gray-400') }}">Initiator Approve</span>
                    <span class="block text-[10px] text-gray-500">Initiator Verifies & Approves</span>
                </div>

                <!-- Step 3: Coordinator Review -->
                <div class="text-center bg-white px-2">
                    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full border-2 {{ $school->agreement_status === 'coordinator_approved' ? 'bg-[#00bde0] border-[#00bde0] text-white' : ($school->agreement_status === 'initiator_approved' ? 'border-[#00bde0] text-[#00bde0] font-semibold' : 'border-gray-200 bg-gray-50 text-gray-400') }}">
                        @if($school->agreement_status === 'coordinator_approved')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                            </svg>
                        @else
                            3
                        @endif
                    </div>
                    <span class="mt-2 block text-xs font-semibold {{ $school->agreement_status === 'coordinator_approved' ? 'text-gray-900' : ($school->agreement_status === 'initiator_approved' ? 'text-[#00bde0]' : 'text-gray-400') }}">Coordinator Review</span>
                    <span class="block text-[10px] text-gray-500">School Coordinator Approves</span>
                </div>

                <!-- Step 4: Finalized -->
                <div class="text-center bg-white px-2">
                    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full border-2 {{ $school->agreement_status === 'coordinator_approved' ? 'bg-green-500 border-green-500 text-white' : 'border-gray-200 bg-gray-50 text-gray-400' }}">
                        @if($school->agreement_status === 'coordinator_approved')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                            </svg>
                        @else
                            4
                        @endif
                    </div>
                    <span class="mt-2 block text-xs font-semibold {{ $school->agreement_status === 'coordinator_approved' ? 'text-green-600 font-bold' : 'text-gray-400' }}">Read-Only</span>
                    <span class="block text-[10px] text-gray-500">Workflow Finalized</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left 2 Columns: Actions and Signature -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Agreement Document Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="border-b border-gray-200 px-6 py-4 bg-gray-50/50 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900">Agreement Actions</h3>
                    @if($school->agreement_pdf_path)
                        <a href="{{ Storage::url($school->agreement_pdf_path) }}" target="_blank" class="text-sm font-semibold text-[#00bde0] hover:text-[#0099b8] flex items-center gap-1.5 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            View Uploaded PDF
                        </a>
                    @endif
                </div>

                <div class="p-6 space-y-6">
                    <!-- Sample Agreement Download -->
                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div class="flex gap-3">
                            <div class="p-3 bg-blue-500 text-white rounded-xl shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-blue-900">Sample Agreement Template</h4>
                                <p class="text-sm text-blue-700 mt-0.5">Download a customizable Word document pre-filled with the active terms & conditions.</p>
                            </div>
                        </div>
                        <a href="{{ route('schools.agreement.download-sample', $school->school_id) }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-blue-500 hover:bg-blue-600 text-white font-bold text-sm rounded-lg transition shrink-0 shadow-sm gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Download Word (.doc)
                        </a>
                    </div>

                    <!-- PDF Upload Section -->
                    @if(!$school->isAgreementReadOnly() && $school->agreement_status !== 'initiator_approved')
                        @if(Auth::user()->hasRole('Initiator') && $school->initiator_id === Auth::id())
                            <div class="border-t border-gray-100 pt-6">
                                <h4 class="font-bold text-gray-800 mb-2">Upload Signed Agreement</h4>
                                <p class="text-xs text-gray-500 mb-4">Please print the agreement, sign it, and upload the scanned copy. Must be in PDF format only (Max size: 5MB).</p>
                                
                                <form action="{{ route('schools.agreement.upload', $school->school_id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                    @csrf
                                    <div class="flex items-center gap-3">
                                        <input type="file" name="agreement_pdf" accept="application/pdf" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-[#00bde0] hover:file:bg-gray-100 transition border border-gray-200 rounded-lg p-1">
                                        <button type="submit" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-bold px-5 py-2.5 rounded-lg transition text-sm shrink-0 shadow-sm">
                                            {{ $school->agreement_pdf_path ? 'Replace PDF' : 'Upload PDF' }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @else
                            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 text-center">
                                <p class="text-sm text-gray-600 font-medium">Currently assigned to Initiator <span class="font-bold text-gray-900">{{ $school->initiator ? ($school->initiator->fname . ' ' . $school->initiator->lname) : 'Unassigned' }}</span> for PDF upload and signing.</p>
                            </div>
                        @endif
                    @else
                        <div class="bg-green-50 border border-green-200 rounded-xl p-5 text-center flex items-center justify-center gap-3">
                            <svg class="w-6 h-6 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            <p class="text-sm font-semibold text-green-800">
                                @if($school->agreement_status === 'initiator_approved')
                                    The agreement has been approved by the Initiator and is under Coordinator Review (Read-Only).
                                @else
                                    The agreement has been finalized and is read-only. No further modifications or file replacements are allowed.
                                @endif
                            </p>
                        </div>
                    @endif

                    <!-- Approval Action Panel -->
                    @if(!$school->isAgreementReadOnly())
                        <!-- Initiator Approval Form -->
                        @if($school->agreement_status === 'uploaded' && Auth::user()->hasRole('Initiator') && $school->initiator_id === Auth::id())
                            <div class="border-t border-gray-100 pt-6 flex justify-between items-center gap-4 bg-yellow-50/50 p-4 rounded-xl border border-yellow-100">
                                <div>
                                    <h4 class="font-bold text-yellow-900">Approve Agreement Step</h4>
                                    <p class="text-xs text-yellow-700">Confirm the uploaded signed document matches terms and is fully correct.</p>
                                </div>
                                <form action="{{ route('schools.agreement.approve-initiator', $school->school_id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-bold px-6 py-2.5 rounded-lg transition text-sm shadow-md">
                                        Approve as Initiator
                                    </button>
                                </form>
                            </div>
                        @endif

                        <!-- Coordinator Approval Form -->
                        @if($school->agreement_status === 'initiator_approved' && (Auth::user()->hasRole('Sankalp Coordinator (School)') || Auth::user()->hasRole('School Coordinator') || Auth::user()->hasRole('Admin')))
                            <div class="border-t border-gray-100 pt-6 flex justify-between items-center gap-4 bg-indigo-50/50 p-4 rounded-xl border border-indigo-100">
                                <div>
                                    <h4 class="font-bold text-indigo-900">Final coordinator Review</h4>
                                    <p class="text-xs text-indigo-700">Perform final review of the signed PDF and lock the agreement.</p>
                                </div>
                                <form action="{{ route('schools.agreement.approve-coordinator', $school->school_id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold px-6 py-2.5 rounded-lg transition text-sm shadow-md">
                                        Approve & Lock (Finalized)
                                    </button>
                                </form>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Signature Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-6">Digital Signature Management</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Upload Signature -->
                    @if(!$school->isAgreementReadOnly() && $school->agreement_status !== 'initiator_approved')
                        <div class="space-y-4">
                            <h4 class="font-bold text-gray-800 text-sm">Upload Your Signature</h4>
                            <p class="text-xs text-gray-500">Provide an image of your signature (PNG, JPG, JPEG) to use on agreement approvals (Max size: 2MB).</p>
                            
                            <form action="{{ route('schools.agreement.upload-signature', $school->school_id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                @csrf
                                <div class="space-y-3">
                                    <input type="file" name="signature_image" accept="image/*" required class="block w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 transition border border-gray-200 rounded-lg p-1">
                                    <button type="submit" class="w-full bg-[#00bde0] hover:bg-[#0099b8] text-white font-semibold py-2 rounded-lg transition text-sm shadow-sm">
                                        Upload/Replace Signature
                                    </button>
                                </div>
                            </form>

                            @if(Auth::user()->signature_path)
                                <div class="border border-gray-100 rounded-xl p-4 bg-gray-50/50 flex flex-col items-center">
                                    <span class="text-xs font-semibold text-gray-500 mb-2">Your Current Signature Image:</span>
                                    <div class="bg-white p-2 rounded-lg shadow-sm border border-gray-200">
                                        <img src="{{ Storage::url(Auth::user()->signature_path) }}" alt="My Signature" class="max-h-20 object-contain">
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 flex items-center justify-center text-center">
                            <p class="text-xs text-gray-500 font-medium">Agreement is finalized. Signature modifications are disabled.</p>
                        </div>
                    @endif

                    <!-- Display Key Actor Signatures -->
                    <div class="space-y-4 border-t md:border-t-0 md:border-l border-gray-100 md:pl-8 pt-6 md:pt-0">
                        <h4 class="font-bold text-gray-800 text-sm">Key Signatures</h4>
                        
                        <div class="space-y-4">
                            <!-- School Admin Signature -->
                            <div class="flex justify-between items-center gap-3">
                                <div>
                                    <p class="text-xs font-bold text-gray-700">School Admin Signature</p>
                                    <p class="text-[10px] text-gray-500">{{ $school->adminname }}</p>
                                </div>
                                <div class="shrink-0">
                                    @if($schoolAdmin && $schoolAdmin->signature_path)
                                        <div class="bg-white border border-gray-200 p-1.5 rounded-lg shadow-sm">
                                            <img src="{{ Storage::url($schoolAdmin->signature_path) }}" alt="School Admin Signature" class="h-10 max-w-[80px] object-contain">
                                        </div>
                                    @else
                                        <span class="text-[10px] text-gray-400 italic">Not Uploaded</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Initiator Signature -->
                            <div class="flex justify-between items-center gap-3">
                                <div>
                                    <p class="text-xs font-bold text-gray-700">Initiator Signature</p>
                                    <p class="text-[10px] text-gray-500">{{ $school->initiator ? ($school->initiator->fname . ' ' . $school->initiator->lname) : 'Unassigned' }}</p>
                                </div>
                                <div class="shrink-0">
                                    @if($school->initiator && $school->initiator->signature_path)
                                        <div class="bg-white border border-gray-200 p-1.5 rounded-lg shadow-sm">
                                            <img src="{{ Storage::url($school->initiator->signature_path) }}" alt="Initiator Signature" class="h-10 max-w-[80px] object-contain">
                                        </div>
                                    @else
                                        <span class="text-[10px] text-gray-400 italic">Not Uploaded</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right 1 Column: Summary & Activity History -->
        <div class="space-y-8">
            <!-- Information Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Assigned Roles</h3>
                <div class="space-y-4">
                    <div>
                        <span class="block text-xs text-gray-500 uppercase tracking-wider">School Admin</span>
                        <span class="block text-sm font-semibold text-gray-800">{{ $school->adminname }}</span>
                        <span class="block text-xs text-gray-500">{{ $school->admincontact }} | {{ $school->email ?? 'No email' }}</span>
                    </div>
                    <div class="border-t border-gray-100 pt-3">
                        <span class="block text-xs text-gray-500 uppercase tracking-wider">Initiator</span>
                        @if($school->initiator)
                            <span class="block text-sm font-semibold text-gray-800">{{ $school->initiator->fname }} {{ $school->initiator->lname }}</span>
                            <span class="block text-xs text-gray-500">{{ $school->initiator->phone ?? 'No phone' }} | {{ $school->initiator->email }}</span>
                        @else
                            <span class="block text-sm text-yellow-600 font-medium">Unassigned</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Activity History Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-6">Activity History</h3>
                
                @if($activities->count() > 0)
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            @foreach($activities as $index => $activity)
                                <li>
                                    <div class="relative pb-8">
                                        <!-- Line to connect icons -->
                                        @if($index < $activities->count() - 1)
                                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                        @endif
                                        
                                        <div class="relative flex space-x-3">
                                            <div>
                                                @if(in_array($activity->event_type, ['INITIATOR_APPROVED', 'COORDINATOR_APPROVED']))
                                                    <span class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center text-green-600 shadow-sm border border-green-200">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                    </span>
                                                @elseif(in_array($activity->event_type, ['AGREEMENT_UPLOADED', 'AGREEMENT_REPLACED']))
                                                    <span class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 shadow-sm border border-blue-200">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                                        </svg>
                                                    </span>
                                                @else
                                                    <span class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 shadow-sm border border-gray-200">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                                        </svg>
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs font-semibold text-gray-800">
                                                    {{ $activity->description }}
                                                </p>
                                                <div class="text-[10px] text-gray-400 mt-1 flex items-center gap-2">
                                                    <span>{{ $activity->created_at->format('M d, Y h:i A') }}</span>
                                                    <span>•</span>
                                                    <span>By {{ $activity->user ? ($activity->user->fname . ' ' . $activity->user->lname) : 'System' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div class="text-center py-6">
                        <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-xs text-gray-400">No activity logged yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
