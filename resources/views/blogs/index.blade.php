@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Blog Management</h1>
            <p class="text-sm text-gray-600 mt-1">Manage and review student blogs</p>
        </div>
        @if(auth()->user()->hasRole('Student'))
        <a href="{{ route('blogs.create') }}" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition">
            Write New Blog
        </a>
        @endif
    </div>

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

    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($blogs as $blog)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $blog->title }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ optional($blog->student)->fname }} {{ optional($blog->student)->lname }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $blog->created_at->format('d M, Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($blog->status == 'Approved')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">{{ $blog->status }}</span>
                                @elseif($blog->status == 'Rejected')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">{{ $blog->status }}</span>
                                @elseif($blog->status == 'Pending')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">{{ $blog->status }}</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ $blog->status }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium flex justify-end gap-3">
                                <a href="{{ route('blogs.show', $blog->id) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                
                                @if(auth()->user()->hasRole('Student') && $blog->status !== 'Approved')
                                    <a href="{{ route('blogs.edit', $blog->id) }}" class="text-[#00bde0] hover:text-[#0099b8]">Edit</a>
                                @endif

                                @if((auth()->user()->hasRole('Initiator') || auth()->user()->hasRole('Admin')) && in_array($blog->status, ['Pending', 'Approved', 'Rejected']))
                                    <a href="{{ route('blogs.review', $blog->id) }}" class="text-indigo-600 hover:text-indigo-900">Review</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                No blogs found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($blogs->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $blogs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
