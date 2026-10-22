@extends('layouts.app')

@section('content')
    @php
        $initiatorRole = $roles->firstWhere('rolename', 'Initiator');
        $initiatorRoleId = $initiatorRole ? $initiatorRole->role_id : 3;
    @endphp
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Create New User</h1>
            <p class="mt-1 text-sm text-gray-600">Add a new user to the system</p>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf

                <div class="space-y-4">
                    <!-- Role -->
                    <div>
                        <label for="role_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Role <span class="text-red-500">*</span>
                        </label>
                        <select id="role_id" name="role_id" required
                            onchange="toggleFinancialType(this.value)"
                            class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('role_id') border-red-500 @enderror">
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->role_id }}" {{ old('role_id') == $role->role_id ? 'selected' : '' }}>
                                    {{ $role->rolename }}
                                </option>
                            @endforeach
                        </select>
                        @error('role_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Financial Type -->
                    <div id="financial_type_container" class="{{ old('role_id') == $initiatorRoleId ? '' : 'hidden' }}">
                        <label for="financial_type" class="block text-sm font-medium text-gray-700 mb-1">
                            Financial Type Model <span class="text-red-500">*</span>
                        </label>
                        <select id="financial_type" name="financial_type"
                            class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                            <option value="volunteer" {{ old('financial_type') == 'volunteer' ? 'selected' : '' }}>Volunteer Model (Free)</option>
                            <option value="paid" {{ old('financial_type', 'paid') == 'paid' ? 'selected' : '' }}>Paid Model</option>
                        </select>
                    </div>

                    <!-- First Name -->
                    <div>
                        <label for="fname" class="block text-sm font-medium text-gray-700 mb-1">
                            First Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="fname" name="fname" value="{{ old('fname') }}" required maxlength="100"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('fname') border-red-500 @enderror">
                        @error('fname')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Middle Name -->
                    <div>
                        <label for="mname" class="block text-sm font-medium text-gray-700 mb-1">
                            Middle Name
                        </label>
                        <input type="text" id="mname" name="mname" value="{{ old('mname') }}" maxlength="100"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('mname') border-red-500 @enderror">
                        @error('mname')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Last Name -->
                    <div>
                        <label for="lname" class="block text-sm font-medium text-gray-700 mb-1">
                            Last Name
                        </label>
                        <input type="text" id="lname" name="lname" value="{{ old('lname') }}" maxlength="100"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('lname') border-red-500 @enderror">
                        @error('lname')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">
                            Username <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="username" name="username" value="{{ old('username') }}" required
                            maxlength="255"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('username') border-red-500 @enderror">
                        @error('username')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            Email
                        </label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" maxlength="255"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('email') border-red-500 @enderror">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                            Phone
                        </label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                            maxlength="10" inputmode="numeric" pattern="[0-9]{10}" placeholder="10-digit mobile number"
                            oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('phone') border-red-500 @enderror">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- WhatsApp -->
                    <div>
                        <label for="whatsapp" class="block text-sm font-medium text-gray-700 mb-1">
                            WhatsApp
                        </label>
                        <input type="text" id="whatsapp" name="whatsapp" value="{{ old('whatsapp') }}"
                            maxlength="10" inputmode="numeric" pattern="[0-9]{10}" placeholder="10-digit mobile number"
                            oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('whatsapp') border-red-500 @enderror">
                        @error('whatsapp')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="pswd" class="block text-sm font-medium text-gray-700 mb-1">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="pswd" name="pswd" minlength="8"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('pswd') border-red-500 @enderror">
                        <p class="mt-1 text-xs text-gray-500">Minimum 8 characters. Required if PIN is not provided.</p>
                        @error('pswd')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- PIN -->
                    <div>
                        <label for="pin" class="block text-sm font-medium text-gray-700 mb-1">
                            PIN <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="pin" name="pin"
                            maxlength="4" inputmode="numeric" pattern="[0-9]{4}" placeholder="4-digit PIN"
                            oninput="this.value=this.value.replace(/\D/g,'').slice(0,4)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('pin') border-red-500 @enderror">
                        <p class="mt-1 text-xs text-gray-500">Exactly 4 digits. Required if Password is not provided.</p>
                        @error('pin')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Address 1 -->
                    <div>
                        <label for="address1" class="block text-sm font-medium text-gray-700 mb-1">
                            Address Line 1
                        </label>
                        <input type="text" id="address1" name="address1" value="{{ old('address1') }}" maxlength="500"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('address1') border-red-500 @enderror">
                        @error('address1')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Address 2 -->
                    <div>
                        <label for="address2" class="block text-sm font-medium text-gray-700 mb-1">
                            Address Line 2
                        </label>
                        <input type="text" id="address2" name="address2" value="{{ old('address2') }}" maxlength="500"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('address2') border-red-500 @enderror">
                        @error('address2')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- City -->
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-1">
                            City
                        </label>
                        <input type="text" id="city" name="city" value="{{ old('city') }}" maxlength="100"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('city') border-red-500 @enderror">
                        @error('city')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Pincode -->
                    <div>
                        <label for="pincode" class="block text-sm font-medium text-gray-700 mb-1">
                            Pincode
                        </label>
                        <input type="text" id="pincode" name="pincode" value="{{ old('pincode') }}" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" placeholder="6-digit pincode" oninput="this.value=this.value.replace(/\D/g,'').slice(0,6)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('pincode') border-red-500 @enderror">
                        @error('pincode')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Inactive Status -->
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="inactive" value="1" {{ old('inactive') ? 'checked' : '' }}
                                class="h-4 w-4 text-[#00bde0] focus:ring-[#00bde0] border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Mark as Inactive</span>
                        </label>
                        @error('inactive')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200">
                    <a href="{{ route('users.index') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                        Cancel
                    </a>
                    <button type="submit"
                        class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition">
                        Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
@push('scripts')
<script>
    function toggleFinancialType(roleId) {
        const container = document.getElementById('financial_type_container');
        const initiatorRoleId = "{{ $initiatorRoleId }}";
        if (roleId == initiatorRoleId) { // Initiator
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
        }
    }
</script>
@endpush
@endsection