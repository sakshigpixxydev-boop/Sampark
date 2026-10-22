@extends('layouts.app')

@section('title', 'Complete School Registration')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="text-center">
            <h2 class="text-2xl font-bold text-gray-900">Complete School Registration</h2>
            <p class="mt-2 text-sm text-gray-600">Fill in your school details to complete registration</p>
        </div>

        <form method="POST" action="{{ route('school-registration.submit') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <input type="hidden" name="token" value="{{ $lead->registration_token }}">

            <!-- Section 1: Admin & Basic School Details -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-6">
                <div class="border-b pb-3">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#00bde0]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        Admin & Basic School Details
                    </h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Admin Name -->
                    <div>
                        <label for="adminname" class="block text-sm font-medium text-gray-700 mb-1">
                            Admin Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="adminname" id="adminname" value="{{ old('adminname', Auth::user()->fname . ' ' . Auth::user()->lname) }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('adminname') border-red-500 @enderror">
                        @error('adminname')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- Admin Contact -->
                    <div>
                        <label for="admincontact" class="block text-sm font-medium text-gray-700 mb-1">
                            Admin Contact <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="admincontact" id="admincontact" value="{{ old('admincontact', Auth::user()->phone) }}" required maxlength="10" inputmode="numeric" pattern="[0-9]{10}" oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)" onkeypress="return /[0-9]/.test(event.key)" onpaste="setTimeout(()=>{this.value=this.value.replace(/\D/g,'').slice(0,10)},0)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('admincontact') border-red-500 @enderror">
                        @error('admincontact')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- School Name -->
                    <div class="md:col-span-2">
                        <label for="schoolname" class="block text-sm font-medium text-gray-700 mb-1">
                            School Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="schoolname" id="schoolname" value="{{ old('schoolname', $lead->school_name) }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('schoolname') border-red-500 @enderror">
                        @error('schoolname')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- School Type -->
                    <div>
                        <label for="schooltype_id" class="block text-sm font-medium text-gray-700 mb-1">
                            School Type <span class="text-red-500">*</span>
                        </label>
                        <select name="schooltype_id" id="schooltype_id" required
                            class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('schooltype_id') border-red-500 @enderror">
                            <option value="">Select School Type</option>
                            @foreach($schoolTypes as $type)
                                <option value="{{ $type->schooltype_id }}" {{ old('schooltype_id') == $type->schooltype_id ? 'selected' : '' }}>
                                    {{ $type->schooltypename }}
                                </option>
                            @endforeach
                        </select>
                        @error('schooltype_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- Educational Board -->
                    <div>
                        <label for="eduboard_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Educational Board <span class="text-red-500">*</span>
                        </label>
                        <select name="eduboard_id" id="eduboard_id" required
                            class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('eduboard_id') border-red-500 @enderror">
                            <option value="">Select Board</option>
                            @foreach($educationalBoards as $board)
                                <option value="{{ $board->eduboard_id }}" {{ old('eduboard_id') == $board->eduboard_id ? 'selected' : '' }}>
                                    {{ $board->boardname }}
                                </option>
                            @endforeach
                        </select>
                        @error('eduboard_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- Established Year -->
                    <div>
                        <label for="establishedyear" class="block text-sm font-medium text-gray-700 mb-1">Established Year</label>
                        <input type="number" name="establishedyear" id="establishedyear" value="{{ old('establishedyear') }}" 
                            min="1800" max="{{ date('Y') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                    </div>
                </div>
            </div>

            <!-- Section 2: Contact Details & School Logo -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-6">
                <div class="border-b pb-3">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#00bde0]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        Contact Details & School Logo
                    </h3>
                </div>

                <!-- School Logo (Highlighted in one row) -->
                <div class="bg-gray-50 border border-dashed border-gray-300 rounded-xl p-6">
                    <label for="logourl" class="block text-sm font-semibold text-gray-700 mb-2">School Logo <span class="text-gray-400 font-normal">(Optional)</span></label>
                    <div class="flex flex-col sm:flex-row items-center gap-4">
                        <div class="w-20 h-20 bg-gray-200 rounded-full flex items-center justify-center overflow-hidden border border-gray-200 shrink-0">
                            <img id="logo_preview" src="" class="w-full h-full object-cover hidden">
                            <svg id="logo_placeholder" class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <div class="grow w-full">
                            <input type="file" name="logourl" id="logourl" accept="image/*" onchange="previewLogo(this)"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('logourl') border-red-500 @enderror">
                            <p class="mt-1.5 text-xs text-gray-500">Allowed formats: JPG, JPEG, PNG, GIF. Recommended dimension: Square (e.g. 512x512px). Max size: 2MB.</p>
                        </div>
                    </div>
                    @error('logourl')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $lead->phone) }}" minlength="10" maxlength="10" inputmode="numeric" pattern="[0-9]{10}" oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)" onkeypress="return /[0-9]/.test(event.key)" onpaste="setTimeout(()=>{this.value=this.value.replace(/\D/g,'').slice(0,10)},0)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                    </div>

                    <!-- Mobile -->
                    <div>
                        <label for="mobile" class="block text-sm font-medium text-gray-700 mb-1">Mobile</label>
                        <input type="text" name="mobile" id="mobile" value="{{ old('mobile') }}" maxlength="10" inputmode="numeric" pattern="[0-9]{10}" oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)" onkeypress="return /[0-9]/.test(event.key)" onpaste="setTimeout(()=>{this.value=this.value.replace(/\D/g,'').slice(0,10)},0)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $lead->email) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                    </div>

                    <!-- Website -->
                    <div>
                        <label for="website" class="block text-sm font-medium text-gray-700 mb-1">School Official Website</label>
                        <input type="url" name="website" id="website" value="{{ old('website', $lead->website) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                    </div>
                </div>
            </div>

            <!-- Section 3: Principal / Authorized Person Details -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-6">
                <div class="border-b pb-3">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#00bde0]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        Principal / Primary Authorized Person Details
                    </h3>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" id="same_as_admin" name="same_as_admin" value="1"
                        class="h-4 w-4 rounded border-gray-300 text-[#00bde0] focus:ring-[#00bde0] cursor-pointer">
                    <label for="same_as_admin" class="ml-2 text-sm text-gray-700 cursor-pointer select-none font-medium">
                        Same as Admin
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Role -->
                    <div>
                        <label for="auth_role" class="block text-sm font-medium text-gray-700 mb-1">
                            Role <span class="text-red-500">*</span>
                        </label>
                        <select name="auth_role" id="auth_role" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('auth_role') border-red-500 @enderror">
                            <option value="">Select Role</option>
                            <option value="Admin" {{ old('auth_role') == 'Admin' ? 'selected' : '' }}>Admin</option>
                            <option value="Principal" {{ old('auth_role') == 'Principal' ? 'selected' : '' }}>Principal</option>
                            <option value="Trustee" {{ old('auth_role') == 'Trustee' ? 'selected' : '' }}>Trustee</option>
                            <option value="Others" {{ old('auth_role') == 'Others' ? 'selected' : '' }}>Others</option>
                        </select>
                        @error('auth_role')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- Specify Role -->
                    <div id="specify_role_container" class="hidden">
                        <label for="auth_role_other" class="block text-sm font-medium text-gray-700 mb-1">
                            Specify Role <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="auth_role_other" id="auth_role_other" value="{{ old('auth_role_other') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('auth_role_other') border-red-500 @enderror">
                        @error('auth_role_other')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- Name -->
                    <div>
                        <label for="auth_name" class="block text-sm font-medium text-gray-700 mb-1">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="auth_name" id="auth_name" value="{{ old('auth_name') }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('auth_name') border-red-500 @enderror">
                        @error('auth_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="auth_email" class="block text-sm font-medium text-gray-700 mb-1">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="auth_email" id="auth_email" value="{{ old('auth_email') }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('auth_email') border-red-500 @enderror">
                        @error('auth_email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- Contact Number -->
                    <div>
                        <label for="auth_phone" class="block text-sm font-medium text-gray-700 mb-1">
                            Contact Number <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="auth_phone" id="auth_phone" value="{{ old('auth_phone') }}" required maxlength="10" inputmode="numeric" pattern="[0-9]{10}" oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)" onkeypress="return /[0-9]/.test(event.key)" onpaste="setTimeout(()=>{this.value=this.value.replace(/\D/g,'').slice(0,10)},0)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('auth_phone') border-red-500 @enderror">
                        @error('auth_phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <!-- Toggle for Second Auth Person Detail (Optional) -->
                <div class="pt-4 border-t flex flex-col gap-4">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" id="toggle_auth2" name="toggle_auth2" value="1"
                            {{ old('auth2_role') ? 'checked' : '' }}
                            class="w-4 h-4 rounded border-gray-300 text-[#00bde0] focus:ring-[#00bde0]">
                        <span class="text-sm font-semibold text-gray-700">+ Add Secondary Authorized Person (Optional)</span>
                    </label>

                    <!-- Secondary Auth Fields Block -->
                    <div id="auth2_container" class="{{ old('auth2_role') ? '' : 'hidden' }} space-y-4 pt-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Role -->
                            <div>
                                <label for="auth2_role" class="block text-sm font-medium text-gray-700 mb-1">
                                    Role <span class="text-red-500">*</span>
                                </label>
                                <select name="auth2_role" id="auth2_role"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('auth2_role') border-red-500 @enderror">
                                    <option value="">Select Role</option>
                                    <option value="Admin" {{ old('auth2_role') == 'Admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="Principal" {{ old('auth2_role') == 'Principal' ? 'selected' : '' }}>Principal</option>
                                    <option value="Trustee" {{ old('auth2_role') == 'Trustee' ? 'selected' : '' }}>Trustee</option>
                                    <option value="Others" {{ old('auth2_role') == 'Others' ? 'selected' : '' }}>Others</option>
                                </select>
                                @error('auth2_role')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <!-- Specify Role -->
                            <div id="specify_role2_container" class="hidden">
                                <label for="auth2_role_other" class="block text-sm font-medium text-gray-700 mb-1">
                                    Specify Role <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="auth2_role_other" id="auth2_role_other" value="{{ old('auth2_role_other') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('auth2_role_other') border-red-500 @enderror">
                                @error('auth2_role_other')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <!-- Name -->
                            <div>
                                <label for="auth2_name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="auth2_name" id="auth2_name" value="{{ old('auth2_name') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('auth2_name') border-red-500 @enderror">
                                @error('auth2_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="auth2_email" class="block text-sm font-medium text-gray-700 mb-1">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input type="email" name="auth2_email" id="auth2_email" value="{{ old('auth2_email') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('auth2_email') border-red-500 @enderror">
                                @error('auth2_email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <!-- Contact Number -->
                            <div>
                                <label for="auth2_phone" class="block text-sm font-medium text-gray-700 mb-1">
                                    Contact Number <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="auth2_phone" id="auth2_phone" value="{{ old('auth2_phone') }}" maxlength="10" inputmode="numeric" pattern="[0-9]{10}" oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)" onkeypress="return /[0-9]/.test(event.key)" onpaste="setTimeout(()=>{this.value=this.value.replace(/\D/g,'').slice(0,10)},0)"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('auth2_phone') border-red-500 @enderror">
                                @error('auth2_phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 4: Address / Location Details -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-6">
                <div class="border-b pb-3">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#00bde0]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Address & Location details
                    </h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Pincode -->
                    <div>
                        <label for="pincode" class="block text-sm font-medium text-gray-700 mb-1">Pincode</label>
                        <div class="relative">
                            <input type="text" name="pincode" id="pincode" value="{{ old('pincode') }}" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" placeholder="6-digit pincode" oninput="this.value=this.value.replace(/\D/g,'').slice(0,6); if(this.value.length === 6) fetchAddressByPincode();" onkeypress="return /[0-9]/.test(event.key)" onpaste="setTimeout(()=>{this.value=this.value.replace(/\D/g,'').slice(0,6); if(this.value.length === 6) fetchAddressByPincode();},0)"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                            <div id="pincode_loading" class="absolute right-3 top-2 hidden">
                                <svg class="animate-spin h-5 w-5 text-[#00bde0]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="country_id" id="country_id" value="{{ $indiaCountryId }}">

                    <!-- State -->
                    <div>
                        <label for="state_id" class="block text-sm font-medium text-gray-700 mb-1">State <span class="text-red-500">*</span></label>
                        <select name="state_id" id="state_id" required class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                            <option value="">Select State</option>
                        </select>
                    </div>

                    <!-- District -->
                    <div>
                        <label for="district_id" class="block text-sm font-medium text-gray-700 mb-1">District <span class="text-red-500">*</span></label>
                        <select name="district_id" id="district_id" required class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                            <option value="">Select District</option>
                        </select>
                    </div>

                    <!-- Taluka -->
                    <div>
                        <label for="takula_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Taluka <span class="text-red-500">*</span>
                        </label>
                        <select name="takula_id" id="takula_id" required
                            class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('takula_id') border-red-500 @enderror">
                            <option value="">Select Taluka</option>
                        </select>
                        @error('takula_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- City/Village -->
                    <div>
                        <label for="city_village" class="block text-sm font-medium text-gray-700 mb-1">City/Village</label>
                        <input type="text" name="city_village" id="city_village" value="{{ old('city_village') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                    </div>
                    <!-- Courier Address -->
                    <div class="md:col-span-2">
                        <label for="address1" class="block text-sm font-medium text-gray-700 mb-1">Courier Address</label>
                        <textarea name="address1" id="address1" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">{{ old('address1') }}</textarea>
                    </div>

                    <!-- Geo Location Link/Coordinates -->
                    <div class="md:col-span-2">
                        <label for="geolocation" class="block text-sm font-medium text-gray-700 mb-1">Geo Location (Google Maps Link or Coordinates)</label>
                        <div class="flex gap-2">
                            <input type="text" name="geolocation" id="geolocation" value="{{ old('geolocation') }}" placeholder="Paste Google Maps Link or click 'Get Current Location'"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('geolocation') border-red-500 @enderror">
                            <button type="button" onclick="getCurrentGeolocation()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg border border-gray-300 flex items-center gap-1.5 whitespace-nowrap">
                                📍 Get Current Location
                            </button>
                            <a href="https://www.google.com/maps" target="_blank" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg border border-gray-300 flex items-center gap-1.5 whitespace-nowrap">
                                🗺️ Open Map
                            </a>
                        </div>
                        <p class="mt-1.5 text-xs text-gray-500">Provide maps URL or click coordinates retriever.</p>
                        @error('geolocation')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- Section 5: Terms and Conditions -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-6">
                <div class="border-b pb-3">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#00bde0]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        Terms and Conditions
                    </h3>
                </div>

                <div class="space-y-4">
                    @if($lead->tc_content)
                        <div class="bg-gray-50 border border-gray-200 rounded-lg overflow-hidden">
                            <button type="button" onclick="this.nextElementSibling.classList.toggle('hidden')" 
                                class="w-full px-4 py-2 text-left text-sm font-semibold text-gray-700 hover:bg-gray-100 flex justify-between items-center transition-colors">
                                <span>Terms & Conditions Review</span>
                                <svg class="w-4 h-4 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div class="hidden p-4 max-h-40 overflow-y-auto border-t border-gray-200 bg-white">
                                <div class="prose prose-sm max-w-none text-gray-600">
                                    {!! $lead->tc_content !!}
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="bg-blue-50/30 p-4 rounded-lg border border-blue-100/50">
                        <label class="flex items-start cursor-pointer group">
                            <div class="flex items-center h-5">
                                <input type="checkbox" id="agree_terms" required
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
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3">
                <button type="submit" id="submit_button" disabled
                    class="px-6 py-2 bg-gray-400 text-white rounded-lg cursor-not-allowed transition-all opacity-70">
                    Complete Registration
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        let states = [];
        let districts = [];
        let talukas = [];

        function previewLogo(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('logo_preview');
                    const placeholder = document.getElementById('logo_placeholder');
                    if (preview && placeholder) {
                        preview.src = e.target.result;
                        preview.classList.remove('hidden');
                        placeholder.classList.add('hidden');
                    }
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function getCurrentGeolocation() {
            if (navigator.geolocation) {
                window.notify('info', 'Retrieving current coordinates...');
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const val = `${position.coords.latitude},${position.coords.longitude}`;
                        document.getElementById('geolocation').value = val;
                        window.notify('success', 'Location updated with current coordinates.');
                    },
                    (error) => {
                        console.error(error);
                        window.notify('error', 'Unable to retrieve location automatically. Please search on the map and copy the link/coordinates.');
                    }
                );
            } else {
                window.notify('error', 'Geolocation is not supported by your browser.');
            }
        }

        function clearLocationDropdowns() {
            const stateSelect = document.getElementById('state_id');
            const districtSelect = document.getElementById('district_id');
            const talukaSelect = document.getElementById('takula_id');

            if (stateSelect.tomselect) {
                stateSelect.tomselect.setValue('');
            } else {
                stateSelect.value = '';
            }

            if (districtSelect.tomselect) {
                districtSelect.tomselect.clear();
                districtSelect.tomselect.clearOptions();
                districtSelect.tomselect.addOption({ value: '', text: 'Select District' });
                districtSelect.tomselect.setValue('');
            } else {
                districtSelect.innerHTML = '<option value="">Select District</option>';
                districtSelect.value = '';
            }

            if (talukaSelect.tomselect) {
                talukaSelect.tomselect.clear();
                talukaSelect.tomselect.clearOptions();
                talukaSelect.tomselect.addOption({ value: '', text: 'Select Taluka' });
                talukaSelect.tomselect.setValue('');
            } else {
                talukaSelect.innerHTML = '<option value="">Select Taluka</option>';
                talukaSelect.value = '';
            }
        }

        async function fetchAddressByPincode() {
            const pincodeInput = document.getElementById('pincode');
            const pin = pincodeInput.value;
            const loadingIndicator = document.getElementById('pincode_loading');

            console.log('fetchAddressByPincode called with pin:', pin);

            if (!pin || pin.length !== 6 || isNaN(pin)) return;

            loadingIndicator.classList.remove('hidden');

            try {
                // Fetch pincode details
                console.log('Fetching pincode data for:', pin);
                const res = await fetch(`{{ url('/api/pincode') }}/${pin}`);
                const data = await res.json();
                console.log('Pincode API response:', data);

                if (data && data[0] && data[0].Status === 'Success' && data[0].PostOffice && data[0].PostOffice.length > 0) {
                    const postOffices = data[0].PostOffice;
                    const stateName = postOffices[0].State;
                    const districtName = postOffices[0].District;
                    const talukaName = postOffices[0].Block || postOffices[0].Taluk || postOffices[0].Division;
                    console.log('State from API:', stateName, 'District from API:', districtName, 'Taluka from API:', talukaName);

                    // Match state
                    const matchedState = states.find(s => s.sname.toLowerCase().trim() === stateName.toLowerCase().trim());
                    console.log('Matched state:', matchedState);
                    if (matchedState) {
                        const stateSelect = document.getElementById('state_id');
                        if (stateSelect.tomselect) {
                            stateSelect.tomselect.setValue(matchedState.state_id.toString());
                        } else {
                            stateSelect.value = matchedState.state_id;
                        }
                        stateSelect.dispatchEvent(new Event('change'));

                        // Wait for districts to load (up to 5 seconds)
                        await new Promise((resolve, reject) => {
                            const checkDistricts = setInterval(() => {
                                if (districts.length > 0) {
                                    clearInterval(checkDistricts);
                                    resolve();
                                }
                            }, 100);
                            setTimeout(() => {
                                clearInterval(checkDistricts);
                                reject(new Error('District load timeout'));
                            }, 5000);
                        });

                        // Match district
                        const matchedDist = districts.find(d => d.dname.toLowerCase().trim() === districtName.toLowerCase().trim());
                        console.log('Matched district:', matchedDist);
                        if (matchedDist) {
                            const districtSelect = document.getElementById('district_id');
                            if (districtSelect.tomselect) {
                                districtSelect.tomselect.setValue(matchedDist.dist_id.toString());
                            } else {
                                districtSelect.value = matchedDist.dist_id;
                            }
                            districtSelect.dispatchEvent(new Event('change'));

                            // Wait for talukas to load (up to 5 seconds)
                            await new Promise((resolve, reject) => {
                                const checkTalukas = setInterval(() => {
                                    if (talukas.length > 0) {
                                        clearInterval(checkTalukas);
                                        resolve();
                                    }
                                }, 100);
                                setTimeout(() => {
                                    clearInterval(checkTalukas);
                                    reject(new Error('Taluka load timeout'));
                                }, 5000);
                            });

                            // Try to match taluka if talukaName is available
                            if (talukaName) {
                                const matchedTaluka = talukas.find(t => t.tname.toLowerCase().trim() === talukaName.toLowerCase().trim());
                                console.log('Matched taluka:', matchedTaluka);
                                if (matchedTaluka) {
                                    const talukaSelect = document.getElementById('takula_id');
                                    if (talukaSelect.tomselect) {
                                        talukaSelect.tomselect.setValue(matchedTaluka.takula_id.toString());
                                    } else {
                                        talukaSelect.value = matchedTaluka.takula_id;
                                    }
                                }
                            }
                            
                            window.notify('success', 'Location details updated successfully based on PIN Code.');
                        } else {
                            throw new Error('No matching district found in database.');
                        }
                    } else {
                        throw new Error('No matching state found in database.');
                    }
                } else {
                    throw new Error('Invalid or unknown PIN Code.');
                }
            } catch (err) {
                console.error('Pincode lookup error:', err);
                clearLocationDropdowns();
                window.notify('error', 'Location lookup failed: ' + err.message + ' Please select manually.');
            } finally {
                loadingIndicator.classList.add('hidden');
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const countrySelect = document.getElementById('country_id');
            const stateSelect = document.getElementById('state_id');
            const districtSelect = document.getElementById('district_id');
            const talukaSelect = document.getElementById('takula_id');

            // Load states for India (1) immediately on page load
            const countryId = countrySelect.value || '1';
            if (countryId) {
                if (stateSelect.tomselect) stateSelect.tomselect.disable(); else stateSelect.disabled = true;
                
                fetch("{{ route('locations.api.states', ['country' => ':id']) }}".replace(':id', countryId))
                    .then(response => response.json())
                    .then(data => {
                        states = data;
                        data.forEach(state => {
                            if (stateSelect.tomselect) {
                                stateSelect.tomselect.addOption({ value: state.state_id, text: state.sname });
                            } else {
                                const option = new Option(state.sname, state.state_id);
                                stateSelect.add(option);
                            }
                        });
                        if (stateSelect.tomselect) stateSelect.tomselect.enable(); else stateSelect.disabled = false;

                        // Check if state is old-selected
                        const oldStateId = "{{ old('state_id') }}";
                        if (oldStateId) {
                            if (stateSelect.tomselect) {
                                stateSelect.tomselect.setValue(oldStateId);
                            } else {
                                stateSelect.value = oldStateId;
                            }
                            stateSelect.dispatchEvent(new Event('change'));
                        }
                    });
            }

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
                    districtSelect.value = '';
                }

                if (talukaSelect.tomselect) {
                    talukaSelect.tomselect.clear();
                    talukaSelect.tomselect.clearOptions();
                    talukaSelect.tomselect.addOption({ value: '', text: 'Select Taluka' });
                    talukaSelect.tomselect.addItem('');
                } else {
                    talukaSelect.innerHTML = '<option value="">Select Taluka</option>';
                    talukaSelect.value = '';
                }

                if (stateId) {
                    fetch("{{ route('locations.api.districts', ['state' => ':id']) }}".replace(':id', stateId))
                        .then(response => response.json())
                        .then(data => {
                            districts = data;
                            data.forEach(district => {
                                if (districtSelect.tomselect) {
                                    districtSelect.tomselect.addOption({ value: district.dist_id, text: district.dname });
                                } else {
                                    const option = new Option(district.dname, district.dist_id);
                                    districtSelect.add(option);
                                }
                            });
                            if (districtSelect.tomselect) districtSelect.tomselect.enable(); else districtSelect.disabled = false;

                            // Check if district is old-selected
                            const oldDistrictId = "{{ old('district_id') }}";
                            if (oldDistrictId) {
                                if (districtSelect.tomselect) {
                                    districtSelect.tomselect.setValue(oldDistrictId);
                                } else {
                                    districtSelect.value = oldDistrictId;
                                }
                                districtSelect.dispatchEvent(new Event('change'));
                            }
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
                    talukaSelect.value = '';
                }

                if (districtId) {
                    fetch("{{ route('locations.api.talukos', ['district' => ':id']) }}".replace(':id', districtId))
                        .then(response => response.json())
                        .then(data => {
                            talukas = data;
                            data.forEach(taluka => {
                                if (talukaSelect.tomselect) {
                                    talukaSelect.tomselect.addOption({ value: taluka.takula_id, text: taluka.tname });
                                } else {
                                    const option = new Option(taluka.tname, taluka.takula_id);
                                    talukaSelect.add(option);
                                }
                            });
                            if (talukaSelect.tomselect) talukaSelect.tomselect.enable(); else talukaSelect.disabled = false;

                            // Check if taluka is old-selected
                            const oldTalukaId = "{{ old('takula_id') }}";
                            if (oldTalukaId) {
                                if (talukaSelect.tomselect) {
                                    talukaSelect.tomselect.setValue(oldTalukaId);
                                } else {
                                    talukaSelect.value = oldTalukaId;
                                }
                            }
                        });
                }
            });

            // Same as Admin logic
            const sameAsAdminCheckbox = document.getElementById('same_as_admin');
            const adminNameInput = document.getElementById('adminname');
            const adminContactInput = document.getElementById('admincontact');
            const adminEmail = "{{ Auth::user()->email ?? '' }}";

            const authNameInput = document.getElementById('auth_name');
            const authPhoneInput = document.getElementById('auth_phone');
            const authEmailInput = document.getElementById('auth_email');
            const authRoleSelect = document.getElementById('auth_role');
            const specifyRoleContainer = document.getElementById('specify_role_container');
            const authRoleOtherInput = document.getElementById('auth_role_other');

            function syncAdminDetails() {
                if (sameAsAdminCheckbox.checked) {
                    authNameInput.value = adminNameInput.value;
                    authPhoneInput.value = adminContactInput.value;
                    authEmailInput.value = adminEmail;

                    // Automatically set role to Admin
                    authRoleSelect.value = 'Admin';
                    toggleSpecifyRole();

                    authNameInput.readOnly = true;
                    authPhoneInput.readOnly = true;
                    authEmailInput.readOnly = true;

                    authNameInput.classList.add('bg-gray-100');
                    authPhoneInput.classList.add('bg-gray-100');
                    authEmailInput.classList.add('bg-gray-100');
                } else {
                    authNameInput.readOnly = false;
                    authPhoneInput.readOnly = false;
                    authEmailInput.readOnly = false;

                    authNameInput.classList.remove('bg-gray-100');
                    authPhoneInput.classList.remove('bg-gray-100');
                    authEmailInput.classList.remove('bg-gray-100');
                }
            }

            sameAsAdminCheckbox.addEventListener('change', syncAdminDetails);
            adminNameInput.addEventListener('input', () => {
                if (sameAsAdminCheckbox.checked) {
                    authNameInput.value = adminNameInput.value;
                }
            });
            adminContactInput.addEventListener('input', () => {
                if (sameAsAdminCheckbox.checked) {
                    authPhoneInput.value = adminContactInput.value;
                }
            });

            // Initial trigger of admin details sync if checked on page load
            syncAdminDetails();

            // Role conditional display logic
            function toggleSpecifyRole() {
                if (authRoleSelect.value === 'Others') {
                    specifyRoleContainer.classList.remove('hidden');
                    authRoleOtherInput.required = true;
                } else {
                    specifyRoleContainer.classList.add('hidden');
                    authRoleOtherInput.required = false;
                    authRoleOtherInput.value = '';
                }
            }

            authRoleSelect.addEventListener('change', toggleSpecifyRole);
            toggleSpecifyRole(); // Trigger initially to sync state

            // Secondary Auth logic
            const toggleAuth2Checkbox = document.getElementById('toggle_auth2');
            const auth2Container = document.getElementById('auth2_container');
            const auth2RoleSelect = document.getElementById('auth2_role');
            const specifyRole2Container = document.getElementById('specify_role2_container');
            const auth2RoleOtherInput = document.getElementById('auth2_role_other');

            const auth2Inputs = [
                document.getElementById('auth2_role'),
                document.getElementById('auth2_name'),
                document.getElementById('auth2_email'),
                document.getElementById('auth2_phone')
            ];

            function toggleAuth2Fields() {
                if (toggleAuth2Checkbox && toggleAuth2Checkbox.checked) {
                    if (auth2Container) auth2Container.classList.remove('hidden');
                    auth2Inputs.forEach(input => {
                        if (input) input.required = true;
                    });
                    toggleSpecifyRole2();
                } else {
                    if (auth2Container) auth2Container.classList.add('hidden');
                    auth2Inputs.forEach(input => {
                        if (input) {
                            input.required = false;
                            input.value = '';
                        }
                    });
                    if (auth2RoleOtherInput) {
                        auth2RoleOtherInput.required = false;
                        auth2RoleOtherInput.value = '';
                    }
                    if (specifyRole2Container) specifyRole2Container.classList.add('hidden');
                }
            }

            function toggleSpecifyRole2() {
                if (auth2RoleSelect && auth2RoleSelect.value === 'Others' && toggleAuth2Checkbox && toggleAuth2Checkbox.checked) {
                    if (specifyRole2Container) specifyRole2Container.classList.remove('hidden');
                    if (auth2RoleOtherInput) auth2RoleOtherInput.required = true;
                } else {
                    if (specifyRole2Container) specifyRole2Container.classList.add('hidden');
                    if (auth2RoleOtherInput) {
                        auth2RoleOtherInput.required = false;
                        auth2RoleOtherInput.value = '';
                    }
                }
            }

            if (toggleAuth2Checkbox) {
                toggleAuth2Checkbox.addEventListener('change', toggleAuth2Fields);
                // Call initially to sync state (e.g. on validation redirect back with old input)
                toggleAuth2Fields();
            }
            if (auth2RoleSelect) {
                auth2RoleSelect.addEventListener('change', toggleSpecifyRole2);
            }

            // Terms agreement logic
            const agreeCheckbox = document.getElementById('agree_terms');
            const submitButton = document.getElementById('submit_button');

            if (agreeCheckbox && submitButton) {
                agreeCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        submitButton.disabled = false;
                        submitButton.classList.remove('bg-gray-400', 'cursor-not-allowed', 'opacity-70');
                        submitButton.classList.add('bg-[#00bde0]', 'hover:bg-[#00a8c9]');
                    } else {
                        submitButton.disabled = true;
                        submitButton.classList.add('bg-gray-400', 'cursor-not-allowed', 'opacity-70');
                        submitButton.classList.remove('bg-[#00bde0]', 'hover:bg-[#00a8c9]');
                    }
                });
            }
        });
    </script>
    @endpush
@endsection
