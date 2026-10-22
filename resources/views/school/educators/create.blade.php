@extends('layouts.app')

@section('title', 'Add Educator')

@section('content')
<div class="py-6">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Alerts -->
        @if (session('error'))
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg flex items-center justify-between shadow-sm">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-medium text-red-800">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- Breadcrumb / Header -->
        <div class="mb-8">
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-2">
                    <li class="inline-flex items-center">
                        <a href="{{ route('school.educators.index') }}" class="text-sm font-semibold text-gray-500 hover:text-gray-700">Educators</a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="ml-1 md:ml-2 text-sm font-semibold text-gray-900">Add Educator</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h2 class="text-3xl font-bold text-gray-900">Add Educator</h2>
            <p class="mt-2 text-sm text-gray-600">Create a new educator and assign their permitted module access.</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <form action="{{ route('school.educators.store') }}" method="POST">
                @csrf
                
                <div class="space-y-6">
                    <!-- Personal details -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="fname" class="block text-sm font-semibold text-gray-700">First Name <span class="text-red-500">*</span></label>
                            <input type="text" name="fname" id="fname" value="{{ old('fname') }}" required class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent text-sm">
                            @error('fname')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="mname" class="block text-sm font-semibold text-gray-700">Middle Name</label>
                            <input type="text" name="mname" id="mname" value="{{ old('mname') }}" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent text-sm">
                            @error('mname')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="lname" class="block text-sm font-semibold text-gray-700">Last Name</label>
                            <input type="text" name="lname" id="lname" value="{{ old('lname') }}" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent text-sm">
                            @error('lname')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Contact & Auth -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="username" class="block text-sm font-semibold text-gray-700">Username <span class="text-red-500">*</span></label>
                            <input type="text" name="username" id="username" value="{{ old('username') }}" required class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent text-sm">
                            @error('username')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent text-sm">
                            @error('email')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="phone" class="block text-sm font-semibold text-gray-700">Phone</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent text-sm">
                            @error('phone')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="pin" class="block text-sm font-semibold text-gray-700">4-Digit PIN</label>
                            <input type="password" name="pin" id="pin" maxlength="4" placeholder="Optional security PIN" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent text-sm">
                            @error('pin')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="pswd" class="block text-sm font-semibold text-gray-700">Password <span class="text-red-500">*</span></label>
                            <input type="password" name="pswd" id="pswd" required class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent text-sm">
                            @error('pswd')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Permissions checkbox list -->
                    <div>
                        <span class="block text-sm font-semibold text-gray-700 mb-2">Permissions</span>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg border border-gray-200">
                            @foreach ($educatorPermissions as $perm)
                                <label class="relative flex items-start">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" name="permissions[]" value="{{ $perm->permkey }}" class="focus:ring-[#00bde0] h-4 w-4 text-[#00bde0] border-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <span class="font-semibold text-gray-800">{{ $perm->permname }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('permissions')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <a href="{{ route('school.educators.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-semibold rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#00bde0] transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-semibold rounded-lg shadow-sm text-white bg-gradient-to-r from-[#00bde0] to-[#0099b8] hover:from-[#00a8c7] hover:to-[#0089a5] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#00bde0] transition-all">
                        Save Educator
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
