@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Create Student</h1>
            <p class="text-sm text-gray-600 mt-1">Add a new student to the system</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <!-- Success Message -->
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            <!-- Error Message -->
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            <!-- Validation Errors -->
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="font-medium mb-1">Please correct the following errors:</p>
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('students.store') }}" method="POST" data-validate>
                @csrf

                <div class="space-y-6">
                    <!-- Basic Information Section -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="fname" class="block text-sm font-medium text-gray-700 mb-1">
                                    First Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="fname" id="fname" value="{{ old('fname') }}" data-required
                                    data-max-length="100"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('fname') border-red-500 @enderror">
                                @error('fname')
                                    <p class="error-message text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="mname" class="block text-sm font-medium text-gray-700 mb-1">
                                    Middle Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="mname" id="mname" value="{{ old('mname') }}" data-required
                                    data-max-length="100"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('mname') border-red-500 @enderror">
                                @error('mname')
                                    <p class="error-message text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="lname" class="block text-sm font-medium text-gray-700 mb-1">
                                    Last Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="lname" id="lname" value="{{ old('lname') }}" data-required
                                    data-max-length="100"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('lname') border-red-500 @enderror">
                                @error('lname')
                                    <p class="error-message text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}" data-required
                                    data-email data-max-length="100"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('email') border-red-500 @enderror">
                                @error('email')
                                    <p class="error-message text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="gender" class="block text-sm font-medium text-gray-700 mb-1">
                                    Gender
                                </label>
                                <select name="gender" id="gender"
                                    class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                                    <option value="">Select Gender</option>
                                    <option value="M" {{ old('gender') == 'M' ? 'selected' : '' }}>Male</option>
                                    <option value="F" {{ old('gender') == 'F' ? 'selected' : '' }}>Female</option>
                                    <option value="O" {{ old('gender') == 'O' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>

                            <div>
                                <label for="birthdate" class="block text-sm font-medium text-gray-700 mb-1">
                                    Birth Date
                                </label>
                                <input type="text" name="birthdate" id="birthdate" value="{{ old('birthdate') }}"
                                    class="date-picker w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent" placeholder="DD/MM/YYYY">
                            </div>
                        </div>
                    </div>

                    <!-- Academic Information Section -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Academic Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="studenttype_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    Student Type <span class="text-red-500">*</span>
                                </label>
                                <select name="studenttype_id" id="studenttype_id"
                                    class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('studenttype_id') border-red-500 @enderror"
                                    required>
                                    <option value="">Select Student Type</option>
                                    @foreach($studentTypes as $type)
                                        <option value="{{ $type->studenttype_id }}" {{ old('studenttype_id') == $type->studenttype_id ? 'selected' : '' }}>
                                            {{ $type->studenttypename }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('studenttype_id')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="eduboard_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    Educational Board <span class="text-red-500">*</span>
                                </label>
                                <select name="eduboard_id" id="eduboard_id"
                                    class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('eduboard_id') border-red-500 @enderror"
                                    required>
                                    <option value="">Select Educational Board</option>
                                    @foreach($educationalBoards as $board)
                                        <option value="{{ $board->eduboard_id }}" {{ old('eduboard_id') == $board->eduboard_id ? 'selected' : '' }}>
                                            {{ $board->boardname }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('eduboard_id')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="std_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    Standard
                                </label>
                                <select name="std_id" id="std_id"
                                    data-preserve-order="true"
                                    class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('std_id') border-red-500 @enderror">
                                    <option value="">Select Standard</option>
                                    @foreach($standards as $standard)
                                        <option value="{{ $standard->std_id }}" {{ old('std_id') == $standard->std_id ? 'selected' : '' }}>
                                            {{ $standard->stdname }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('std_id')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="school_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    School
                                </label>
                                <select name="school_id" id="school_id"
                                    class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                                    <option value="">Select School</option>
                                    @foreach($schools as $school)
                                        <option value="{{ $school->school_id }}" {{ old('school_id') == $school->school_id ? 'selected' : '' }}>
                                            {{ $school->schoolname }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="educationmedium" class="block text-sm font-medium text-gray-700 mb-1">
                                    Education Medium
                                </label>
                                <select name="educationmedium" id="educationmedium"
                                    class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                                    <option value="">Select Medium</option>
                                    <option value="1" {{ old('educationmedium') == '1' ? 'selected' : '' }}>Gujarati</option>
                                    <option value="2" {{ old('educationmedium') == '2' ? 'selected' : '' }}>Hindi</option>
                                    <option value="3" {{ old('educationmedium') == '3' ? 'selected' : '' }}>English</option>
                                    <option value="4" {{ old('educationmedium') == '4' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('educationmedium')
                                    <p class="error-message text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- <div>
                                <label for="institute" class="block text-sm font-medium text-gray-700 mb-1">
                                    Institute
                                </label>
                                <input type="text" name="institute" id="institute" value="{{ old('institute') }}"
                                    data-max-length="200"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                                @error('institute')
                                    <p class="error-message text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div> -->

                            <div>
                                <label for="materiallanguage" class="block text-sm font-medium text-gray-700 mb-1">
                                    Material Language
                                </label>
                                <select name="materiallanguage" id="materiallanguage"
                                    class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                                    <option value="">Select Language</option>
                                    @foreach($languages as $lang)
                                        <option value="{{ $lang->lg_id }}" {{ old('materiallanguage') == $lang->lg_id ? 'selected' : '' }}>
                                            {{ $lang->languagename }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('materiallanguage')
                                    <p class="error-message text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Location Section -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Location</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="country_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    Country <span class="text-red-500">*</span>
                                </label>
                                <select name="country_id" id="country_id"
                                    class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent"
                                    required>
                                    <option value="">Select Country</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->country_id }}" {{ old('country_id') == $country->country_id ? 'selected' : '' }}>
                                            {{ $country->cname }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="state_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    State <span class="text-red-500">*</span>
                                </label>
                                <select name="state_id" id="state_id"
                                    class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent"
                                    required disabled>
                                    <option value="">Select State</option>
                                </select>
                            </div>

                            <div>
                                <label for="district_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    District <span class="text-red-500">*</span>
                                </label>
                                <select name="district_id" id="district_id"
                                    class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent"
                                    required disabled>
                                    <option value="">Select District</option>
                                </select>
                            </div>

                            <div>
                                <label for="takula_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    Taluka <span class="text-red-500">*</span>
                                </label>
                                <select name="takula_id" id="takula_id"
                                    class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('takula_id') border-red-500 @enderror"
                                    required disabled>
                                    <option value="">Select Taluka</option>
                                </select>
                                @error('takula_id')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                                    Address
                                </label>
                                <textarea name="address" id="address" rows="2"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">{{ old('address') }}</textarea>
                            </div>

                            <div>
                                <label for="city_village" class="block text-sm font-medium text-gray-700 mb-1">
                                    City/Village
                                </label>
                                <input type="text" name="city_village" id="city_village" value="{{ old('city_village') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                            </div>

                            <div>
                                <label for="pincode" class="block text-sm font-medium text-gray-700 mb-1">
                                    Pincode
                                </label>
                                <input type="text" name="pincode" id="pincode" value="{{ old('pincode') }}"
                                    maxlength="6" inputmode="numeric" pattern="[0-9]{6}" placeholder="6-digit pincode" oninput="this.value=this.value.replace(/\D/g,'').slice(0,6)"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('pincode') border-red-500 @enderror">
                                @error('pincode')
                                    <p class="error-message text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information Section -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="contact_no" class="block text-sm font-medium text-gray-700 mb-1">
                                    Contact Number
                                </label>
                                <input type="text" name="contact_no" id="contact_no" value="{{ old('contact_no') }}"
                                    maxlength="10" inputmode="numeric" pattern="[0-9]{10}" placeholder="10-digit mobile number" oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('contact_no') border-red-500 @enderror">
                                @error('contact_no')
                                    <p class="error-message text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="whatsapp_no" class="block text-sm font-medium text-gray-700 mb-1">
                                    WhatsApp Number
                                </label>
                                <input type="text" name="whatsapp_no" id="whatsapp_no" value="{{ old('whatsapp_no') }}"
                                    maxlength="10" inputmode="numeric" pattern="[0-9]{10}" placeholder="10-digit mobile number" oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('whatsapp_no') border-red-500 @enderror">
                                @error('whatsapp_no')
                                    <p class="error-message text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>



                    <!-- Additional Information Section -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h3>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label for="achievement_stsang" class="block text-sm font-medium text-gray-700 mb-1">
                                    Satsang Achievements
                                </label>
                                <textarea name="achievement_stsang" id="achievement_stsang" rows="2"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">{{ old('achievement_stsang') }}</textarea>
                            </div>

                            <div>
                                <label for="achievement_academic" class="block text-sm font-medium text-gray-700 mb-1">
                                    Academic Achievements
                                </label>
                                <textarea name="achievement_academic" id="achievement_academic" rows="2"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">{{ old('achievement_academic') }}</textarea>
                            </div>

                            <div>
                                <label for="activities" class="block text-sm font-medium text-gray-700 mb-1">
                                    Activities
                                </label>
                                <textarea name="activities" id="activities" rows="2"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">{{ old('activities') }}</textarea>
                            </div>

                            <div>
                                <label for="learnabout" class="block text-sm font-medium text-gray-700 mb-1">
                                    How did you learn about us?
                                </label>
                                <select name="learnabout" id="learnabout"
                                    class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                                    <option value="">Select Option</option>
                                    <option value="1" {{ old('learnabout') == '1' ? 'selected' : '' }}>Social Media</option>
                                    <option value="2" {{ old('learnabout') == '2' ? 'selected' : '' }}>Friend/Family</option>
                                    <option value="3" {{ old('learnabout') == '3' ? 'selected' : '' }}>Website</option>
                                    <option value="4" {{ old('learnabout') == '4' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('learnabout')
                                    <p class="error-message text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="refby" class="block text-sm font-medium text-gray-700 mb-1">
                                    Referred By
                                </label>
                                <input type="text" name="refby" id="refby" value="{{ old('refby') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                            </div>

                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                                    Notes
                                </label>
                                <textarea name="notes" id="notes" rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200">
                    <a href="{{ route('students.index') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                        Cancel
                    </a>
                    <button type="submit"
                        class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition">
                        Create Student
                    </button>
                </div>
            </form>
        </div>

        <!-- Guardians Section Placeholder -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mt-6">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Guardians</h3>
                    <p class="text-sm text-gray-600 mt-1">Manage guardian information for this student</p>
                </div>
                <button type="button" disabled class="bg-gray-300 text-white font-medium px-4 py-2 rounded-lg cursor-not-allowed">
                    Add Guardian
                </button>
            </div>
            
            <div class="text-center py-8 text-gray-500">
                Please create the student profile first to manage guardians.
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const countrySelect = document.getElementById('country_id');
            const stateSelect = document.getElementById('state_id');
            const districtSelect = document.getElementById('district_id');
            const talukaSelect = document.getElementById('takula_id');

            // Load states when country changes
            countrySelect.addEventListener('change', function () {
                const countryId = this.value;

                if (stateSelect.tomselect) stateSelect.tomselect.disable(); else stateSelect.disabled = true;
                if (districtSelect.tomselect) districtSelect.tomselect.disable(); else districtSelect.disabled = true;
                if (talukaSelect.tomselect) talukaSelect.tomselect.disable(); else talukaSelect.disabled = true;

                if (stateSelect.tomselect) {
                    stateSelect.tomselect.clear();
                    stateSelect.tomselect.clearOptions();
                    stateSelect.tomselect.addOption({ value: '', text: 'Select State' });
                    stateSelect.tomselect.addItem('');
                } else {
                    stateSelect.innerHTML = '<option value="">Select State</option>';
                }

                if (districtSelect.tomselect) {
                    districtSelect.tomselect.clear();
                    districtSelect.tomselect.clearOptions();
                    districtSelect.tomselect.addOption({ value: '', text: 'Select District' });
                    districtSelect.tomselect.addItem('');
                } else {
                    districtSelect.innerHTML = '<option value="">Select District</option>';
                }

                if (talukaSelect.tomselect) {
                    talukaSelect.tomselect.clear();
                    talukaSelect.tomselect.clearOptions();
                    talukaSelect.tomselect.addOption({ value: '', text: 'Select Taluka' });
                    talukaSelect.tomselect.addItem('');
                } else {
                    talukaSelect.innerHTML = '<option value="">Select Taluka</option>';
                }

                if (countryId) {
                    fetch("{{ route('locations.api.states', ['country' => ':id']) }}".replace(':id', countryId))
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(state => {
                                if (stateSelect.tomselect) {
                                    stateSelect.tomselect.addOption({ value: state.state_id, text: state.sname });
                                } else {
                                    const option = new Option(state.sname, state.state_id);
                                    stateSelect.add(option);
                                }
                            });
                            if (stateSelect.tomselect) stateSelect.tomselect.enable(); else stateSelect.disabled = false;
                        });
                }
            });

            // Load districts when state changes
            stateSelect.addEventListener('change', function () {
                const stateId = this.value;

                if (districtSelect.tomselect) districtSelect.tomselect.disable(); else districtSelect.disabled = true;
                if (talukaSelect.tomselect) talukaSelect.tomselect.disable(); else talukaSelect.disabled = true;

                if (districtSelect.tomselect) {
                    districtSelect.tomselect.clear();
                    districtSelect.tomselect.clearOptions();
                    districtSelect.tomselect.addOption({ value: '', text: 'Select District' });
                    districtSelect.tomselect.addItem('');
                } else {
                    districtSelect.innerHTML = '<option value="">Select District</option>';
                }

                if (talukaSelect.tomselect) {
                    talukaSelect.tomselect.clear();
                    talukaSelect.tomselect.clearOptions();
                    talukaSelect.tomselect.addOption({ value: '', text: 'Select Taluka' });
                    talukaSelect.tomselect.addItem('');
                } else {
                    talukaSelect.innerHTML = '<option value="">Select Taluka</option>';
                }

                if (stateId) {
                    fetch("{{ route('locations.api.districts', ['state' => ':id']) }}".replace(':id', stateId))
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(district => {
                                if (districtSelect.tomselect) {
                                    districtSelect.tomselect.addOption({ value: district.dist_id, text: district.dname });
                                } else {
                                    const option = new Option(district.dname, district.dist_id);
                                    districtSelect.add(option);
                                }
                            });
                            if (districtSelect.tomselect) districtSelect.tomselect.enable(); else districtSelect.disabled = false;
                        });
                }
            });

            // Load talukos when district changes
            districtSelect.addEventListener('change', function () {
                const districtId = this.value;

                if (talukaSelect.tomselect) talukaSelect.tomselect.disable(); else talukaSelect.disabled = true;

                if (talukaSelect.tomselect) {
                    talukaSelect.tomselect.clear();
                    talukaSelect.tomselect.clearOptions();
                    talukaSelect.tomselect.addOption({ value: '', text: 'Select Taluka' });
                    talukaSelect.tomselect.addItem('');
                } else {
                    talukaSelect.innerHTML = '<option value="">Select Taluka</option>';
                }

                if (districtId) {
                    fetch("{{ route('locations.api.talukos', ['district' => ':id']) }}".replace(':id', districtId))
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(taluka => {
                                if (talukaSelect.tomselect) {
                                    talukaSelect.tomselect.addOption({ value: taluka.takula_id, text: taluka.tname });
                                } else {
                                    const option = new Option(taluka.tname, taluka.takula_id);
                                    talukaSelect.add(option);
                                }
                            });
                            if (talukaSelect.tomselect) talukaSelect.tomselect.enable(); else talukaSelect.disabled = false;
                        });
                }
            });
        });
    </script>
@endsection