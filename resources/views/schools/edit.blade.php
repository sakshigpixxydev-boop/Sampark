@extends('layouts.app')

@section('content')
    @php
        use Illuminate\Support\Facades\Storage;
    @endphp
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Edit School</h1>
            <p class="text-sm text-gray-600 mt-1">Update school information</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <form action="{{ route('schools.update', $school->school_id) }}" method="POST" enctype="multipart/form-data">
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
                    <!-- Basic Information Section -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label for="schoolname" class="block text-sm font-medium text-gray-700 mb-1">
                                    School Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="schoolname" id="schoolname"
                                    value="{{ old('schoolname', $school->schoolname) }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('schoolname') border-red-500 @enderror"
                                    required>
                                @error('schoolname')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="schooltype_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    School Type <span class="text-red-500">*</span>
                                </label>
                                <select name="schooltype_id" id="schooltype_id"
                                    class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('schooltype_id') border-red-500 @enderror"
                                    required>
                                    <option value="">Select School Type</option>
                                    @foreach($schoolTypes as $type)
                                        <option value="{{ $type->schooltype_id }}" {{ old('schooltype_id', $school->schooltype_id) == $type->schooltype_id ? 'selected' : '' }}>
                                            {{ $type->schooltypename }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('schooltype_id')
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
                                        <option value="{{ $board->eduboard_id }}" {{ old('eduboard_id', $school->eduboard_id) == $board->eduboard_id ? 'selected' : '' }}>
                                            {{ $board->boardname }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('eduboard_id')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="establishedyear" class="block text-sm font-medium text-gray-700 mb-1">
                                    Established Year
                                </label>
                                <input type="number" name="establishedyear" id="establishedyear"
                                    value="{{ old('establishedyear', $school->establishedyear) }}" min="1800"
                                    max="{{ date('Y') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('establishedyear') border-red-500 @enderror">
                                @error('establishedyear')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="logourl" class="block text-sm font-medium text-gray-700 mb-1">
                                    School Logo
                                </label>
                                @if($school->logourl)
                                    <div class="mb-2">
                                        <img src="{{ Storage::url($school->logourl) }}" alt="Current Logo"
                                            class="h-20 w-20 object-cover rounded">
                                        <p class="text-xs text-gray-500 mt-1">Current logo</p>
                                    </div>
                                @endif
                                <input type="file" name="logourl" id="logourl" accept="image/*"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('logourl') border-red-500 @enderror">
                                <p class="text-xs text-gray-500 mt-1">Max 2MB, JPG, PNG, GIF. Leave empty to keep current
                                    logo.</p>
                                @error('logourl')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
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
                                        <option value="{{ $country->country_id }}" {{ old('country_id', $selectedCountry?->country_id) == $country->country_id ? 'selected' : '' }}>
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
                                    required>
                                    <option value="">Select State</option>
                                    @foreach($states as $state)
                                        <option value="{{ $state->state_id }}" {{ old('state_id', $selectedState?->state_id) == $state->state_id ? 'selected' : '' }}>
                                            {{ $state->sname }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="district_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    District <span class="text-red-500">*</span>
                                </label>
                                <select name="district_id" id="district_id"
                                    class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent"
                                    required>
                                    <option value="">Select District</option>
                                    @foreach($districts as $district)
                                        <option value="{{ $district->dist_id }}" {{ old('district_id', $selectedDistrict?->dist_id) == $district->dist_id ? 'selected' : '' }}>
                                            {{ $district->dname }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="takula_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    Taluka <span class="text-red-500">*</span>
                                </label>
                                <select name="takula_id" id="takula_id"
                                    class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('takula_id') border-red-500 @enderror"
                                    required>
                                    <option value="">Select Taluka</option>
                                    @foreach($talukos as $taluka)
                                        <option value="{{ $taluka->takula_id }}" {{ old('takula_id', $school->takula_id) == $taluka->takula_id ? 'selected' : '' }}>
                                            {{ $taluka->tname }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('takula_id')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="address1" class="block text-sm font-medium text-gray-700 mb-1">
                                    Address Line 1
                                </label>
                                <input type="text" name="address1" id="address1"
                                    value="{{ old('address1', $school->address1) }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                            </div>

                            <div class="md:col-span-2">
                                <label for="address2" class="block text-sm font-medium text-gray-700 mb-1">
                                    Address Line 2
                                </label>
                                <input type="text" name="address2" id="address2"
                                    value="{{ old('address2', $school->address2) }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                            </div>

                            <div>
                                <label for="city_village" class="block text-sm font-medium text-gray-700 mb-1">
                                    City/Village
                                </label>
                                <input type="text" name="city_village" id="city_village"
                                    value="{{ old('city_village', $school->city_village) }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                            </div>

                            <div>
                                <label for="pincode" class="block text-sm font-medium text-gray-700 mb-1">
                                    Pincode
                                </label>
                                <input type="text" name="pincode" id="pincode"
                                    value="{{ old('pincode', $school->pincode) }}" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" placeholder="6-digit pincode" oninput="this.value=this.value.replace(/\D/g,'').slice(0,6)"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information Section -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="adminname" class="block text-sm font-medium text-gray-700 mb-1">
                                    Admin Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="adminname" id="adminname"
                                    value="{{ old('adminname', $school->adminname) }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('adminname') border-red-500 @enderror"
                                    required>
                                @error('adminname')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="admincontact" class="block text-sm font-medium text-gray-700 mb-1">
                                    Admin Contact <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="admincontact" id="admincontact"
                                    value="{{ old('admincontact', $school->admincontact) }}" maxlength="10" inputmode="numeric" pattern="[0-9]{10}" oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('admincontact') border-red-500 @enderror"
                                    required>
                                @error('admincontact')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                                    Phone
                                </label>
                                <input type="text" name="phone" id="phone" value="{{ old('phone', $school->phone) }}" minlength="10" maxlength="10" inputmode="numeric" pattern="[0-9]{10}" oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                            </div>

                            <div>
                                <label for="mobile" class="block text-sm font-medium text-gray-700 mb-1">
                                    Mobile
                                </label>
                                <input type="text" name="mobile" id="mobile" value="{{ old('mobile', $school->mobile) }}" maxlength="10" inputmode="numeric" pattern="[0-9]{10}" oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                    Email
                                </label>
                                <input type="email" name="email" id="email" value="{{ old('email', $school->email) }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('email') border-red-500 @enderror">
                                @error('email')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="website" class="block text-sm font-medium text-gray-700 mb-1">
                                    Website
                                </label>
                                <input type="url" name="website" id="website" value="{{ old('website', $school->website) }}"
                                    placeholder="https://example.com"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('website') border-red-500 @enderror">
                                @error('website')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Status Section -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Status</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                    School Status <span class="text-red-500">*</span>
                                </label>
                                <select name="status" id="status"
                                    class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent"
                                    required>
                                    <option value="0" {{ old('status', $school->status) == '0' ? 'selected' : '' }}>Lead
                                    </option>
                                    <option value="1" {{ old('status', $school->status) == '1' ? 'selected' : '' }}>Final
                                    </option>
                                </select>
                            </div>

                            <div class="flex items-center pt-7">
                                <label class="flex items-center">
                                    <input type="checkbox" name="inactive" value="1" {{ old('inactive', $school->inactive) ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-[#00bde0] focus:ring-[#00bde0]">
                                    <span class="ml-2 text-sm text-gray-700">Mark as Inactive</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200">
                    <a href="{{ route('schools.index') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                        Cancel
                    </a>
                    <button type="submit"
                        class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition">
                        Update School
                    </button>
                </div>
            </form>
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