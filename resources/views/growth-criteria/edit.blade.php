@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Edit Growth Criterion</h1>
        <p class="text-sm text-gray-600 mt-1">Modify existing core criteria for growth calculation</p>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <form action="{{ route('growth-criteria.update', $criterion->criterion_id) }}" method="POST" class="p-8">
            @csrf
            @method('PUT')
            <div class="space-y-6">
                <div>
                    <label for="criterion_name" class="block text-sm font-medium text-gray-700 mb-2">Criterion Name <span class="text-red-500">*</span></label>
                    <input type="text" name="criterion_name" id="criterion_name" value="{{ old('criterion_name', $criterion->criterion_name) }}" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                           placeholder="e.g. Discipline, Leadership">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <label for="min_score" class="block text-sm font-medium text-gray-700 mb-2">Min Marks <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" name="min_score" id="min_score" value="{{ old('min_score', $criterion->min_score ?? 0) }}" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                    </div>

                    <div>
                        <label for="max_score" class="block text-sm font-medium text-gray-700 mb-2">Max Marks <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" name="max_score" id="max_score" value="{{ old('max_score', $criterion->max_score ?? 10) }}" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <label for="display_order" class="block text-sm font-medium text-gray-700 mb-2">Display Order <span class="text-red-500">*</span></label>
                        <input type="number" name="display_order" id="display_order" value="{{ old('display_order', $criterion->display_order) }}" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
                        <select id="status" name="status" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                            <option value="1" {{ old('status', $criterion->status) == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status', $criterion->status) == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-100 flex justify-end space-x-4">
                    <a href="{{ route('growth-criteria.index') }}" class="px-6 py-3 border border-gray-300 rounded-xl text-gray-600 font-medium hover:bg-gray-50 transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-10 py-3 bg-primary-500 hover:bg-primary-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all">
                        Update Criterion
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
