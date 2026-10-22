@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-end gap-4">
        <div>
            <a href="{{ route('material-requests.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Requests
            </a>
            <h1 class="text-2xl font-semibold text-gray-900 mt-2">Request Details: #REQ-{{ str_pad($materialRequest->id, 5, '0', STR_PAD_LEFT) }}</h1>
        </div>
        <div>
            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $materialRequest->status_color }} capitalize shadow-sm">
                {{ $materialRequest->status }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Side: Basic Info -->
        <div class="lg:col-span-2 space-y-6">
            <x-card :padding="false" class="overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Material Information</h3>
                    <span class="text-[10px] font-bold text-gray-400 bg-white px-2 py-1 rounded border border-gray-200">
                        TYPE: {{ $materialRequest->material->material_type == 0 ? 'DIGITAL' : 'PRINTABLE' }}
                    </span>
                </div>
                <div class="p-6">
                    <div class="flex flex-col md:flex-row gap-8">
                        @if($materialRequest->material->images->count() > 0)
                        <div class="w-full md:w-1/3 space-y-4">
                            <div class="aspect-square rounded-xl border border-gray-200 overflow-hidden bg-gray-100">
                                <img src="{{ Storage::url($materialRequest->material->images->first()->image_path) }}" class="w-full h-full object-cover" id="main-image">
                            </div>
                            @if($materialRequest->material->images->count() > 1)
                            <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-none">
                                @foreach($materialRequest->material->images as $image)
                                <button type="button" @click="document.getElementById('main-image').src = '{{ Storage::url($image->image_path) }}'" class="shrink-0 w-12 h-12 rounded-lg border border-gray-200 overflow-hidden hover:border-[#00bde0] transition">
                                    <img src="{{ Storage::url($image->image_path) }}" class="w-full h-full object-cover">
                                </button>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        @else
                        <div class="w-full md:w-1/3 aspect-square rounded-xl border border-dashed border-gray-300 flex items-center justify-center bg-gray-50 text-gray-400">
                            No Images
                        </div>
                        @endif

                        <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8">
                            <div class="sm:col-span-2">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Material Name</p>
                                <p class="text-base font-bold text-gray-900 mt-1">{{ $materialRequest->material->name }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Unit Price</p>
                                <div class="mt-1 flex items-baseline gap-2">
                                    <span class="text-lg font-bold text-[#00bde0]">₹{{ number_format($materialRequest->material->active_price, 2) }}</span>
                                    @if($materialRequest->material->is_active_discount)
                                        <span class="text-xs text-gray-400 line-through">₹{{ number_format($materialRequest->material->price, 2) }}</span>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Request Quantity</p>
                                <p class="text-sm font-medium text-gray-900 mt-1">{{ $materialRequest->quantity }} Units</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Delivery Channel</p>
                                <p class="text-sm font-medium text-gray-900 mt-1 capitalize">{{ $materialRequest->delivery_type }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Amount Paid</p>
                                <p class="text-xl font-black text-[#00bde0] mt-1">₹{{ number_format($materialRequest->total_amount, 2) }}</p>
                            </div>
                        </div>
                    </div>
                    
                    @if($materialRequest->material->description)
                    <div class="mt-8 pt-6 border-t border-gray-100">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Material Description</p>
                        <div class="text-sm text-gray-600 leading-relaxed font-light">
                            {!! nl2br(e($materialRequest->material->description)) !!}
                        </div>
                    </div>
                    @endif
                </div>
            </x-card>

            <x-card :padding="false" class="overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">School Information</h3>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">School Name</p>
                        <p class="text-sm font-medium text-gray-900 mt-1">{{ $materialRequest->school->schoolname }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Admin Name</p>
                        <p class="text-sm font-medium text-gray-900 mt-1">{{ $materialRequest->school->adminname }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Address</p>
                        <p class="text-sm font-medium text-gray-900 mt-1">
                            {{ $materialRequest->school->address1 }} {{ $materialRequest->school->address2 }}<br>
                            {{ $materialRequest->school->city_village }}, {{ $materialRequest->school->taluka->tname ?? '' }}, {{ $materialRequest->school->taluka->district->dname ?? '' }} - {{ $materialRequest->school->pincode }}
                        </p>
                    </div>
                </div>
            </x-card>

            @if($materialRequest->admin_remarks)
            <div class="bg-blue-50 border border-blue-100 rounded-xl p-6">
                <h3 class="text-[10px] font-bold text-blue-800 uppercase tracking-widest mb-2">Admin Remarks</h3>
                <p class="text-sm text-blue-900">{{ $materialRequest->admin_remarks }}</p>
            </div>
            @endif

            @if($materialRequest->delivery_type == 'digital' && in_array($materialRequest->status, ['approved', 'completed']))
            <div class="bg-green-50 border border-green-100 rounded-xl p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-sm font-semibold text-green-800">Your material is ready!</h3>
                    <p class="text-xs text-green-700 mt-1">You can now download the digital content.</p>
                </div>
                <a href="#" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg shadow-sm flex items-center justify-center transition text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Download Material
                </a>
            </div>
            @endif
        </div>

        <!-- Right Side: Status Updates (Admin Only) -->
        <div class="space-y-6">
            @canAccess(10613)
            <x-card :padding="false" class="overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Manage Status</h3>
                </div>
                <div class="p-6">
                    <form action="{{ route('material-requests.update-status', $materialRequest->id) }}" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Update Status</label>
                                <select name="status" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                                    <option value="pending" {{ $materialRequest->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ $materialRequest->status == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="dispatched" {{ $materialRequest->status == 'dispatched' ? 'selected' : '' }}>Dispatched</option>
                                    <option value="completed" {{ $materialRequest->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                            </div>

                            <x-textarea label="Admin Remarks" name="admin_remarks">{{ $materialRequest->admin_remarks }}</x-textarea>

                            <button type="submit" class="w-full bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium py-2 rounded-lg transition text-sm">
                                Update Request
                            </button>
                        </div>
                    </form>
                </div>
            </x-card>
            @endcanAccess

            <x-card :padding="false" class="overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Request Metadata</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Requested On</p>
                        <p class="text-sm font-medium text-gray-700 mt-1">{{ $materialRequest->created_at->format('d M Y, h:i A') }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Last Modified</p>
                        <p class="text-sm font-medium text-gray-700 mt-1">{{ $materialRequest->updated_at->format('d M Y, h:i A') }}</p>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection
