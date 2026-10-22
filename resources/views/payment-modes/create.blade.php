@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Add Payment Mode</h1>
        <p class="text-sm text-gray-600 mt-1">Create a new payment mode</p>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('payment-modes.store') }}" method="POST">
            @csrf
            
            <div class="space-y-4">
                <div>
                    <label for="paymodename" class="block text-sm font-medium text-gray-700 mb-1">
                        Payment Mode Name <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="paymodename" 
                        id="paymodename" 
                        value="{{ old('paymodename') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('paymodename') border-red-500 @enderror"
                        placeholder="e.g., Cash, Credit Card, Debit Card, Check"
                        required
                    >
                    @error('paymodename')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6">
                <a href="{{ route('payment-modes.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                    Cancel
                </a>
                <button type="submit" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition">
                    Create Payment Mode
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
