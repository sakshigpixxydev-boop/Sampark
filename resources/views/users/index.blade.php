@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Users</h1>
            <p class="text-sm text-gray-600 mt-1">Manage system users and their access</p>
        </div>
        <a href="{{ route('users.create') }}" class="bg-primary-500 hover:bg-primary-600 text-white font-medium px-4 py-2 rounded-lg transition">
            Add New User
        </a>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
            {{ session('error') }}
        </div>
    @endif

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Username
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Role
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Email
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Phone
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $user->fname }} {{ $user->mname }} {{ $user->lname }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $user->username }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $user->role->rolename ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><x-masked-email :email="$user->email" /></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 flex items-center gap-2 group">
                                    <x-masked-phone :number="$user->phone" />
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->inactive)
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                        Inactive
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-3">
                                    @if(Auth::id() !== $user->sysuser_id)
                                        <form action="{{ route('users.impersonate', $user->sysuser_id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-primary-500 hover:text-primary-600 font-semibold" title="Login As User">
                                                Login As
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('users.edit', $user->sysuser_id) }}" class="text-primary-500 hover:text-primary-600">
                                        Edit
                                    </a>
                                    
                                    @php
                                        $roleName = $user->role->rolename ?? '';
                                    @endphp
                                    
                                    @if(in_array($roleName, ['Initiator', 'School']))
                                        <a href="{{ route('users.assign-schools', $user->sysuser_id) }}" 
                                           class="text-primary-500 hover:text-primary-600"
                                           title="Assign Schools">
                                            Schools
                                        </a>
                                    @endif
                                    
                                    @if($roleName === 'School')
                                        <a href="{{ route('users.school-financial-config', $user->sysuser_id) }}" 
                                           class="text-primary-500 hover:text-primary-600"
                                           title="Financial Configuration">
                                            Financials
                                        </a>
                                    @endif
                                    
                                    @if($roleName === 'Initiator')
                                        <a href="{{ route('users.assign-students', $user->sysuser_id) }}" 
                                           class="text-primary-500 hover:text-primary-600"
                                           title="Assign Students">
                                            Students
                                        </a>
 
                                        @if($user->financial_type === 'paid')
                                            <a href="{{ route('users.financial-config', $user->sysuser_id) }}" 
                                               class="text-primary-500 hover:text-primary-600"
                                               title="Financial Configuration">
                                                Financials
                                            </a>
                                            <a href="{{ route('users.initiator-payments', $user->sysuser_id) }}" 
                                               class="text-primary-500 hover:text-primary-600"
                                               title="Manage Payments">
                                                Payments
                                            </a>
                                        @endif
                                    @endif

                                    @canAccess(10023)
                                        @if(Auth::id() !== $user->sysuser_id)
                                            <form action="{{ route('users.destroy', $user->sysuser_id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    @endcanAccess
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                No users found. <a href="{{ route('users.create') }}" class="text-primary-500 hover:text-primary-600">Create one now</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
