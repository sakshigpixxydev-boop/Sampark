@extends('layouts.guest')

@section('title', __('Thank You') . ' - ' . __('PRAMUKH SANKALP Programme'))

@php
    // The message is shown in all three languages, with the visitor's own
    // language first, so it is understood regardless of the current locale.
    $messages = [
        'en' => [
            'label' => 'English',
            'heading' => 'Thank you — your details have been saved.',
            'body' => 'The current semester of the PRAMUKH SANKALP Programme has already started, so new registrations are closed at the moment. You do not need to fill this form again. As soon as the new batch opens, we will email you at :email with a link to choose your plan and complete the payment.',
        ],
        'hi' => [
            'label' => 'हिंदी',
            'heading' => 'धन्यवाद — आपकी जानकारी सुरक्षित कर ली गई है।',
            'body' => 'प्रमुख संकल्प कार्यक्रम का वर्तमान सत्र आरंभ हो चुका है, इसलिए इस समय नई पंजीकरण प्रक्रिया बंद है। आपको यह फ़ॉर्म दोबारा भरने की आवश्यकता नहीं है। नया बैच शुरू होते ही हम आपको :email पर ईमेल भेजेंगे, जिसमें योजना चुनने और भुगतान पूरा करने की लिंक होगी।',
        ],
        'gu' => [
            'label' => 'ગુજરાતી',
            'heading' => 'આભાર — તમારી વિગતો સાચવી લેવામાં આવી છે.',
            'body' => 'પ્રમુખ સંકલ્પ કાર્યક્રમનું વર્તમાન સત્ર શરૂ થઈ ચૂક્યું છે, તેથી હાલમાં નવી નોંધણી બંધ છે. તમારે આ ફોર્મ ફરીથી ભરવાની જરૂર નથી. નવી બેચ શરૂ થતાં જ અમે તમને :email પર ઈમેલ મોકલીશું, જેમાં પ્લાન પસંદ કરવાની અને ચુકવણી પૂર્ણ કરવાની લિંક હશે.',
        ],
    ];

    $primary = in_array($student->selected_language, ['en', 'hi', 'gu'], true)
        ? $student->selected_language
        : 'en';

    $ordered = array_merge(
        [$primary => $messages[$primary]],
        array_diff_key($messages, [$primary => null])
    );
@endphp

@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full space-y-6 bg-white p-8 md:p-10 rounded-3xl border border-gray-200 shadow-xl">

        <!-- Logo -->
        <div class="flex justify-center">
            <img src="{{ asset('images/sankalp.png') }}" alt="{{ __('PRAMUKH SANKALP Programme') }}" class="h-16 w-auto object-contain">
        </div>

        <!-- Status icon -->
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-50 border border-green-100 text-green-600">
            <svg class="h-9 w-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        <!-- Messages, one block per language -->
        <div class="space-y-4">
            @foreach($ordered as $code => $message)
                @php $isPrimary = $loop->first; @endphp
                <div class="rounded-2xl border p-5 {{ $isPrimary ? 'border-[#00bde0]/30 bg-[#00bde0]/5' : 'border-gray-200 bg-gray-50' }}">
                    <p class="text-[10px] font-black uppercase tracking-widest mb-2 {{ $isPrimary ? 'text-[#0099b8]' : 'text-gray-400' }}">
                        {{ $message['label'] }}
                    </p>
                    <h2 class="font-black text-gray-900 tracking-tight {{ $isPrimary ? 'text-xl' : 'text-base' }}">
                        {{ $message['heading'] }}
                    </h2>
                    <p class="mt-2 text-gray-700 leading-relaxed {{ $isPrimary ? 'text-sm' : 'text-xs' }}">
                        {{ str_replace(':email', $student->email, $message['body']) }}
                    </p>
                </div>
            @endforeach
        </div>

        <!-- Admin-authored note about the next batch -->
        @if($nextBatchNote)
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
                <p class="text-[10px] font-black uppercase tracking-widest text-amber-700 mb-1.5">{{ __('Expected next batch') }}</p>
                <p class="text-sm font-semibold text-amber-900 whitespace-pre-line">{{ $nextBatchNote }}</p>
            </div>
        @endif

        <!-- Reference details -->
        <div class="border-t border-gray-100 pt-5 text-center">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('We will email you at') }}</p>
            <p class="mt-1 text-sm font-black text-gray-900 break-all">{{ $student->email }}</p>
        </div>
    </div>
</div>
@endsection
