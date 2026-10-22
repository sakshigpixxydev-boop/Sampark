@extends('layouts.app')

@section('title', 'Edit Role')

@section('content')
<script>
function toggleModule(moduleId) {
    const module = document.getElementById(moduleId);
    const icon = document.getElementById('icon-' + moduleId);

    if (module.classList.contains('hidden')) {
        module.classList.remove('hidden');
        icon.style.transform = 'rotate(90deg)';
    } else {
        module.classList.add('hidden');
        icon.style.transform = 'rotate(0deg)';
    }
}

function toggleModulePerms(moduleId, checked) {
    const checkboxes = document.querySelectorAll(`input[data-module="${moduleId}"]`);
    checkboxes.forEach(checkbox => {
        checkbox.checked = checked;
    });
}
</script>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-900">Edit Role</h2>
    <p class="text-sm text-gray-600 mt-1">Update role information and permissions</p>
</div>

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <form action="{{ route('roles.update', $role) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <!-- Role Name -->
            <div>
                <label for="rolename" class="block text-sm font-medium text-gray-700 mb-1">
                    Role Name <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="rolename"
                       id="rolename"
                       value="{{ old('rolename', $role->rolename) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('rolename') border-red-500 @enderror"
                       required>
                @error('rolename')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Predefined Flag (read-only) -->
            @if($role->ispredefined)
            <div>
                <div class="flex items-center p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm text-blue-800">This is a predefined role and cannot be deleted.</span>
                </div>
            </div>
            @endif

            <!-- Permissions -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    Assign Permissions
                </label>
                <div class="border border-gray-300 rounded-lg p-4 max-h-[600px] overflow-y-auto">
                    @if($permissions->count() > 0)
                        @php
                             $modules = [
                                'Roles' => [10001, 10002, 10003],
                                'Permissions' => [10011, 10012, 10013],
                                'Users' => [10021, 10022, 10023],
                                'Schools' => [10031, 10032, 10033],
                                'Students' => [10041, 10042, 10043],
                                'Dashboard' => [10051, 10052, 10053],
                                'Invoices' => [10061, 10062, 10063],
                                'Payments' => [10071, 10072, 10073],
                                'Upload Batches' => [10081, 10082, 10083],
                                'Student Batches' => [10091, 10092, 10093],
                                'Guardians' => [10101, 10102, 10103],
                                'Guardian Messages' => [10211, 10212, 10213],
                                'Parcels' => [10111, 10112, 10113],
                                'School Types' => [10121, 10122, 10123],
                                'Educational Boards' => [10131, 10132, 10133],
                                'Student Types' => [10141, 10142, 10143],
                                'Semester Batches' => [10151, 10152, 10153],
                                'Fees Plan & School Fees Plans' => [10161, 10162, 10163],
                                'Discount Rules' => [10171, 10172, 10173],
                                'Payment Modes' => [10181, 10182, 10183],
                                'Parcel Companies' => [10191, 10192, 10193],
                                'Lead Management' => [10201, 10202, 10203],
                                'Initiator Fees Plans & Payments' => [10301, 10302],
                                'Events' => [10401, 10402, 10403],
                                'Growth Card' => [10501, 10502, 10503, 10504],
                                'Materials' => [10601, 10602, 10603],
                                'Material Requests' => [10611, 10612, 10613],
                                'Blogs' => [10701, 10702, 10703],
                                'Ticketing' => [10801, 10802, 10803],
                                'Student Announcements' => [10221, 10222, 10223],
                                'Notifications' => [11101, 11102, 11103],
                                'Certificates' => [10901, 10902, 10903],
                                'Agreement Templates' => [11001, 11002, 11003],
                                'Mail Templates' => [11301, 11302, 11303],
                                'Registrations' => [11201],
                                'Student History' => [11401, 11402, 11403, 11404],
                                'Laravel Logs' => [11501, 11502, 11503],
                                'School Enhancements' => [11601, 11602, 11603, 11604, 11605, 11606],
                                'Student Leads' => [11701, 11702, 11703],
                                'Enquiries' => [11704, 11705, 11706],
                                'Registration Control' => [11707],
                            ];
                        @endphp

                        <div class="space-y-3">
                            @foreach($modules as $moduleName => $permKeys)
                                @php
                                    $modulePerms = $permissions->whereIn('permkey', $permKeys);
                                @endphp
                                @if($modulePerms->count() > 0)
                                <div class="border border-gray-200 rounded-lg">
                                    <div class="bg-gray-50 px-4 py-3 flex items-center justify-between cursor-pointer hover:bg-gray-100 transition"
                                         onclick="toggleModule('module-{{ str_replace(' ', '-', strtolower($moduleName)) }}')">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 text-gray-500 transform transition-transform module-icon"
                                                 id="icon-module-{{ str_replace(' ', '-', strtolower($moduleName)) }}"
                                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                            <span class="ml-2 font-medium text-gray-900">{{ $moduleName }}</span>
                                        </div>
                                        <label class="flex items-center" onclick="event.stopPropagation()">
                                            <input type="checkbox"
                                                   class="module-select-all rounded border-gray-300 text-[#00bde0] focus:ring-[#00bde0]"
                                                   onchange="toggleModulePerms('module-{{ str_replace(' ', '-', strtolower($moduleName)) }}', this.checked)">
                                            <span class="ml-2 text-sm text-gray-600">Select All</span>
                                        </label>
                                    </div>
                                    <div class="hidden p-4 space-y-2 bg-white" id="module-{{ str_replace(' ', '-', strtolower($moduleName)) }}">
                                        @foreach($modulePerms as $permission)
                                        <label class="flex items-start pl-6">
                                            <input type="checkbox"
                                                   name="permissions[]"
                                                   value="{{ $permission->permission_id }}"
                                                   {{ in_array($permission->permission_id, old('permissions', $rolePermissions)) ? 'checked' : '' }}
                                                   class="mt-1 rounded border-gray-300 text-[#00bde0] focus:ring-[#00bde0] module-perm"
                                                   data-module="module-{{ str_replace(' ', '-', strtolower($moduleName)) }}">
                                            <span class="ml-2 text-sm text-gray-700">{{ $permission->permname }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No permissions available.</p>
                    @endif
                </div>
                @error('permissions')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex justify-end space-x-3">
            <a href="{{ route('roles.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                Cancel
            </a>
            <button type="submit" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition">
                Update Role
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function toggleModule(moduleId) {
    const module = document.getElementById(moduleId);
    const icon = document.getElementById('icon-' + moduleId);

    if (module.classList.contains('hidden')) {
        module.classList.remove('hidden');
        icon.style.transform = 'rotate(90deg)';
    } else {
        module.classList.add('hidden');
        icon.style.transform = 'rotate(0deg)';
    }
}

function toggleModulePerms(moduleId, checked) {
    const checkboxes = document.querySelectorAll(`input[data-module="${moduleId}"]`);
    checkboxes.forEach(checkbox => {
        checkbox.checked = checked;
    });
}

// Update "Select All" checkbox state when individual permissions change
document.addEventListener('DOMContentLoaded', function() {
    const moduleCheckboxes = document.querySelectorAll('.module-perm');

    moduleCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const moduleId = this.dataset.module;
            const allCheckboxes = document.querySelectorAll(`input[data-module="${moduleId}"]`);
            const selectAllCheckbox = document.querySelector(`#${moduleId}`).previousElementSibling.querySelector('.module-select-all');

            const allChecked = Array.from(allCheckboxes).every(cb => cb.checked);
            selectAllCheckbox.checked = allChecked;
        });
    });

    // Initialize "Select All" checkboxes state
    document.querySelectorAll('.module-select-all').forEach(selectAll => {
        const moduleDiv = selectAll.closest('.border').querySelector('[id^="module-"]');
        const moduleId = moduleDiv.id;
        const allCheckboxes = document.querySelectorAll(`input[data-module="${moduleId}"]`);
        const allChecked = Array.from(allCheckboxes).every(cb => cb.checked);
        selectAll.checked = allChecked;
    });
});
</script>
@endpush
