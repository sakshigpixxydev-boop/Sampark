@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-900">Profile</h2>
        <a href="{{ route('profile.edit') }}" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition">
            Edit Profile
        </a>
    </div>

    <!-- Profile Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        @if($student)
            <div class="flex flex-col md:flex-row gap-6 items-center md:items-start pb-6 mb-6 border-b border-gray-100">
                <div class="w-24 h-24 rounded-full overflow-hidden border-2 border-gray-200 bg-gray-50 flex-shrink-0">
                    @if($student->document && $student->document->photo)
                        <img src="{{ asset($student->document->photo) }}" alt="Profile Photo" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-400 bg-gray-100">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    @endif
                </div>
                <div class="text-center md:text-left">
                    <h3 class="text-xl font-bold text-gray-900">{{ $user->fname }} {{ $user->mname }} {{ $user->lname }}</h3>
                    <p class="text-sm text-gray-500">Student ID: #{{ $student->student_id }}</p>
                    <p class="text-sm text-gray-500 mt-1">Application Status: 
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $student->application_status === 'Enrolled' || $student->application_status === 'Approved' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $student->application_status ?: 'Initiated' }}
                        </span>
                    </p>
                </div>
            </div>
        @endif

        <div class="space-y-4">
            <!-- Name -->
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500">First Name</label>
                    <p class="mt-1 text-gray-900">{{ $user->fname }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Middle Name</label>
                    <p class="mt-1 text-gray-900">{{ $user->mname ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Last Name</label>
                    <p class="mt-1 text-gray-900">{{ $user->lname }}</p>
                </div>
            </div>

            <hr>

            <!-- Contact -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500">Email</label>
                    <p class="mt-1 text-gray-900">{{ $user->email ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Phone</label>
                    <p class="mt-1 text-gray-900">{{ $user->phone ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">WhatsApp</label>
                    <p class="mt-1 text-gray-900">{{ $user->whatsapp ?? '-' }}</p>
                </div>
            </div>

            <hr>

            <!-- Address -->
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-500">Address Line 1</label>
                    <p class="mt-1 text-gray-900">{{ $user->address1 ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Address Line 2</label>
                    <p class="mt-1 text-gray-900">{{ $user->address2 ?? '-' }}</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">City</label>
                        <p class="mt-1 text-gray-900">{{ $user->city ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Pincode</label>
                        <p class="mt-1 text-gray-900">{{ $user->pincode ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <hr>

            <!-- Account Info -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500">Username</label>
                    <p class="mt-1 text-gray-900">{{ $user->username }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Role</label>
                    <p class="mt-1 text-gray-900">{{ $user->role->role_name ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Actions -->
    <div class="mt-6 grid grid-cols-2 gap-4">
        <a href="{{ route('password.change') }}" class="bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 font-medium px-4 py-3 rounded-lg transition text-center">
            Change Password
        </a>
        <a href="{{ route('pin.change') }}" class="bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 font-medium px-4 py-3 rounded-lg transition text-center">
            Change PIN
        </a>
    </div>
</div>
@endsection
