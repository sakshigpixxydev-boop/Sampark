@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Edit Parcel</h1>
        <p class="text-sm text-gray-600 mt-1">Update parcel shipment information</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('parcels.update', $parcel->parcel_id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="student_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Student <span class="text-red-500">*</span>
                </label>
                <select name="student_id" id="student_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('student_id') border-red-500 @enderror">
                    <option value="">Select Student</option>
                    @foreach($students as $student)
                        <option value="{{ $student->student_id }}" {{ (old('student_id', $parcel->student_id) == $student->student_id) ? 'selected' : '' }}>
                            {{ $student->firstname }} {{ $student->middlename }} {{ $student->lastname }} - {{ $student->email }}
                        </option>
                    @endforeach
                </select>
                @error('student_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="parcelcom_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Parcel Company <span class="text-red-500">*</span>
                </label>
                <select name="parcelcom_id" id="parcelcom_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('parcelcom_id') border-red-500 @enderror">
                    <option value="">Select Parcel Company</option>
                    @foreach($parcelCompanies as $company)
                        <option value="{{ $company->parcelcom_id }}" {{ (old('parcelcom_id', $parcel->parcelcom_id) == $company->parcelcom_id) ? 'selected' : '' }}>
                            {{ $company->companyname }}
                            @if($company->deliverytimeestimate)
                                ({{ $company->deliverytimeestimate }})
                            @endif
                        </option>
                    @endforeach
                </select>
                @error('parcelcom_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="trakingnumber" class="block text-sm font-medium text-gray-700 mb-1">
                    Tracking Number <span class="text-red-500">*</span>
                </label>
                <input type="text" name="trakingnumber" id="trakingnumber" value="{{ old('trakingnumber', $parcel->trakingnumber) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('trakingnumber') border-red-500 @enderror"
                       placeholder="Enter tracking number">
                @error('trakingnumber')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                    Notes
                </label>
                <textarea name="notes" id="notes" rows="4"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('notes') border-red-500 @enderror"
                          placeholder="Enter any additional notes">{{ old('notes', $parcel->notes) }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-3 pt-4">
                <a href="{{ route('parcels.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                    Cancel
                </a>
                <button type="submit" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition">
                    Update Parcel
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
