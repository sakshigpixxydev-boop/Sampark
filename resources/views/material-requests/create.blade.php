@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="requestForm()">
    <div class="mb-6">
        <a href="{{ route('material-requests.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Requests
        </a>
        <h1 class="text-2xl font-semibold text-gray-900 mt-2">Submit Material Request</h1>
    </div>

    <x-card>
        <form action="{{ route('material-requests.store') }}" method="POST">
            @csrf

            <div class="space-y-6">
                @if(count($schools) > 1)
                <div>
                    <label for="school_id" class="block text-sm font-medium text-gray-700 mb-1">Select School <span class="text-red-500">*</span></label>
                    <select name="school_id" id="school_id" required class="searchable-select w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('school_id') border-red-500 @else border-gray-300 @enderror">
                        <option value="">Choose a school</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->school_id }}" {{ old('school_id') == $school->school_id ? 'selected' : '' }}>{{ $school->schoolname }}</option>
                        @endforeach
                    </select>
                    @error('school_id')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                @elseif(count($schools) === 1)
                <input type="hidden" name="school_id" id="school_id" value="{{ $schools[0]->school_id }}">
                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Requesting For</p>
                    <p class="text-sm font-medium text-gray-900 mt-1">{{ $schools[0]->schoolname }}</p>
                </div>
                @else
                <div class="p-4 bg-red-50 rounded-lg border border-red-100">
                    <p class="text-sm text-red-800">No schools are currently assigned to your account.</p>
                </div>
                @endif

                <div>
                    <label for="material_id" class="block text-sm font-medium text-gray-700 mb-1">Select Material <span class="text-red-500">*</span></label>
                    <select name="material_id" id="material_id" x-model="selectedMaterialId" @change="updateMaterialDetails()" required class="searchable-select w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('material_id') border-red-500 @else border-gray-300 @enderror">
                        <option value="">Choose material</option>
                        @foreach($materials as $material)
                            @php $thumbnail = $material->images->first()?->image_path ? Storage::url($material->images->first()->image_path) : ''; @endphp
                            <option value="{{ $material->id }}" 
                                data-price="{{ $material->active_price }}" 
                                data-original-price="{{ $material->price }}"
                                data-has-discount="{{ $material->is_active_discount ? '1' : '0' }}"
                                data-image="{{ $thumbnail }}"
                                data-type="{{ $material->material_type }}">
                                {{ $material->name }} (₹{{ number_format($material->active_price, 2) }})
                            </option>
                        @endforeach
                    </select>
                    @error('material_id')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div x-show="selectedMaterialId" x-cloak class="grid grid-cols-1 md:grid-cols-12 gap-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="md:col-span-3 lg:col-span-2 flex items-center justify-center bg-white rounded-lg border border-gray-100 overflow-hidden aspect-square">
                        <template x-if="materialImage">
                            <img :src="materialImage" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!materialImage">
                            <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </template>
                    </div>
                    <div class="md:col-span-9 lg:col-span-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Material Type</p>
                            <p class="text-sm font-medium text-gray-900 mt-1" x-text="materialType == 0 ? 'Digital' : 'Printable'"></p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Unit Price</p>
                            <div class="mt-1 flex items-baseline gap-2">
                                <span class="text-sm font-bold text-[#00bde0]" x-text="'₹' + Number(unitPrice).toFixed(2)"></span>
                                <template x-if="hasDiscount == '1'">
                                    <span class="text-[10px] text-gray-400 line-through" x-text="'₹' + Number(originalPrice).toFixed(2)"></span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity <span class="text-red-500">*</span></label>
                        <input type="number" name="quantity" id="quantity" x-model="quantity" min="1" required @input="calculateTotal()" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('quantity') border-red-500 @enderror">
                        @error('quantity')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="delivery_type" class="block text-sm font-medium text-gray-700 mb-1">Delivery Type <span class="text-red-500">*</span></label>
                        <select name="delivery_type" id="delivery_type" x-model="deliveryType" required class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('delivery_type') border-red-500 @enderror">
                            <option value="">Choose delivery</option>
                            <option value="digital">Digital (PDF/Download)</option>
                            <option value="printable">Printable (Courier)</option>
                        </select>
                        @error('delivery_type')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div x-show="selectedMaterialId" x-cloak class="pt-4 flex items-center justify-between border-t border-gray-100">
                    <span class="text-lg font-semibold text-gray-700">Total Amount:</span>
                    <span class="text-2xl font-bold text-[#00bde0]" x-text="'₹' + Number(totalAmount).toFixed(2)"></span>
                </div>

                <div class="pt-6 border-t border-gray-200 flex justify-end gap-3">
                    <a href="{{ route('material-requests.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-medium text-sm">Cancel</a>
                    <button type="submit" class="px-4 py-2 bg-[#00bde0] hover:bg-[#0099b8] text-white rounded-lg transition font-medium text-sm">Submit Request</button>
                </div>
            </div>
        </form>
    </x-card>
</div>

@push('scripts')
<script>
    function requestForm() {
        return {
            selectedMaterialId: '',
            unitPrice: 0,
            originalPrice: 0,
            hasDiscount: '0',
            materialImage: '',
            quantity: 1,
            totalAmount: 0,
            materialType: -1,
            deliveryType: '',
            
            init() {
                this.$watch('deliveryType', value => {
                    const el = document.querySelector('select[name="delivery_type"]');
                    if (el && el.tomselect && el.tomselect.getValue() !== value) {
                        el.tomselect.setValue(value);
                    }
                });
            },

            updateMaterialDetails() {
                const select = document.getElementById('material_id');
                const selectedOption = Array.from(select.options).find(opt => opt.value == this.selectedMaterialId);
                
                if (selectedOption) {
                    this.unitPrice = selectedOption.getAttribute('data-price') || 0;
                    this.originalPrice = selectedOption.getAttribute('data-original-price') || 0;
                    this.hasDiscount = selectedOption.getAttribute('data-has-discount') || '0';
                    this.materialImage = selectedOption.getAttribute('data-image') || '';
                    this.materialType = selectedOption.getAttribute('data-type') || -1;
                    
                    this.deliveryType = '';
                    this.calculateTotal();
                } else {
                    this.unitPrice = 0;
                    this.originalPrice = 0;
                    this.hasDiscount = '0';
                    this.materialImage = '';
                    this.materialType = -1;
                    this.deliveryType = '';
                    this.totalAmount = 0;
                }
            },
            
            calculateTotal() {
                this.totalAmount = this.unitPrice * this.quantity;
            }
        }
    }
</script>
@endpush
@endsection
