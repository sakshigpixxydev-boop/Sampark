@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Add New Guardian</h1>
            <p class="text-sm text-gray-600 mt-1">Create a new guardian record</p>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <form action="{{ route('guardians.store') }}" method="POST">
                @csrf

                <!-- Basic Information Section -->
                <div class="mb-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Student -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Student <span class="text-red-500">*</span>
                            </label>
                            <select name="student_id" required
                                class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('student_id') border-red-500 @enderror">
                                <option value="">Select Student</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->student_id }}" {{ old('student_id', $selectedStudentId) == $student->student_id ? 'selected' : '' }}>
                                        {{ $student->fname }} {{ $student->mname }} {{ $student->lname }}
                                    </option>
                                @endforeach
                            </select>
                            @error('student_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Relation -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Relation <span class="text-red-500">*</span>
                            </label>
                            <select name="relation_id" required
                                class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('relation_id') border-red-500 @enderror">
                                <option value="">Select Relation</option>
                                @foreach($relations as $relation)
                                    <option value="{{ $relation->relation_id }}" {{ old('relation_id') == $relation->relation_id ? 'selected' : '' }}>
                                        {{ $relation->relation }}
                                    </option>
                                @endforeach
                            </select>
                            @error('relation_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- First Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                First Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="fname" value="{{ old('fname') }}" required maxlength="100"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('fname') border-red-500 @enderror">
                            @error('fname')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Middle Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Middle Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="mname" value="{{ old('mname') }}" required maxlength="100"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('mname') border-red-500 @enderror">
                            @error('mname')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Last Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Last Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="lname" value="{{ old('lname') }}" required maxlength="100"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('lname') border-red-500 @enderror">
                            @error('lname')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Gender -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                            <select name="gender"
                                class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('gender') border-red-500 @enderror">
                                <option value="">Select Gender</option>
                                <option value="M" {{ old('gender') === 'M' ? 'selected' : '' }}>Male</option>
                                <option value="F" {{ old('gender') === 'F' ? 'selected' : '' }}>Female</option>
                                <option value="O" {{ old('gender') === 'O' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('gender')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Contact Information Section -->
                <div class="mb-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Phone -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" maxlength="10" inputmode="numeric" pattern="[0-9]{10}"
                                placeholder="10-digit mobile number" oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('phone') border-red-500 @enderror">
                            @error('phone')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" maxlength="255"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('email') border-red-500 @enderror">
                            @error('email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Additional Information Section -->
                <div class="mb-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h2>
                    <div class="space-y-4">
                        <!-- Education -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Education</label>
                            <textarea name="education" rows="2"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('education') border-red-500 @enderror">{{ old('education') }}</textarea>
                            @error('education')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Work Experience -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Work Experience</label>
                            <input type="text" name="work_experience" value="{{ old('work_experience') }}" maxlength="255"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('work_experience') border-red-500 @enderror">
                            @error('work_experience')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- How to Help -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">How Can They Help?</label>
                            <textarea name="how_to_help" rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('how_to_help') border-red-500 @enderror">{{ old('how_to_help') }}</textarea>
                            @error('how_to_help')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex gap-3 pt-4 border-t border-gray-200">
                    <button type="submit"
                        class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-6 py-2 rounded-lg transition">
                        Save Guardian
                    </button>
                    <a href="{{ route('guardians.index') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-6 py-2 rounded-lg transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection