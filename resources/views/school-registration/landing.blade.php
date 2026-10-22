@extends('layouts.guest')

@section('title', 'School Registration')

@section('content')
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Logo/Header -->
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-900">School Registration</h2>
                <p class="mt-2 text-sm text-gray-600">{{ $lead->school_name }}</p>
            </div>

            <!-- Lead Information Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Welcome!</h3>
                <p class="text-sm text-gray-600 mb-4">
                    You've been invited to register your school with Pramukh Sankalp School Management System.
                </p>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">School:</span>
                        <span class="font-medium text-gray-900">{{ $lead->school_name }}</span>
                    </div>
                    @if($lead->email)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Email:</span>
                            <span class="font-medium text-gray-900">{{ $lead->email }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Terms and Conditions Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                @if($lead->tc_content)
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                        <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-[#00bde0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Terms & Conditions
                        </h3>
                    </div>
                    <div class="p-6 max-h-60 overflow-y-auto bg-gray-50/50">
                        <div class="prose prose-sm max-w-none text-gray-600">
                            {!! $lead->tc_content !!}
                        </div>
                    </div>
                @endif
                <div class="px-6 py-4 bg-blue-50/30 border-t border-gray-100">
                    <label class="flex items-start cursor-pointer group">
                        <div class="flex items-center h-5">
                            <input type="checkbox" id="agree_terms" 
                                class="h-4 w-4 rounded border-gray-300 text-[#00bde0] focus:ring-[#00bde0] cursor-pointer">
                        </div>
                        <div class="ml-3 text-sm">
                            <span class="text-gray-700 select-none group-hover:text-gray-900 transition-colors">
                                By proceeding with registration, you agree to the terms mentioned above.
                            </span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Action Cards -->
            <div class="space-y-4">
                <!-- Existing User -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Already have an account?</h3>
                    <p class="text-sm text-gray-600 mb-4">Login to add this school to your account</p>
                    <button id="login_btn" onclick="showLoginForm()" disabled
                        class="w-full px-4 py-2 bg-gray-400 text-white rounded-lg cursor-not-allowed transition-all opacity-70">
                        Login with Existing Account
                    </button>
                </div>

                <!-- New User -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">New to the system?</h3>
                    <p class="text-sm text-gray-600 mb-4">Create a new account to get started</p>
                    <a id="register_btn" href="{{ route('school-registration.create-account.form', ['token' => $lead->registration_token]) }}"
                        class="block w-full px-4 py-2 bg-gray-200 text-gray-500 rounded-lg cursor-not-allowed transition-all opacity-70 text-center pointer-events-none">
                        Create New Account
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Login Modal -->
    <div id="loginModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Login</h3>
                <button onclick="closeLoginForm()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('school-registration.login') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $lead->registration_token }}">

                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" name="username" id="username" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" id="password" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                </div>

                <button type="submit"
                    class="w-full px-4 py-2 bg-[#00bde0] text-white rounded-lg hover:bg-[#00a8c9] transition-colors">
                    Login
                </button>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const agreeCheckbox = document.getElementById('agree_terms');
                const loginBtn = document.getElementById('login_btn');
                const registerBtn = document.getElementById('register_btn');

                if (agreeCheckbox && loginBtn && registerBtn) {
                    agreeCheckbox.addEventListener('change', function() {
                        if (this.checked) {
                            // Enable login button
                            loginBtn.disabled = false;
                            loginBtn.classList.remove('bg-gray-400', 'cursor-not-allowed', 'opacity-70');
                            loginBtn.classList.add('bg-[#00bde0]', 'hover:bg-[#00a8c9]');

                            // Enable register link
                            registerBtn.classList.remove('bg-gray-200', 'text-gray-500', 'cursor-not-allowed', 'pointer-events-none', 'opacity-70');
                            registerBtn.classList.add('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
                        } else {
                            // Disable login button
                            loginBtn.disabled = true;
                            loginBtn.classList.add('bg-gray-400', 'cursor-not-allowed', 'opacity-70');
                            loginBtn.classList.remove('bg-[#00bde0]', 'hover:bg-[#00a8c9]');

                            // Disable register link
                            registerBtn.classList.add('bg-gray-200', 'text-gray-500', 'cursor-not-allowed', 'pointer-events-none', 'opacity-70');
                            registerBtn.classList.remove('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
                        }
                    });
                }
            });

            function showLoginForm() {
                document.getElementById('loginModal').classList.remove('hidden');
            }

            function closeLoginForm() {
                document.getElementById('loginModal').classList.add('hidden');
            }

            // Close modal on outside click
            document.getElementById('loginModal')?.addEventListener('click', function (e) {
                if (e.target === this) {
                    closeLoginForm();
                }
            });
        </script>
    @endpush
@endsection