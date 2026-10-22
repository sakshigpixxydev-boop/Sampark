@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Edit Taluko</h1>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('locations.talukos.update', $taluko->takula_id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label for="dist_id" class="block text-sm font-medium text-gray-700 mb-1">District <span class="text-red-500">*</span></label>
                    <select name="dist_id" id="dist_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent" required>
                        <option value="">Select District</option>
                        @foreach($districts as $district)
                            <option value="{{ $district->dist_id }}" {{ old('dist_id', $taluko->dist_id) == $district->dist_id ? 'selected' : '' }}>
                                {{ $district->dname }} ({{ $district->state->sname ?? '' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="tname" class="block text-sm font-medium text-gray-700 mb-1">Taluko Name <span class="text-red-500">*</span></label>
                    <input type="text" name="tname" id="tname" value="{{ old('tname', $taluko->tname) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent" required>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('locations.talukos.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition">Cancel</a>
                <button type="submit" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition">Update Taluko</button>
            </div>
        </form>
    </div>
</div>
@endsection
