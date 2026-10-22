@extends('layouts.app')

@section('title', 'Agreement Templates')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Agreement Templates</h1>
            <p class="text-sm text-gray-600 mt-1">Manage terms and conditions for various registration flows</p>
        </div>
        @if(auth()->user()->hasPermission(11002))
        <div class="flex gap-3">
            <a href="{{ route('agreement-templates.create') }}" class="bg-primary-500 hover:bg-primary-600 text-white font-medium px-4 py-2 rounded-lg transition shadow-sm flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create Template
            </a>
        </div>
        @endif
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
            {{ session('success') }}
        </div>
    @endif

    <!-- Templates Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                        @if(auth()->user()->hasAnyPermission([11002, 11003]))
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($templates as $template)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $template->title }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-mono bg-gray-100 text-gray-600 rounded">
                                    {{ $template->slug }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($template->is_active)
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-md bg-green-100 text-green-800 border border-green-200">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-md bg-red-100 text-red-800 border border-red-200">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $template->updated_at->format('M d, Y') }}
                            </td>
                            @if(auth()->user()->hasAnyPermission([11002, 11003]))
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                @if(auth()->user()->hasPermission(11002))
                                <a href="{{ route('agreement-templates.edit', $template->agreement_template_id) }}" 
                                     class="text-primary-500 hover:text-primary-600 font-medium">Edit</a>
                                @endif
                                
                                @if(auth()->user()->hasPermission(11003))
                                <form action="{{ route('agreement-templates.destroy', $template->agreement_template_id) }}" 
                                    method="POST" 
                                    class="inline-block"
                                    onsubmit="return confirm('Are you sure you want to delete this template?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-medium">Delete</button>
                                </form>
                                @endif
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->hasAnyPermission([11002, 11003]) ? '5' : '4' }}" class="px-6 py-8 text-center text-gray-500">
                                No templates found. @if(auth()->user()->hasPermission(11002)) <a href="{{ route('agreement-templates.create') }}" class="text-primary-500 hover:text-primary-600 font-medium">Create one now</a> @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if(method_exists($templates, 'hasPages') && $templates->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $templates->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
