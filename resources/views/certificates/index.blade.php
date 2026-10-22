@extends('layouts.app')

@section('title', 'Certificates')
@section('page-title', 'Certificates')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Certificate Generation</h1>
            <p class="text-sm text-gray-600 mt-1">Generate and manage student certificates</p>
        </div>
        <a href="{{ route('certificates.create') }}" class="bg-primary-500 hover:bg-primary-600 text-white font-medium px-4 py-2 rounded-lg transition text-sm flex items-center shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add Certificate
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cert ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recipient</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($certificates as $certificate)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                {{ $certificate->certificate_no }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $certificate->type === 'STUDENT' ? 'bg-blue-100 text-blue-700' : 
                                       ($certificate->type === 'EVENT' ? 'bg-purple-100 text-purple-700' : 'bg-orange-100 text-orange-700') }}">
                                    {{ $certificate->type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">
                                {{ $certificate->recipient_name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $certificate->batch ? ($certificate->batch->standard->stdname ?? 'N/A') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $certificate->generated_date ? $certificate->generated_date->format('d/m/Y') : '' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end items-center space-x-3">
                                    <a href="{{ route('certificates.show', $certificate->certificate_id) }}?print=1" target="_blank" class="text-gray-600 hover:text-primary-500 transition" title="Download Certificate">
                                        Download 
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                No certificates generated yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($certificates->hasPages())
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            {{ $certificates->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
