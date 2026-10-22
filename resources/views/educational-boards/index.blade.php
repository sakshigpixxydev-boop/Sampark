@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Educational Boards</h1>
            <p class="text-sm text-gray-600 mt-1">Manage educational board categories</p>
        </div>
        @canAccess(10132)
        <a href="{{ route('educational-boards.create') }}" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition">
            Add New Educational Board
        </a>
        @endcanAccess
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

    <!-- Educational Boards Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Board Name
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($educationalBoards as $board)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $board->boardname }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-3">
                                    @canAccess(10132)
                                    <a href="{{ route('educational-boards.edit', $board->eduboard_id) }}" class="text-[#00bde0] hover:text-[#0099b8]">
                                        Edit
                                    </a>
                                    @endcanAccess
                                    @canAccess(10133)
                                    <form action="{{ route('educational-boards.destroy', $board->eduboard_id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this educational board?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">
                                            Delete
                                        </button>
                                    </form>
                                    @endcanAccess
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-6 py-8 text-center text-gray-500">
                                No educational boards found. @canAccess(10132)
                                No educational boards found. <a href="{{ route('educational-boards.create') }}" class="text-[#00bde0] hover:text-[#0099b8]">Create one now</a>
                                No educational boards found. @endcanAccess
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($educationalBoards->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $educationalBoards->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
