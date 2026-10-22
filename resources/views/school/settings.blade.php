@extends('layouts.app')

@section('title', 'Registration Settings')

@section('content')
<div class="py-6" x-data="{ copied: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Alerts -->
        @if (session('success'))
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-r-lg flex items-center justify-between shadow-sm">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-medium text-green-800">{{ session('success') }}</span>
                </div>
            </div>
        @endif

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

        <!-- Page Header -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-900">Registration URL & Settings</h2>
            <p class="mt-2 text-sm text-gray-600">Configure public registration settings, fees, services, payment details, and generate a unique registration link for your school.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Side: Custom Settings Form -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <form action="{{ route('school.registration-settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="space-y-6">
                        
                        <!-- Form Title -->
                        <div>
                            <label for="reg_form_name" class="block text-sm font-semibold text-gray-700">Custom Form Title</label>
                            <input type="text" name="reg_form_name" id="reg_form_name" value="{{ old('reg_form_name', $school->reg_form_name) }}" placeholder="e.g. BAPS Academy Student Admission Form" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent text-sm">
                            @error('reg_form_name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Date range validation -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="reg_start_date" class="block text-sm font-semibold text-gray-700">Start Date & Time</label>
                                <input type="datetime-local" name="reg_start_date" id="reg_start_date" value="{{ old('reg_start_date', $school->reg_start_date ? $school->reg_start_date->format('Y-m-d\TH:i') : '') }}" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent text-sm">
                                @error('reg_start_date')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="reg_end_date" class="block text-sm font-semibold text-gray-700">End Date & Time</label>
                                <input type="datetime-local" name="reg_end_date" id="reg_end_date" value="{{ old('reg_end_date', $school->reg_end_date ? $school->reg_end_date->format('Y-m-d\TH:i') : '') }}" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent text-sm">
                                @error('reg_end_date')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Notes / Instructions -->
                        <div>
                            <label for="notes" class="block text-sm font-semibold text-gray-700">Special Notes / Instructions for Students</label>
                            <textarea name="notes" id="notes" rows="3" placeholder="Enter special instructions or notes to show students during registration..." class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent text-sm">{{ old('notes', $school->notes) }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Custom Fees Section -->
                        <div x-data="{ 
                            fees: {{ json_encode($school->custom_fees ?? []) }}, 
                            addFee() { this.fees.push({name: '', amount: 0}) }, 
                            removeFee(index) { this.fees.splice(index, 1) } 
                        }" class="border-t border-gray-200 pt-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-1">Custom School Fees</h3>
                            <p class="text-xs font-medium text-gray-500 mb-4">Each entry is a separate fee plan. A student picks one of them when paying &mdash; they are not added together.</p>
                            <div class="space-y-4">
                                <template x-for="(fee, index) in fees" :key="index">
                                    <div class="flex items-center gap-4">
                                        <div class="flex-1">
                                            <input type="text" :name="'custom_fees['+index+'][name]'" x-model="fee.name" placeholder="Fee Name (e.g. Lab Fee)" class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] text-sm" required>
                                        </div>
                                        <div class="w-32">
                                            <input type="number" :name="'custom_fees['+index+'][amount]'" x-model="fee.amount" placeholder="Amount" step="0.01" min="0" class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] text-sm" required>
                                        </div>
                                        <button type="button" @click="removeFee(index)" class="text-red-500 hover:text-red-700">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                                <button type="button" @click="addFee()" class="inline-flex items-center text-sm font-semibold text-[#0099b8] hover:text-[#007b94]">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Add Fee Entry
                                </button>
                            </div>
                        </div>

                        <!-- Services & Features Section -->
                        <div x-data="{ 
                            features: {{ json_encode($school->services_features ?? []) }}, 
                            addFeature() { this.features.push('') }, 
                            removeFeature(index) { this.features.splice(index, 1) } 
                        }" class="border-t border-gray-200 pt-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Included Services & Features</h3>
                            <div class="space-y-4">
                                <template x-for="(feature, index) in features" :key="index">
                                    <div class="flex items-center gap-4">
                                        <div class="flex-1">
                                            <input type="text" :name="'services_features['+index+']'" x-model="features[index]" placeholder="Feature/Service (e.g. WiFi Access)" class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] text-sm" required>
                                        </div>
                                        <button type="button" @click="removeFeature(index)" class="text-red-500 hover:text-red-700">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                                <button type="button" @click="addFeature()" class="inline-flex items-center text-sm font-semibold text-[#0099b8] hover:text-[#007b94]">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Add Service/Feature
                                </button>
                            </div>
                        </div>

                        <!-- Offline & QR Payment Details -->
                        <div class="border-t border-gray-200 pt-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Payment Configuration</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="offline_payment_name" class="block text-sm font-semibold text-gray-700">Account / Receiver Name</label>
                                    <input type="text" name="offline_payment_name" id="offline_payment_name" value="{{ old('offline_payment_name', $school->offline_payment_name) }}" placeholder="e.g. BAPS Pramukh Academy" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] text-sm">
                                    @error('offline_payment_name')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="offline_payment_contact" class="block text-sm font-semibold text-gray-700">Payment Helpdesk / Contact</label>
                                    <input type="text" name="offline_payment_contact" id="offline_payment_contact" value="{{ old('offline_payment_contact', $school->offline_payment_contact) }}" placeholder="e.g. +91 98765 43210" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] text-sm">
                                    @error('offline_payment_contact')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6 items-end">
                                <div>
                                    <label for="qr_code" class="block text-sm font-semibold text-gray-700">Direct Payment UPI / QR Code</label>
                                    <input type="file" name="qr_code" id="qr_code" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-[#00bde0]/10 file:text-[#0099b8] hover:file:bg-[#00bde0]/20">
                                    @error('qr_code')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                @if($school->qr_code_path)
                                    <div class="bg-gray-50 p-4 border border-gray-200 rounded-lg flex items-center justify-between">
                                        <span class="text-sm font-semibold text-gray-700">Current QR Code:</span>
                                        <a href="{{ Storage::url($school->qr_code_path) }}" target="_blank" class="inline-flex items-center text-xs font-semibold text-[#0099b8] hover:underline">
                                            View Image
                                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2"></path>
                                            </svg>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>

                    <div class="mt-8 flex justify-end">
                        <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-semibold rounded-lg shadow-sm text-white bg-gradient-to-r from-[#00bde0] to-[#0099b8] hover:from-[#00a8c7] hover:to-[#0089a5] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#00bde0] transition-colors duration-150">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right Side: Link Generation -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Registration Link</h3>
                    
                    @if ($school->reg_token)
                        @php
                            $registrationLink = url('/register-student/school/' . $school->reg_token);
                        @endphp
                        
                        <p class="text-sm text-gray-600 mb-4">Your registration token is active and valid. Share this URL with students of your school:</p>
                        
                        <!-- Link Display & Copy -->
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 flex items-center justify-between mb-4">
                            <span class="text-xs font-mono text-gray-700 truncate mr-2">{{ $registrationLink }}</span>
                            <button @click="navigator.clipboard.writeText('{{ $registrationLink }}'); copied = true; setTimeout(() => copied = false, 2000)" class="text-gray-500 hover:text-[#00bde0] transition-colors" title="Copy URL">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!copied">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                                </svg>
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="copied" x-cloak>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <div class="mb-6">
                            <div class="flex items-center space-x-2 text-xs text-gray-500">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span>
                                    Validity: 
                                    @if ($school->reg_start_date && $school->reg_end_date)
                                        <strong>{{ $school->reg_start_date->format('d M, Y H:i') }}</strong> to <strong>{{ $school->reg_end_date->format('d M, Y H:i') }}</strong>
                                    @else
                                        <span class="text-amber-500">Not scheduled (Always Active)</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    @else
                        <div class="p-4 bg-amber-50 rounded-lg text-amber-800 text-sm mb-6 flex items-start">
                            <svg class="w-5 h-5 text-amber-500 mr-2 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <div>
                                <span class="font-semibold">No URL Generated</span>
                                <p class="mt-1 text-xs">A registration link is not currently active for this school. Generate one below to enable student admissions.</p>
                            </div>
                        </div>
                    @endif
                </div>

                <form action="{{ route('school.registration-settings.generate-token') }}" method="POST" class="mt-6">
                    @csrf
                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-3 border border-gray-300 text-sm font-semibold rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#00bde0] transition-colors duration-150">
                        <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89M9 11l3-3m0 0l3 3m-3-3v12"></path>
                        </svg>
                        {{ $school->reg_token ? 'Regenerate Link' : 'Generate Registration Link' }}
                    </button>
                </form>
            </div>

        </div>

    </div>
</div>
@endsection
