@extends('layouts.app')

@section('title', 'Audit Log Details')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Audit Log Details</h2>
            <p class="text-sm text-gray-600 mt-1">View detailed information about this audit log entry</p>
        </div>
        <a href="{{ route('audit-logs.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
            Back to List
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Basic Information -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
        
        <dl class="space-y-3">
            <div>
                <dt class="text-sm font-medium text-gray-500">Event Type</dt>
                <dd class="mt-1">
                    <span class="px-2 py-1 text-xs font-medium rounded-full
                        @if($auditLog->event_type === 'CREATE') bg-green-100 text-green-800
                        @elseif($auditLog->event_type === 'UPDATE') bg-blue-100 text-blue-800
                        @elseif($auditLog->event_type === 'DELETE') bg-red-100 text-red-800
                        @elseif(str_contains($auditLog->event_type, 'LOGIN')) bg-purple-100 text-purple-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        {{ $auditLog->event_type }}
                    </span>
                </dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">Description</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $auditLog->description }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">Timestamp</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $auditLog->created_at->format('Y-m-d H:i:s') }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">User</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    @if($auditLog->user)
                        {{ $auditLog->user->firstname }} {{ $auditLog->user->lastname }} (ID: {{ $auditLog->user_id }})
                    @else
                        <span class="text-gray-400">System</span>
                    @endif
                </dd>
            </div>

            @if($auditLog->auditable_type)
            <div>
                <dt class="text-sm font-medium text-gray-500">Model</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    {{ class_basename($auditLog->auditable_type) }} #{{ $auditLog->auditable_id }}
                </dd>
            </div>
            @endif
        </dl>
    </div>

    <!-- Request Information -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Request Information</h3>
        
        <dl class="space-y-3">
            <div>
                <dt class="text-sm font-medium text-gray-500">IP Address</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $auditLog->ip_address }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">HTTP Method</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $auditLog->method }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">URL</dt>
                <dd class="mt-1 text-sm text-gray-900 break-all">{{ $auditLog->url }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">User Agent</dt>
                <dd class="mt-1 text-sm text-gray-900 break-all">{{ $auditLog->user_agent }}</dd>
            </div>
        </dl>
    </div>

    <!-- Old Values -->
    @if($auditLog->old_values)
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Old Values</h3>
        <pre class="bg-gray-50 p-4 rounded-lg text-xs overflow-x-auto">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT) }}</pre>
    </div>
    @endif

    <!-- New Values -->
    @if($auditLog->new_values)
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">New Values</h3>
        <pre class="bg-gray-50 p-4 rounded-lg text-xs overflow-x-auto">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT) }}</pre>
    </div>
    @endif

    <!-- Metadata -->
    @if($auditLog->metadata)
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 lg:col-span-2">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Additional Metadata</h3>
        <pre class="bg-gray-50 p-4 rounded-lg text-xs overflow-x-auto">{{ json_encode($auditLog->metadata, JSON_PRETTY_PRINT) }}</pre>
    </div>
    @endif
</div>
@endsection
