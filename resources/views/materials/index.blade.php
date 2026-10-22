@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Materials</h1>
            <p class="text-sm text-gray-600 mt-1">Manage educational materials and pricing</p>
        </div>
        @canAccess(10602)
        <a href="{{ route('materials.create') }}" class="bg-primary-500 hover:bg-primary-600 text-white font-medium px-4 py-2 rounded-lg transition text-sm">
            Add New Material
        </a>
        @endcanAccess
    </div>

    <!-- Filters -->
    <form action="{{ route('materials.index') }}" method="GET" class="mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search by name..." 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select name="material_type" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <option value="">All Types</option>
                    <option value="0" {{ request('material_type') === '0' ? 'selected' : '' }}>Digital</option>
                    <option value="1" {{ request('material_type') === '1' ? 'selected' : '' }}>Printable</option>
                </select>
            </div>
            <div class="lg:col-span-2 flex items-end">
                <button type="submit" class="bg-primary-500 hover:bg-primary-600 text-white font-medium px-4 py-2 rounded-lg transition text-sm">Filter</button>
                <a href="{{ route('materials.index') }}" class="ml-2 text-gray-500 hover:text-gray-700 text-sm py-2">Reset</a>
            </div>
        </div>
    </form>

    <!-- Materials Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Material Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price (₹)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($materials as $material)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 shrink-0 mr-3 overflow-hidden rounded-md border border-gray-100 bg-gray-50">
                                        @if($material->images->count() > 0)
                                            <img class="h-full w-full object-cover" src="{{ asset('storage/' . $material->images->first()->image_path) }}" alt="{{ $material->name }}">
                                        @else
                                            <div class="flex items-center justify-center h-full text-gray-400">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $material->name }}</div>
                                        @if($material->description)
                                            <div class="text-xs text-gray-500 truncate max-w-xs">{{ $material->description }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($material->is_active_discount)
                                    <span class="text-sm font-semibold text-gray-900">₹{{ number_format($material->discounted_price, 2) }}</span>
                                    <span class="text-xs text-gray-400 line-through ml-1">₹{{ number_format($material->price, 2) }}</span>
                                @else
                                    <span class="text-sm text-gray-900">₹{{ number_format($material->price, 2) }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $material->material_type == 0 ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $material->type_name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($material->inactive)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-3">
                                    @canAccess(10602)
                                    <a href="{{ route('materials.edit', $material->id) }}" class="text-primary-500 hover:text-primary-600">Edit</a>
                                    @endcanAccess
                                    
                                    @canAccess(10603)
                                    <form action="{{ route('materials.destroy', $material->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this material?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                    </form>
                                    @endcanAccess
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                No materials found. 
                                @canAccess(10602)
                                <a href="{{ route('materials.create') }}" class="text-primary-500 hover:text-primary-600">Create one now</a>
                                @endcanAccess
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($materials->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $materials->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
