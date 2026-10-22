@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Edit Discount Rule</h1>
        <p class="mt-1 text-sm text-gray-600">Update discount rule details</p>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-4">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('discount-rules.update', $discountRule->discount_id) }}" method="POST" class="space-y-6" x-data="{ scope: '{{ old('scope', $discountRule->scope) }}', discountType: '{{ old('discount_type', $discountRule->discount_type) }}' }">
            @csrf
            @method('PUT')

            <div>
                <label for="scope" class="block text-sm font-medium text-gray-700 mb-1">
                    Discount Scope <span class="text-red-500">*</span>
                </label>
                <select name="scope" id="scope" x-model="scope" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('scope') border-red-500 @enderror">
                    <option value="SCHOOL">School</option>
                    <option value="STUDENT">Student</option>
                </select>
                @error('scope')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div x-show="scope === 'SCHOOL'">
                <label for="school_id" class="block text-sm font-medium text-gray-700 mb-1">
                    School <span class="text-red-500">*</span>
                </label>
                <select name="school_id" id="school_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('school_id') border-red-500 @enderror">
                    <option value="">-- Select School --</option>
                    @foreach($schools as $school)
                        <option value="{{ $school->school_id }}" {{ old('school_id', $discountRule->school_id) == $school->school_id ? 'selected' : '' }}>
                            {{ $school->schoolname }}
                        </option>
                    @endforeach
                </select>
                @error('school_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div x-show="scope === 'STUDENT'">
                <label for="student_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Student <span class="text-red-500">*</span>
                </label>
                <select name="student_id" id="student_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('student_id') border-red-500 @enderror">
                    <option value="">-- Select Student --</option>
                    @foreach($students as $student)
                        <option value="{{ $student->student_id }}" {{ old('student_id', $discountRule->student_id) == $student->student_id ? 'selected' : '' }}>
                            {{ $student->fname }} {{ $student->mname }} {{ $student->lname }} ({{ $student->email }})
                        </option>
                    @endforeach
                </select>
                @error('student_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Discount Details</h3>

                <div class="space-y-4">
                    <div>
                        <label for="discount_type" class="block text-sm font-medium text-gray-700 mb-1">
                            Discount Type <span class="text-red-500">*</span>
                        </label>
                        <select name="discount_type" id="discount_type" x-model="discountType" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('discount_type') border-red-500 @enderror">
                            <option value="FLAT">Flat Amount</option>
                            <option value="PERCENT">Percentage</option>
                        </select>
                        @error('discount_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="discount_value" class="block text-sm font-medium text-gray-700 mb-1">
                            Discount Value <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span x-show="discountType === 'FLAT'" class="absolute left-3 top-2 text-gray-500">₹</span>
                            <input type="number" name="discount_value" id="discount_value" value="{{ old('discount_value', $discountRule->discount_value) }}" step="0.01" min="0" required
                                :class="discountType === 'FLAT' ? 'pl-8' : 'pl-3'"
                                class="w-full pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('discount_value') border-red-500 @enderror">
                            <span x-show="discountType === 'PERCENT'" class="absolute right-3 top-2 text-gray-500">%</span>
                        </div>
                        @error('discount_value')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="applies_to" class="block text-sm font-medium text-gray-700 mb-1">
                            Applies To <span class="text-red-500">*</span>
                        </label>
                        <select name="applies_to" id="applies_to" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('applies_to') border-red-500 @enderror">
                            <option value="ALL" {{ old('applies_to', $discountRule->applies_to) === 'ALL' ? 'selected' : '' }}>All Fees</option>
                            <option value="TUITION" {{ old('applies_to', $discountRule->applies_to) === 'TUITION' ? 'selected' : '' }}>Tuition Only</option>
                        </select>
                        @error('applies_to')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Validity Period</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="valid_from" class="block text-sm font-medium text-gray-700 mb-1">
                            Valid From <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="valid_from" id="valid_from" value="{{ old('valid_from', $discountRule->valid_from->format('d/m/Y')) }}" required
                            class="date-picker w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('valid_from') border-red-500 @enderror" placeholder="DD/MM/YYYY">
                        @error('valid_from')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="valid_to" class="block text-sm font-medium text-gray-700 mb-1">
                            Valid To
                        </label>
                        <input type="text" name="valid_to" id="valid_to" value="{{ old('valid_to', $discountRule->valid_to ? $discountRule->valid_to->format('d/m/Y') : '') }}"
                            class="date-picker w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('valid_to') border-red-500 @enderror" placeholder="DD/MM/YYYY">
                        @error('valid_to')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <div class="flex items-center">
                    <input type="checkbox" name="active" id="active" value="1" {{ old('active', $discountRule->active) ? 'checked' : '' }}
                        class="h-4 w-4 text-[#00bde0] focus:ring-[#00bde0] border-gray-300 rounded">
                    <label for="active" class="ml-2 block text-sm text-gray-900">
                        Active
                    </label>
                </div>
                <p class="mt-1 text-sm text-gray-500">Inactive discount rules will not be applied to invoices</p>
            </div>

            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ route('discount-rules.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                    Cancel
                </a>
                <button type="submit" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition">
                    Update Discount Rule
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
