@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <a href="{{ route('materials.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Materials
        </a>
        <h1 class="text-2xl font-semibold text-gray-900 mt-2">Edit Material: {{ $material->name }}</h1>
    </div>

    <x-card>
        <form action="{{ route('materials.update', $material->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <x-input label="Material Name" name="name" :value="old('name', $material->name)" required :error="$errors->first('name')" />

                <x-textarea label="Description" name="description" :error="$errors->first('description')">{{ old('description', $material->description) }}</x-textarea>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6" x-data="{ price: {{ old('price', $material->price) }}, discounted: {{ old('discounted_price', $material->discounted_price ?? 0) }} }">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Original Price (₹) <span class="text-red-500">*</span></label>
                        <input type="number" name="price" step="0.01" required x-model="price" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" placeholder="0.00">
                        @error('price')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Discounted Price (₹)</label>
                        <input type="number" name="discounted_price" step="0.01" x-model="discounted" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" placeholder="0.00">
                        <p x-show="discounted && parseFloat(discounted) >= parseFloat(price)" class="text-xs text-red-600 mt-1">Discounted price must be less than original price.</p>
                        @error('discounted_price')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-input type="date" label="Discount Start Date" name="discount_start_date" :value="old('discount_start_date', $material->discount_start_date ? $material->discount_start_date->format('d/m/Y') : '')" :error="$errors->first('discount_start_date')" />
                    
                    <x-input type="date" label="Discount End Date" name="discount_end_date" :value="old('discount_end_date', $material->discount_end_date ? $material->discount_end_date->format('d/m/Y') : '')" :error="$errors->first('discount_end_date')" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Material Type <span class="text-red-500">*</span></label>
                    <select name="material_type" required class="searchable-select w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('material_type') border-red-500 @else border-gray-300 @enderror">
                        <option value="0" {{ old('material_type', $material->material_type) == 0 ? 'selected' : '' }}>Digital</option>
                        <option value="1" {{ old('material_type', $material->material_type) == 1 ? 'selected' : '' }}>Printable (Courier)</option>
                    </select>
                    @error('material_type')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                @php
                    $filters = $material->filters ?? [];
                    $selectedSchoolIds = old('school_ids', $filters['school_ids'] ?? []);
                @endphp

                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">School Visibility Filter</label>
                        <select name="school_ids[]" id="school_ids" multiple class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            @foreach($schools as $school)
                                <option value="{{ $school->school_id }}" {{ in_array($school->school_id, (array)$selectedSchoolIds) ? 'selected' : '' }}>
                                    {{ $school->schoolname }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Leave empty to display to all schools. You can select multiple schools.</p>
                    </div>
                </div>

                <div x-data="{ 
                    previews: [],
                    files: [],
                    sizeError: false,
                    handleFiles(event) {
                        const newFiles = Array.from(event.target.files);
                        this.files = [...this.files, ...newFiles];
                        this.renderPreviews();
                        this.updateInput();
                    },
                    removeNewFile(index) {
                        this.files.splice(index, 1);
                        this.renderPreviews();
                        this.updateInput();
                    },
                    renderPreviews() {
                        this.previews = [];
                        this.sizeError = false;
                        this.files.forEach(file => {
                            if (file.size > 2048 * 1024) {
                                this.sizeError = true;
                            }
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                this.previews.push(e.target.result);
                            };
                            reader.readAsDataURL(file);
                        });
                    },
                    updateInput() {
                        const dt = new DataTransfer();
                        this.files.forEach(file => dt.items.add(file));
                        $refs.imageInput.files = dt.files;
                    },
                    deleteImage(id, event) {
                        if (!confirm('Are you sure you want to delete this image?')) return;
                        
                        fetch(`/material-images/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                event.target.closest('.image-container').remove();
                            } else {
                                alert(data.message || 'Deletion failed');
                            }
                        });
                    }
                }">
                    @if($material->images->count() > 0)
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Current Images</label>
                            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-4">
                                @foreach($material->images as $image)
                                    <div class="relative group aspect-square rounded-lg overflow-hidden border border-gray-200 image-container">
                                        <img src="{{ Storage::url($image->image_path) }}" alt="Material Image" class="w-full h-full object-cover">
                                        <button type="button" @click="deleteImage({{ $image->id }}, $event)" class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Add More Images</label>
                        <input type="file" x-ref="imageInput" name="images[]" multiple accept="image/*" @change="handleFiles" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-500 file:text-white hover:file:bg-primary-600 cursor-pointer" />
                        
                        <div x-show="sizeError" class="mt-2 text-xs text-red-600 font-medium">
                            One or more images exceed the 2MB size limit. Please upload smaller images.
                        </div>

                        <div x-show="previews.length" class="mt-4 flex flex-wrap gap-4">
                            <template x-for="(src, index) in previews" :key="index">
                                <div class="relative w-24 h-24 rounded-lg overflow-hidden border border-gray-200 group">
                                    <img :src="src" class="w-full h-full object-cover">
                                    <button type="button" @click="removeNewFile(index)" class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>
                            </template>
                        </div>

                        @error('images.*')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="inactive" id="inactive" value="1" class="h-4 w-4 text-primary-500 focus:ring-primary-500 border-gray-300 rounded" {{ old('inactive', $material->inactive) ? 'checked' : '' }}>
                    <label for="inactive" class="ml-2 block text-sm text-gray-700">Mark as Inactive</label>
                </div>

                <div class="pt-4 border-t border-gray-200 flex justify-end gap-3">
                    <a href="{{ route('materials.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-medium text-sm">Cancel</a>
                    <button type="submit" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-lg transition font-medium text-sm">Update Material</button>
                </div>
            </div>
        </form>
    </x-card>
</div>
@endsection
