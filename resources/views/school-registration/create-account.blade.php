@extends('layouts.guest')

@section('title', 'Create Account')

@section('content')
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl w-full">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-900">Create School Admin Account</h2>
                <p class="mt-2 text-sm text-gray-600">Fill in your details to get started</p>
            </div>

            <form method="POST" action="{{ route('school-registration.create-account') }}"
                x-data="adminRegisterForm()"
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 space-y-6">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <!-- Hidden inputs for fname, mname, lname to be submitted to backend -->
                <input type="hidden" name="fname" x-model="fname">
                <input type="hidden" name="mname" x-model="mname">
                <input type="hidden" name="lname" x-model="lname">

                <div>
                    <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="full_name" id="full_name" x-model="fullName" @input="splitName" required
                        placeholder="e.g. FIRSTNAME MIDDLENAME LASTNAME"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('fname') border-red-500 @enderror">
                    @error('fname')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span
                                class="text-red-500">*</span></label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('email') border-red-500 @enderror">
                        @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Contact Number <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="phone" id="phone" x-model="phone" required minlength="10"
                            maxlength="10" inputmode="numeric" pattern="[0-9]{10}"
                            @input="onPhoneInput"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('phone') border-red-500 @enderror">
                        @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Login Username <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="username" id="username" value="{{ old('username') }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('username') border-red-500 @enderror">
                        @error('username')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="whatsapp" class="block text-sm font-medium text-gray-700">WhatsApp</label>
                            <label class="flex items-center gap-1.5 cursor-pointer group">
                                <input type="checkbox" name="whatsapp_same_as_phone" x-model="whatsappSameAsPhone"
                                    @change="if($el.checked) whatsapp = phone"
                                    class="w-4 h-4 text-[#00bde0] focus:ring-[#00bde0] border-gray-300 rounded">
                                <span class="text-xs text-gray-500 group-hover:text-[#00bde0] transition-colors">Same as Contact</span>
                            </label>
                        </div>
                        <input type="text" name="whatsapp" id="whatsapp" x-model="whatsapp"
                            :readonly="whatsappSameAsPhone"
                            maxlength="10" inputmode="numeric" pattern="[0-9]{10}"
                            @input="onWhatsappInput"
                            :class="{'bg-gray-100 text-gray-500 cursor-not-allowed': whatsappSameAsPhone}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('whatsapp') border-red-500 @enderror">
                        @error('whatsapp')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="pswd" class="block text-sm font-medium text-gray-700 mb-1">Password <span
                                class="text-red-500">*</span></label>
                        <input type="password" name="pswd" id="pswd" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('pswd') border-red-500 @enderror">
                        @error('pswd')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="pswd_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password
                            <span class="text-red-500">*</span></label>
                        <input type="password" name="pswd_confirmation" id="pswd_confirmation" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <a href="{{ route('school-registration.landing', ['token' => $token]) }}"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        Back
                    </a>
                    <button type="submit"
                        class="px-6 py-2 bg-[#00bde0] text-white rounded-lg hover:bg-[#00a8c9] transition-colors">
                        Create Account & Continue
                    </button>
                </div>
            </form>
        </div>
    </div>

@push('scripts')
@php
    $oldFullName = old('full_name');
    if (empty($oldFullName) && (old('fname') || old('mname') || old('lname'))) {
        $oldFullName = implode(' ', array_filter([old('fname'), old('mname'), old('lname')]));
    }
@endphp
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('adminRegisterForm', () => ({
            fullName: @json($oldFullName ?? ''),
            fname: @json(old('fname', '')),
            mname: @json(old('mname', '')),
            lname: @json(old('lname', '')),
            phone: @json(old('phone', '')),
            whatsapp: @json(old('whatsapp', '')),
            whatsappSameAsPhone: {{ old('whatsapp_same_as_phone') ? 'true' : 'false' }},

            init() {
                if (this.whatsappSameAsPhone) {
                    this.whatsapp = this.phone;
                }
                this.splitName();
            },

            splitName() {
                this.fullName = this.fullName.toUpperCase().replace(/[^A-Z\s]/g, '');
                const fullName = this.fullName.trim();
                const nameParts = fullName.split(/\s+/);
                if (nameParts.length > 0 && nameParts[0] !== '') {
                    this.fname = nameParts[0];
                    if (nameParts.length > 2) {
                        this.mname = nameParts.slice(1, -1).join(' ');
                        this.lname = nameParts[nameParts.length - 1];
                    } else if (nameParts.length === 2) {
                        this.mname = '';
                        this.lname = nameParts[1];
                    } else {
                        this.mname = '';
                        this.lname = '';
                    }
                } else {
                    this.fname = '';
                    this.mname = '';
                    this.lname = '';
                }
            },

            syncWhatsapp() {
                if (this.whatsappSameAsPhone) {
                    this.whatsapp = this.phone;
                }
            },

            onPhoneInput(e) {
                this.phone = e.target.value.replace(/\D/g, '').slice(0, 10);
                this.syncWhatsapp();
            },

            onWhatsappInput(e) {
                this.whatsapp = e.target.value.replace(/\D/g, '').slice(0, 10);
            }
        }));
    });
</script>
@endpush
@endsection