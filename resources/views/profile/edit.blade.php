@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Edit Profile</h1>
        <p class="text-sm text-gray-600 mt-1">Update your profile information</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <!-- Success Message -->
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        <!-- Error Message -->
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Validation Errors -->
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="space-y-6">
                @if($student)
                    <!-- Profile Photo Section -->
                    <div class="pb-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Profile Photo</h3>
                        <div class="flex flex-col md:flex-row items-center gap-6" x-data="{ photoPreview: null }">
                            <div class="w-24 h-24 rounded-full overflow-hidden border-2 border-gray-200 bg-gray-50 flex-shrink-0 relative group">
                                <template x-if="!photoPreview">
                                    @if($student->document && $student->document->photo)
                                        <img src="{{ asset($student->document->photo) }}" alt="Profile Photo" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-400 bg-gray-100">
                                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </template>
                                <template x-if="photoPreview">
                                    <img :src="photoPreview" class="w-full h-full object-cover">
                                </template>
                            </div>
                            
                            <div class="flex-1 w-full">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Upload New Photograph
                                </label>
                                <input 
                                    type="file" 
                                    name="photo" 
                                    accept="image/*"
                                    class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#00bde0]/10 file:text-[#00bde0] hover:file:bg-[#00bde0]/20"
                                    @change="const file = $el.files[0]; if (file) { const reader = new FileReader(); reader.onload = (e) => { photoPreview = e.target.result; }; reader.readAsDataURL(file); }"
                                >
                                <p class="text-xs text-gray-500 mt-1.5">
                                    Allowed formats: JPG, JPEG, PNG. Max size: 2MB.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Personal Information Section -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="fname" class="block text-sm font-medium text-gray-700 mb-1">
                                First Name <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="fname" 
                                name="fname" 
                                value="{{ old('fname', $user->fname) }}"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('fname') border-red-500 @enderror"
                            >
                            @error('fname')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="mname" class="block text-sm font-medium text-gray-700 mb-1">
                                Middle Name
                            </label>
                            <input 
                                type="text" 
                                id="mname" 
                                name="mname" 
                                value="{{ old('mname', $user->mname) }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent"
                            >
                        </div>
                        <div>
                            <label for="lname" class="block text-sm font-medium text-gray-700 mb-1">
                                Last Name <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="lname" 
                                name="lname" 
                                value="{{ old('lname', $user->lname) }}"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('lname') border-red-500 @enderror"
                            >
                            @error('lname')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Contact Information Section -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value="{{ old('email', $user->email) }}"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('email') border-red-500 @enderror"
                            >
                            @error('email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                                    Phone
                                </label>
                                <input 
                                    type="text" 
                                    id="phone" 
                                    name="phone" 
                                    value="{{ old('phone', $user->phone) }}"
                                    maxlength="10" 
                                    inputmode="numeric" 
                                    pattern="[0-9]{10}"
                                    oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)" 
                                    onkeypress="return /[0-9]/.test(event.key)" 
                                    onpaste="setTimeout(()=>{this.value=this.value.replace(/\D/g,'').slice(0,10)},0)"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('phone') border-red-500 @enderror"
                                    placeholder="10-digit phone number"
                                >
                                @error('phone')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="whatsapp" class="block text-sm font-medium text-gray-700 mb-1">
                                    WhatsApp
                                </label>
                                <input 
                                    type="text" 
                                    id="whatsapp" 
                                    name="whatsapp" 
                                    value="{{ old('whatsapp', $user->whatsapp) }}"
                                    maxlength="10" 
                                    inputmode="numeric" 
                                    pattern="[0-9]{10}"
                                    oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)" 
                                    onkeypress="return /[0-9]/.test(event.key)" 
                                    onpaste="setTimeout(()=>{this.value=this.value.replace(/\D/g,'').slice(0,10)},0)"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('whatsapp') border-red-500 @enderror"
                                    placeholder="10-digit WhatsApp number"
                                >
                                @error('whatsapp')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Address Section -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Address</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="address1" class="block text-sm font-medium text-gray-700 mb-1">
                                Address Line 1
                            </label>
                            <input 
                                type="text" 
                                id="address1" 
                                name="address1" 
                                value="{{ old('address1', $user->address1) }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent"
                            >
                        </div>
                        <div>
                            <label for="address2" class="block text-sm font-medium text-gray-700 mb-1">
                                Address Line 2
                            </label>
                            <input 
                                type="text" 
                                id="address2" 
                                name="address2" 
                                value="{{ old('address2', $user->address2) }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent"
                            >
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="city" class="block text-sm font-medium text-gray-700 mb-1">
                                    City
                                </label>
                                <input 
                                    type="text" 
                                    id="city" 
                                    name="city" 
                                    value="{{ old('city', $user->city) }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent"
                                >
                            </div>
                            <div>
                                <label for="pincode" class="block text-sm font-medium text-gray-700 mb-1">
                                    Pincode
                                </label>
                                <input 
                                    type="text" 
                                    id="pincode" 
                                    name="pincode" 
                                    value="{{ old('pincode', $user->pincode) }}"
                                    maxlength="6" 
                                    inputmode="numeric" 
                                    pattern="[0-9]{6}"
                                    oninput="this.value=this.value.replace(/\D/g,'').slice(0,6)" 
                                    onkeypress="return /[0-9]/.test(event.key)" 
                                    onpaste="setTimeout(()=>{this.value=this.value.replace(/\D/g,'').slice(0,6)},0)"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('pincode') border-red-500 @enderror"
                                    placeholder="6-digit pincode"
                                >
                                @error('pincode')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                @if($student)
                    <!-- Student-Specific Section -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Achievements & Activities</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="achievement_stsang" class="block text-sm font-medium text-gray-700 mb-1">
                                    Satsang Achievements
                                </label>
                                <textarea 
                                    name="achievement_stsang" 
                                    id="achievement_stsang" 
                                    rows="4"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('achievement_stsang') border-red-500 @enderror"
                                    placeholder="Describe your satsang-related achievements..."
                                >{{ old('achievement_stsang', $student->achievement_stsang) }}</textarea>
                                @error('achievement_stsang')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="achievement_academic" class="block text-sm font-medium text-gray-700 mb-1">
                                    Academic Achievements
                                </label>
                                <textarea 
                                    name="achievement_academic" 
                                    id="achievement_academic" 
                                    rows="4"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('achievement_academic') border-red-500 @enderror"
                                    placeholder="Describe your academic achievements..."
                                >{{ old('achievement_academic', $student->achievement_academic) }}</textarea>
                                @error('achievement_academic')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="activities" class="block text-sm font-medium text-gray-700 mb-1">
                                    Extracurricular Activities
                                </label>
                                <textarea 
                                    name="activities" 
                                    id="activities" 
                                    rows="4"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('activities') border-red-500 @enderror"
                                    placeholder="Describe your extracurricular activities..."
                                >{{ old('activities', $student->activities) }}</textarea>
                                @error('activities')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('profile.show') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                        Cancel
                    </a>
                    <button type="submit" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition">
                        Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
