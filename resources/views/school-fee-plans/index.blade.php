@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">School Fee Plans</h1>
        @canAccess(10162)
        <a href="{{ route('school-fee-plans.create') }}" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition">
            Add New School Fee Plan
        </a>
        @endcanAccess
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fee Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Semester Covers</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valid From</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valid To</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Auto Close Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($feePlans as $feePlan)
                        <tr class="hover:bg-gray-50">
                             <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $feePlan->fee_name }}
                            </td>
                             <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ₹{{ number_format($feePlan->amount, 2) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                @forelse($feePlan->semesterBatches as $batch)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 mb-1">
                                        {{ $batch->display_name }} (Sem {{ $batch->semester }})
                                    </span>
                                @empty
                                    <span class="text-gray-500">None</span>
                                @endforelse
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $feePlan->valid_from ? $feePlan->valid_from->format('M d, Y') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $feePlan->valid_to ? $feePlan->valid_to->format('M d, Y') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $feePlan->plan_auto_close_date ? $feePlan->plan_auto_close_date->format('M d, Y') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                @canAccess(10162)
                                <a href="{{ route('school-fee-plans.edit', $feePlan->fee_plan_id) }}" class="text-[#00bde0] hover:text-[#0099b8] mr-3">Edit</a>
                                @endcanAccess
                                @canAccess(10163)
                                <form action="{{ route('school-fee-plans.destroy', $feePlan->fee_plan_id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this school fee plan?')">Delete</button>
                                </form>
                                @endcanAccess
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                No school fee plans found. @canAccess(10162)
                                No school fee plans found. <a href="{{ route('school-fee-plans.create') }}" class="text-[#00bde0] hover:text-[#0099b8]">Create one now</a>
                                No school fee plans found. @endcanAccess
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $feePlans->links() }}
    </div>
</div>
@endsection
