@extends('layouts.guest')

@section('title', __('Upload Document & Verify Email'))

@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto w-full">
        <!-- Header -->
        <div class="text-center mb-12">
            <img src="{{ asset('images/sankalp.png') }}" alt="Logo" class="h-16 mx-auto mb-6">
            <h1 class="text-4xl font-black text-gray-900 uppercase tracking-tight mb-4">{{ __('Identity Verification') }}</h1>
            <p class="text-lg text-gray-600 font-bold">{{ __('Please upload a clear photograph of yourself.') }}</p>
        </div>

        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100">
            <div class="p-8 md:p-12">
                @if(session('success'))
                    <div class="mb-8 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-r-xl font-bold">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-8 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-xl font-bold">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" 
                    x-data="{ 
                        photoPreview: null
                    }">
                    @csrf
                    <input type="hidden" name="student_id" value="{{ $encodedId }}">

                    <!-- Photo Upload Section -->
                    <div class="mb-10">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-4 text-center">{{ __('Student Photo') }} <span class="text-red-500">*</span></label>
                        
                        <div class="flex flex-col items-center justify-center">
                            <!-- Image Preview -->
                            <div class="relative mb-6">
                                <div class="w-48 h-48 rounded-3xl bg-gray-100 border-4 border-dashed border-gray-200 overflow-hidden flex items-center justify-center relative group">
                                    <template x-if="!photoPreview">
                                        <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </template>
                                    <template x-if="photoPreview">
                                        <img :src="photoPreview" class="w-full h-full object-cover">
                                    </template>
                                </div>
                                <label for="photo" class="absolute -bottom-4 left-1/2 -translate-x-1/2 w-12 h-12 bg-[#00bde0] text-white rounded-2xl flex items-center justify-center shadow-xl cursor-pointer hover:bg-[#0099b8] transition-all hover:scale-110 active:scale-95">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    <input type="file" id="photo" name="photo" class="hidden" accept="image/*" 
                                        @change="const file = $el.files[0]; if (file) { const reader = new FileReader(); reader.onload = (e) => { photoPreview = e.target.result; }; reader.readAsDataURL(file); }">
                                </label>
                            </div>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">{{ __('JPG, PNG allowed (Max 2MB)') }}</p>
                        </div>
                    </div>



                    <div class="bg-blue-50 rounded-2xl p-6 mb-10">
                        <h4 class="text-xs font-black text-blue-900 uppercase tracking-widest mb-2">{{ __('Requirements') }}</h4>
                        <ul class="text-[11px] font-bold text-blue-800 space-y-1">
                            <li class="flex items-start gap-2">
                                <svg class="w-3 h-3 mt-0.5 text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                {{ __('Only Photo in Document.. no other Docs..') }}
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-3 h-3 mt-0.5 text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                {{ __('Ensure the photo is clear and your face is visible.') }}
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-3 h-3 mt-0.5 text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                {{ __('They can Update it later from their Portal.') }}
                            </li>
                        </ul>
                    </div>

                    <div class="flex justify-center">
                        <button type="submit" class="group px-12 py-4 bg-green-600 text-white rounded-2xl font-black uppercase tracking-widest hover:bg-green-700 hover:shadow-2xl hover:shadow-green-600/30 transition-all flex items-center gap-3">
                            {{ __('Complete Registration') }}
                            <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <p class="mt-8 text-center text-gray-500 text-[10px] font-bold uppercase tracking-widest">
            {{ __('PRAMUKH Academy © 2026') }}
        </p>
    </div>
</div>
@endsection
