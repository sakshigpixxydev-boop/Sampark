<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset PIN - Pramukh Sankalp</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Logo and Title -->
            <div>
                <h2 class="mt-6 text-center text-3xl font-bold text-gray-900">
                    Reset PIN
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Enter your new 4-digit PIN
                </p>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                <form method="POST" action="{{ route('pin.update') }}">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">

                    <!-- Email -->
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="{{ $email ?? old('email') }}"
                            required
                            autofocus
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('email') border-red-500 @enderror"
                        >
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- PIN -->
                    <div class="mb-4">
                        <label for="pin" class="block text-sm font-medium text-gray-700 mb-1">
                            New PIN <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="password" 
                            id="pin" 
                            name="pin" 
                            maxlength="4"
                            pattern="[0-9]{4}"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('pin') border-red-500 @enderror"
                        >
                        @error('pin')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Must be exactly 4 digits</p>
                    </div>

                    <!-- Confirm PIN -->
                    <div class="mb-6">
                        <label for="pin_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                            Confirm PIN <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="password" 
                            id="pin_confirmation" 
                            name="pin_confirmation" 
                            maxlength="4"
                            pattern="[0-9]{4}"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent"
                        >
                    </div>

                    <!-- Submit Button -->
                    <div class="mb-4">
                        <button 
                            type="submit" 
                            class="w-full bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition"
                        >
                            Reset PIN
                        </button>
                    </div>

                    <!-- Back to Login -->
                    <div class="text-center">
                        <a href="{{ route('login') }}" class="text-sm text-[#00bde0] hover:text-[#0099b8]">
                            Back to Login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
