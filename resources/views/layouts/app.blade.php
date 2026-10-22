<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Pramukh Sankalp')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="bg-gray-50 h-full overflow-hidden print:h-auto print:overflow-visible" x-data="{ sidebarOpen: false }">
    <div class="flex h-full overflow-hidden print:h-auto print:overflow-visible print:block">
        @auth
        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform transition-transform duration-200 lg:relative lg:translate-x-0 lg:flex lg:flex-col"
            :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen }">
            <!-- Sidebar Header -->
            <div class="flex items-center justify-between h-16 px-6 border-b border-gray-200">
                <h1 class="text-xl font-bold text-primary-500">Pramukh Sankalp</h1>
                <button @click="sidebarOpen = false" class="lg:hidden text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-1">
                <!-- Dashboard -->
                @canAccess(10051)
                <a href="{{ route('dashboard') }}"
                    class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ (request()->routeIs('dashboard') || request()->routeIs('admin.dashboard') || request()->routeIs('student.dashboard') || request()->routeIs('initiator.dashboard') || request()->routeIs('school.dashboard') || request()->routeIs('accountant.dashboard')) ? 'bg-primary-500 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                        </path>
                    </svg>
                    Dashboard
                </a>
                @endcanAccess

                @canAccess(11101)
                <a href="{{ route('notifications.index') }}"
                    class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('notifications.index') ? 'bg-primary-500 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <span class="flex-1">Notifications</span>
                    @if(auth()->user()->unreadTotalCount() > 0)
                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-white text-primary-600 shadow-sm">
                        {{ auth()->user()->unreadTotalCount() }}
                    </span>
                    @endif
                </a>
                @endcanAccess

                @if(Auth::user()->isPaidInitiator())
                <a href="{{ route('initiator.earnings') }}"
                    class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('initiator.earnings') ? 'bg-[#00bde0] text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    My Earnings
                </a>
                @endif

                <!-- User Management -->
                @if(auth()->user()->canAccessModule(10021) || auth()->user()->canAccessModule(10001) || auth()->user()->canAccessModule(10011))
                <div x-data="{ open: {{ request()->routeIs('users.*') || request()->routeIs('roles.*') || request()->routeIs('permissions.*') ? 'true' : 'false' }} }" class="space-y-1">
                    <button @click="open = !open"
                         class="flex items-center w-full px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('users.*') || request()->routeIs('roles.*') || request()->routeIs('permissions.*') ? 'bg-primary-500 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg>
                        <span class="flex-1 text-left">User Management</span>
                        <svg class="w-4 h-4 ml-2 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak class="mt-1 space-y-1">
                        @canAccess(10021)
                        <a href="{{ route('users.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('users.*') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Users
                        </a>
                        @endcanAccess
                        @canAccess(10001)
                        <a href="{{ route('roles.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('roles.*') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            Roles
                        </a>
                        @endcanAccess
                        @canAccess(10011)
                        <a href="{{ route('permissions.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('permissions.*') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            Permissions
                        </a>
                        @endcanAccess
                    </div>
                </div>
                @endif

                <!-- Academic Management -->
                @if(auth()->user()->canAccessModule(10031) || auth()->user()->canAccessModule(10041) || auth()->user()->canAccessModule(10222) || auth()->user()->canAccessModule(10223) || auth()->user()->canAccessModule(10501) || auth()->user()->canAccessModule(10101) || auth()->user()->canAccessModule(10401))
                <div x-data="{ open: {{ request()->routeIs('schools.*') || request()->routeIs('students.*') || request()->routeIs('student-growth.*') || request()->routeIs('guardians.*') || request()->routeIs('events.*') || request()->routeIs('students.announcements.*') ? 'true' : 'false' }} }" class="space-y-1">
                    <button @click="open = !open"
                         class="flex items-center w-full px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('schools.*') || request()->routeIs('students.*') || request()->routeIs('student-growth.*') || request()->routeIs('guardians.*') || request()->routeIs('events.*') || request()->routeIs('students.announcements.*') ? 'bg-primary-500 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 14l9-5-9-5-9 5 9 5z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path>
                        </svg>
                        <span class="flex-1 text-left">Academics</span>
                        <svg class="w-4 h-4 ml-2 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak class="mt-1 space-y-1">
                        @canAccess(10031)
                        <a href="{{ route('schools.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('schools.*') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            Schools
                        </a>
                        @endcanAccess
                        @canAccess(10041)
                        <a href="{{ route('students.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ (request()->routeIs('students.*') && !request()->routeIs('students.announcements.*')) ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path>
                            </svg>
                            Students
                        </a>
                        @endcanAccess
                        @canAccess(10222)
                        <a href="{{ route('students.announcements.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('students.announcements.index') || request()->routeIs('students.announcements.show') || request()->routeIs('students.announcements.create') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
                            </svg>
                            <span class="flex-1">Announcements</span>
                            @if(auth()->user()->unreadAnnouncementCount() > 0)
                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                {{ auth()->user()->unreadAnnouncementCount() }}
                            </span>
                            @endif
                        </a>
                        @endcanAccess
                        @canAccess(10223)
                        <a href="{{ route('students.announcements.templates.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('students.announcements.templates.*') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                            </svg>
                            Announcement Templates
                        </a>
                        @endcanAccess
                        @canAccess(10501)
                        <a href="{{ route('student-growth.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('student-growth.*') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Growth Card
                        </a>
                        @endcanAccess
                        @canAccess(10101)
                        <a href="{{ route('guardians.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('guardians.*') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Guardians
                        </a>
                        @endcanAccess
                        @canAccess(10401)
                        <a href="{{ route('events.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('events.*') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span class="flex-1">Events</span>
                            @if(auth()->user()->unreadEventCount() > 0)
                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                {{ auth()->user()->unreadEventCount() }}
                            </span>
                            @endif
                        </a>
                        @endcanAccess
                    </div>
                </div>
                @endif

                <!-- Admissions & Leads -->
                @if(auth()->user()->canAccessModule(10001) || auth()->user()->canAccessModule(11201) || auth()->user()->canAccessModule(10081) || auth()->user()->hasRole('School') || (auth()->user()->hasRole('Educator') && auth()->user()->hasAnyPermission([11601, 11602, 11603, 11604, 11605, 11606])))
                <div x-data="{ open: {{ request()->routeIs('admin.registrations.*') || request()->routeIs('batch-verification.*') || request()->routeIs('school.registrations.*') || request()->routeIs('school.registration-settings.*') || request()->routeIs('school.educators.*') ? 'true' : 'false' }} }" class="space-y-1">
                    <button @click="open = !open"
                          class="flex items-center w-full px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.registrations.*') || request()->routeIs('batch-verification.*') || request()->routeIs('school.registrations.*') || request()->routeIs('school.registration-settings.*') || request()->routeIs('school.educators.*') ? 'bg-primary-500 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                            </path>
                        </svg>
                        <span class="flex-1 text-left">Admissions & Leads</span>
                        <svg class="w-4 h-4 ml-2 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak class="mt-1 space-y-1">
                        @if(auth()->user()->hasAnyPermission([10001, 11201, 11601, 11602, 11603, 11604, 11605, 11606]))
                        <a href="{{ route('school.registrations.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('school.registrations.index') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                            </svg>
                            School Registrations
                        </a>
                        @endif
                        @if(auth()->user()->hasRole('School'))
                        <a href="{{ route('school.registration-settings.edit') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('school.registration-settings.*') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            </svg>
                            Registration Settings
                        </a>
                        <a href="{{ route('school.educators.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('school.educators.*') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            Educators
                        </a>
                        @endif
                        @if(auth()->user()->canAccessModule(11201) || (auth()->user()->hasRole('Educator') && auth()->user()->hasAnyPermission([11601, 11602, 11603, 11604, 11605, 11606])))
                        <a href="{{ route('admin.registrations.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.registrations.index') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Registration List
                        </a>
                        @endif
                        @canAccess(11401)
                        <a href="{{ route('admin.registrations.history') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.registrations.history') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Migration History
                        </a>
                        @endcanAccess
                        @canAccess(10081)
                        <a href="{{ route('batch-verification.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('batch-verification.*') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Batch Verification
                        </a>
                        @endcanAccess
                    </div>
                </div>
                @endif

                <!-- Leads -->
                @if(auth()->user()->canAccessModule(10001) || auth()->user()->canAccessModule(10201) || auth()->user()->canAccessModule(11701) || auth()->user()->canAccessModule(11703) || auth()->user()->canAccessModule(11704) || auth()->user()->canAccessModule(11706))
                <div x-data="{ open: {{ request()->routeIs('leads.*') || request()->routeIs('student-leads.*') || request()->routeIs('enquiries.*') ? 'true' : 'false' }} }" class="space-y-1">
                    <button @click="open = !open"
                          class="flex items-center w-full px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('leads.*') || request()->routeIs('student-leads.*') || request()->routeIs('enquiries.*') ? 'bg-primary-500 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z">
                            </path>
                        </svg>
                        <span class="flex-1 text-left">Leads</span>
                        <svg class="w-4 h-4 ml-2 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak class="mt-1 space-y-1">
                        @if(auth()->user()->canAccessModule(10001) || auth()->user()->canAccessModule(10201))
                        <a href="{{ route('leads.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('leads.*') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            School Leads
                        </a>
                        @endif
                        @if(auth()->user()->canAccessModule(10001) || auth()->user()->canAccessModule(11701) || auth()->user()->canAccessModule(11703))
                        <a href="{{ route('student-leads.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('student-leads.*') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            Student Leads
                        </a>
                        @endif
                        @if(auth()->user()->canAccessModule(10001) || auth()->user()->canAccessModule(11704) || auth()->user()->canAccessModule(11706))
                        <a href="{{ route('enquiries.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('enquiries.*') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Enquiry
                        </a>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Finance -->
                @if(auth()->user()->canAccessModule(10071) || auth()->user()->canAccessModule(10061))
                <div x-data="{ open: {{ request()->routeIs('payments.*') || request()->routeIs('invoices.*') ? 'true' : 'false' }} }" class="space-y-1">
                    <button @click="open = !open"
                         class="flex items-center w-full px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('payments.*') || request()->routeIs('invoices.*') ? 'bg-primary-500 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                        <span class="flex-1 text-left">Finance</span>
                        <svg class="w-4 h-4 ml-2 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak class="mt-1 space-y-1">
                        @canAccess(10071)
                        <a href="{{ route('payments.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('payments.*') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                            Payments
                        </a>
                        @endcanAccess
                        @canAccess(10061)
                        <a href="{{ route('invoices.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('invoices.*') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 0h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"></path>
                            </svg>
                            Invoices
                        </a>
                        @endcanAccess
                    </div>
                </div>
                @endif

                <!-- Resources -->
                @if(auth()->user()->canAccessModule(10601) || auth()->user()->canAccessModule(10611) || auth()->user()->canAccessModule(10111) || auth()->user()->canAccessModule(10901))
                <div x-data="{ open: {{ request()->routeIs('materials.*') || request()->routeIs('material-requests.*') || request()->routeIs('parcels.*') || request()->routeIs('certificates.*') ? 'true' : 'false' }} }" class="space-y-1">
                    <button @click="open = !open"
                         class="flex items-center w-full px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('materials.*') || request()->routeIs('material-requests.*') || request()->routeIs('parcels.*') || request()->routeIs('certificates.*') ? 'bg-primary-500 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4">
                            </path>
                        </svg>
                        <span class="flex-1 text-left">Resources</span>
                        <svg class="w-4 h-4 ml-2 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak class="mt-1 space-y-1">
                        @canAccess(10601)
                        <a href="{{ route('materials.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('materials.*') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                            </svg>
                            Materials
                        </a>
                        @endcanAccess
                        @canAccess(10611)
                        <a href="{{ route('material-requests.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('material-requests.*') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                            Material Requests
                        </a>
                        @endcanAccess
                        @canAccess(10111)
                        <a href="{{ route('parcels.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('parcels.*') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            Parcels
                        </a>
                        @endcanAccess
                        @canAccess(10901)
                        <a href="{{ route('certificates.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('certificates.*') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                            </svg>
                            Certificates
                        </a>
                        @endcanAccess
                    </div>
                </div>
                @endif

                <!-- Communication -->
                @if(auth()->user()->canAccessModule(10701) || auth()->user()->canAccessModule(10801))
                <div x-data="{ open: {{ request()->routeIs('blogs.*') || request()->routeIs('tickets.*') ? 'true' : 'false' }} }" class="space-y-1">
                    <button @click="open = !open"
                         class="flex items-center w-full px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('blogs.*') || request()->routeIs('tickets.*') ? 'bg-primary-500 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                            </path>
                        </svg>
                        <span class="flex-1 text-left">Communication</span>
                        <svg class="w-4 h-4 ml-2 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak class="mt-1 space-y-1">
                        @canAccess(10701)
                        <a href="{{ route('blogs.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('blogs.*') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                            </svg>
                            <span class="flex-1">Blogs</span>
                            @if(auth()->user()->unreadBlogCount() > 0)
                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                {{ auth()->user()->unreadBlogCount() }}
                            </span>
                            @endif
                        </a>
                        @endcanAccess
                        @canAccess(10801)
                        <a href="{{ route('tickets.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('tickets.*') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            <span class="flex-1">Ticketing</span>
                            @if(auth()->user()->unreadTicketCount() > 0)
                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                {{ auth()->user()->unreadTicketCount() }}
                            </span>
                            @endif
                        </a>
                        @endcanAccess
                    </div>
                </div>
                @endif

                <!-- CMS -->
                @if(auth()->user()->canAccessModule(11001) || auth()->user()->canAccessModule(11301))
                <div x-data="{ open: {{ request()->routeIs('agreement-templates.*') || request()->routeIs('mail-templates.*') ? 'true' : 'false' }} }" class="space-y-1">
                    <button @click="open = !open"
                         class="flex items-center w-full px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('agreement-templates.*') || request()->routeIs('mail-templates.*') ? 'bg-primary-500 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <span class="flex-1 text-left">CMS</span>
                        <svg class="w-4 h-4 ml-2 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak class="mt-1 space-y-1">
                        @canAccess(11001)
                        <a href="{{ route('agreement-templates.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('agreement-templates.*') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Agreement Templates
                        </a>
                        @endcanAccess
                        @canAccess(11301)
                        <a href="{{ route('mail-templates.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('mail-templates.*') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            Mail Templates
                        </a>
                        @endcanAccess
                    </div>
                </div>
                @endif

                <!-- Settings -->
                @if(auth()->user()->hasRole('Admin') || auth()->user()->canAccessModule(10121) || auth()->user()->canAccessModule(10131) || auth()->user()->canAccessModule(10141) || auth()->user()->canAccessModule(10151) || auth()->user()->canAccessModule(10161) || auth()->user()->canAccessModule(10301) || auth()->user()->canAccessModule(10171) || auth()->user()->canAccessModule(11501) || auth()->user()->canAccessModule(11707) || auth()->user()->canAccessModule(10001))
                <div x-data="{ open: {{ request()->routeIs('school-types.*') || request()->routeIs('educational-boards.*') || request()->routeIs('student-types.*') || request()->routeIs('semester-batches.*') || request()->routeIs('fee-plans.*') || request()->routeIs('school-fee-plans.*') || request()->routeIs('initiator-fee-plans.*') || request()->routeIs('discount-rules.*') || request()->routeIs('laravel-logs.*') || request()->routeIs('audit-logs.*') || request()->routeIs('settings.registration.*') ? 'true' : 'false' }} }" class="space-y-1">
                    <button @click="open = !open"
                         class="flex items-center w-full px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('school-types.*') || request()->routeIs('educational-boards.*') || request()->routeIs('student-types.*') || request()->routeIs('semester-batches.*') || request()->routeIs('fee-plans.*') || request()->routeIs('school-fee-plans.*') || request()->routeIs('initiator-fee-plans.*') || request()->routeIs('discount-rules.*') || request()->routeIs('laravel-logs.*') || request()->routeIs('audit-logs.*') || request()->routeIs('settings.registration.*') ? 'bg-primary-500 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z">
                            </path>
                        </svg>
                        <span class="flex-1 text-left">Settings</span>
                        <svg class="w-4 h-4 ml-2 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak class="mt-1 space-y-1">
                        @canAccess(11707)
                        <p class="px-12 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Registration</p>
                        <a href="{{ route('settings.registration.edit') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('settings.registration.*') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            Registration Control
                        </a>
                        @endcanAccess
                        <p class="px-12 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Academic Configuration</p>
                        @canAccess(10121)
                        <a href="{{ route('school-types.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('school-types.*') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                            School Types
                        </a>
                        @endcanAccess
                        @canAccess(10131)
                        <a href="{{ route('educational-boards.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('educational-boards.*') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                            </svg>
                            Educational Boards
                        </a>
                        @endcanAccess
                        @canAccess(10141)
                        <a href="{{ route('student-types.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('student-types.*') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Student Types
                        </a>
                        @endcanAccess
                        @canAccess(10151)
                        <a href="{{ route('semester-batches.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('semester-batches.*') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Semester Batches
                        </a>
                        @endcanAccess
                        <p class="px-12 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Finance Configuration</p>
                        @canAccess(10161)
                        <a href="{{ route('fee-plans.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('fee-plans.*') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Fee Plans
                        </a>
                        <a href="{{ route('school-fee-plans.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('school-fee-plans.*') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            School Fee Plans
                        </a>
                        @endcanAccess
                        @canAccess(10301)
                        <a href="{{ route('initiator-fee-plans.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('initiator-fee-plans.*') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Initiator Fee Plans
                        </a>
                        @endcanAccess
                        @canAccess(10171)
                        <a href="{{ route('discount-rules.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('discount-rules.*') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            Discount Rules
                        </a>
                        @endcanAccess
                        <p class="px-12 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">System Logs</p>
                        @canAccess(11501)
                        <a href="{{ route('laravel-logs.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('laravel-logs.*') ? 'bg-gray-100 text-primary-500 font-semibold border-l-2 border-primary-500' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Laravel Logs
                        </a>
                        @endcanAccess
                        @canAccess(10001)
                        <a href="{{ route('audit-logs.index') }}"
                            class="flex items-center pl-12 pr-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('audit-logs.*') ? 'bg-gray-100 text-[#00bde0] font-semibold border-l-2 border-[#00bde0]' : 'text-gray-600 hover:bg-gray-50' }}">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            Action Logs
                        </a>
                        @endcanAccess
                    </div>
                </div>
                @endif
            </nav>

            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-gray-200">
                <div class="flex items-center">
                    <div
                        class="w-10 h-10 bg-[#00bde0] rounded-full flex items-center justify-center text-white font-medium">
                        {{ strtoupper(substr(Auth::user()->fname, 0, 1)) }}
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-gray-900">{{ Auth::user()->fname }} {{ Auth::user()->lname }}
                        </p>
                        <p class="text-xs text-gray-500">{{ Auth::user()->role?->rolename ?? 'User' }}</p>
                    </div>
                </div>
            </div>
        </aside>
        @endauth

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            @if(session()->has('impersonator_id'))
                <div class="bg-amber-500 text-white px-4 py-2 flex items-center justify-between text-sm font-semibold shadow-sm z-50">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <span>You are currently logged in as <strong class="underline">{{ Auth::user()->username }}</strong> (Impersonator Mode).</span>
                    </div>
                    <form action="{{ route('users.stop-impersonating') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-white text-amber-700 hover:bg-amber-50 px-3 py-1 rounded-md text-xs font-bold transition-all shadow-sm">
                            Return to Admin Account
                        </button>
                    </form>
                </div>
            @endif
            <!-- Top Navigation Bar -->
            <header class="bg-white shadow-sm border-b border-gray-200 z-10">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                    <!-- Hamburger Menu Button -->
                    <button @click="sidebarOpen = !sidebarOpen"
                        class="lg:hidden text-gray-500 hover:text-gray-700 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>

                    {{-- Global Search (hidden on mobile)
                    <div class="hidden md:block flex-1 max-w-2xl mx-4">
                        <x-global-search />
                    </div>
                    --}}

                    <!-- Page Title (visible on mobile, hidden on desktop when search is shown) -->
                    <div class="flex-1 lg:ml-0 ml-4 md:hidden">
                        <h2 class="text-lg font-semibold text-gray-900">@yield('page-title', '')</h2>
                    </div>

                    <!-- User Profile Dropdown -->
                    @auth
                    <div class="flex items-center space-x-4 ml-auto">
                        @if(\App\Support\ViewCohort::isAvailable())
                        @php $activeCohort = \App\Support\ViewCohort::selected(); @endphp
                        <!-- Global cohort switch: narrows every page to one student group -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" type="button"
                                class="flex items-center gap-2 px-3 py-2 rounded-lg border text-sm font-semibold transition-colors focus:outline-none
                                    {{ $activeCohort === 'all' ? 'border-gray-200 text-gray-700 hover:bg-gray-50' : 'border-[#00bde0] text-[#0099b8] bg-[#00bde0]/5' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                </svg>
                                <span class="hidden sm:inline">{{ \App\Support\ViewCohort::label($activeCohort) }}</span>
                                <svg class="w-3.5 h-3.5 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false" x-cloak x-transition
                                class="absolute right-0 mt-2 w-52 bg-white border border-gray-200 rounded-xl shadow-lg z-50 py-1">
                                <p class="px-4 py-2 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Showing data for</p>
                                @foreach(\App\Support\ViewCohort::options() as $key => $label)
                                    <a href="{{ route('view-mode.switch', $key) }}"
                                        class="flex items-center justify-between px-4 py-2 text-sm font-semibold transition-colors {{ $activeCohort === $key ? 'text-[#0099b8] bg-[#00bde0]/5' : 'text-gray-700 hover:bg-gray-50' }}">
                                        {{ $label }}
                                        @if($activeCohort === $key)
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        <!-- Notification Bell -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="text-gray-500 hover:text-gray-700 focus:outline-none relative">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                                    </path>
                                </svg>
                                @if(auth()->user() && auth()->user()->unreadNotifications->count() > 0)
                                <span class="absolute top-0 right-0 inline-block bg-red-500 rounded-full" style="width:10px; height:10px;"></span>
                                @endif
                            </button>

                            <div x-show="open" @click.away="open = false" x-cloak
                                class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50 transform translate-y-6">
                                <div class="px-4 py-2 border-b border-gray-100 font-medium text-gray-700">
                                    Notifications
                                </div>
                                <div class="max-h-64 overflow-y-auto">
                                    @if(auth()->user())
                                        @forelse(auth()->user()->unreadNotifications as $notification)
                                            <a href="{{ route('notifications.read', $notification->id) }}"
                                                class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-50">
                                                <p class="text-sm font-medium text-gray-800">{{ $notification->data['message'] ?? 'Notification' }}</p>
                                                <small class="text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</small>
                                            </a>
                                        @empty
                                            <div class="px-4 py-3 text-sm text-gray-500 text-center">
                                                No new notifications
                                            </div>
                                        @endforelse
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="flex items-center text-sm focus:outline-none">
                                <div
                                    class="w-8 h-8 bg-primary-500 rounded-full flex items-center justify-center text-white font-medium">
                                    {{ strtoupper(substr(Auth::user()->fname, 0, 1)) }}
                                </div>
                                <svg class="w-4 h-4 ml-2 text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <div x-show="open" @click.away="open = false" x-cloak
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                                <a href="{{ route('profile.show') }}"
                                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    Profile
                                </a>
                                <a href="{{ route('password.change') }}"
                                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z">
                                        </path>
                                    </svg>
                                    Change Password
                                </a>
                                <a href="{{ route('pin.change') }}"
                                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                        </path>
                                    </svg>
                                    Change PIN
                                </a>
                                <hr class="my-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="flex items-center w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                            </path>
                                        </svg>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endauth
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 print:overflow-visible print:h-auto print:block">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false"
        x-transition:enter="transition-opacity ease-linear duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-600 bg-opacity-75 z-40 lg:hidden" x-cloak>
    </div>

    <!-- Notification Container -->
    <x-notification-container />

    @auth
        @if(Auth::user()->hasRole('Student'))
            @php
                $student = Auth::user()->students()->first();
                $needsPhoto = $student && (!$student->document || !$student->document->photo);
            @endphp
            @if($needsPhoto)
                <!-- Persistent Modal overlay to block student access if photo is missing -->
                <div x-data="{ showPhotoModal: !localStorage.getItem('dismiss_photo_modal') }" x-show="showPhotoModal" class="fixed inset-0 bg-gray-900 bg-opacity-80 backdrop-blur-md z-[9999] flex items-center justify-center p-4 overflow-y-auto" x-cloak>
                    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full border border-gray-100 overflow-hidden transform transition-all duration-300 scale-100 relative">
                        <!-- Close Button -->
                        <button type="button" @click="localStorage.setItem('dismiss_photo_modal', 'true'); showPhotoModal = false;" class="absolute top-4 right-4 text-white/80 hover:text-white bg-white/10 hover:bg-white/20 rounded-full p-1.5 transition-all focus:outline-none" title="Close">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                        <div class="bg-gradient-to-r from-[#00bde0] to-[#0099b8] px-6 py-5 text-white text-center">
                            <div class="mx-auto w-16 h-16 bg-white/10 rounded-full flex items-center justify-center mb-3">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-extrabold tracking-wide">Upload Profile Photo</h3>
                            <p class="text-xs text-white/80 mt-1">Please upload a clear photograph of yourself to continue.</p>
                        </div>

                        <form action="{{ route('student.upload-photo') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6" @submit="localStorage.removeItem('dismiss_photo_modal')">
                            @csrf
                            @if($errors->has('photo'))
                                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm flex items-start gap-2">
                                    <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ $errors->first('photo') }}</span>
                                </div>
                            @endif

                            <div x-data="{ photoPreview: null }" class="flex flex-col items-center justify-center">
                                <!-- Photo Container -->
                                <div class="relative w-40 h-40 rounded-2xl border-2 border-dashed border-gray-300 hover:border-[#00bde0] transition-colors flex items-center justify-center overflow-hidden bg-gray-50 group">
                                    <template x-if="!photoPreview">
                                        <div class="text-center p-4">
                                            <svg class="w-10 h-10 text-gray-400 mx-auto mb-2 group-hover:text-[#00bde0] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <span class="text-xs text-gray-500 font-medium">Select Image</span>
                                        </div>
                                    </template>
                                    <template x-if="photoPreview">
                                        <img :src="photoPreview" class="w-full h-full object-cover">
                                    </template>
                                    
                                    <label for="student_photo_input" class="absolute inset-0 cursor-pointer opacity-0">
                                        <input type="file" id="student_photo_input" name="photo" class="hidden" accept="image/*" required
                                            @change="const file = $el.files[0]; if (file) { const reader = new FileReader(); reader.onload = (e) => { photoPreview = e.target.result; }; reader.readAsDataURL(file); }">
                                    </label>
                                </div>
                                
                                <p class="text-xs text-gray-400 mt-3 text-center">
                                    Allowed formats: JPG, JPEG, PNG. Max size: 2MB.
                                </p>
                            </div>

                            <div class="flex flex-col gap-3">
                                <button type="submit" class="w-full py-3 bg-gradient-to-r from-[#00bde0] to-[#0099b8] hover:shadow-lg text-white rounded-xl font-bold transition-all hover:scale-[1.02] active:scale-[0.98] shadow-md flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                    </svg>
                                    Upload & Proceed
                                </button>
                                
                                <button type="button" onclick="document.getElementById('modal-logout-form').submit();" class="w-full py-2.5 border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl font-semibold text-sm transition-all flex items-center justify-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    Logout
                                </button>
                            </div>
                        </form>
                        
                        <form id="modal-logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
                            @csrf
                        </form>
                    </div>
                </div>
            @endif
        @endif
    @endauth

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('scripts')
</body>

</html>