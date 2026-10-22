{{--
    One registration channel rendered from the uniform array built by
    App\Services\RegistrationChannelStats. Both dashboard tabs include this
    same file — only $stats differs.
--}}
@php
    $toneClasses = [
        'green' => ['bg' => 'bg-green-50 border-green-100', 'text' => 'text-green-700', 'label' => 'text-green-800'],
        'amber' => ['bg' => 'bg-amber-50 border-amber-100', 'text' => 'text-amber-700', 'label' => 'text-amber-800'],
        'red' => ['bg' => 'bg-red-50 border-red-100', 'text' => 'text-red-700', 'label' => 'text-red-800'],
    ];
@endphp

<div class="space-y-6">

    <!-- Headline numbers -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 rounded-xl border border-gray-100 bg-gray-50/50">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Registrations</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="p-4 rounded-xl border border-gray-100 bg-gray-50/50">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">This Month</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['this_month']) }}</p>
        </div>
        <div class="p-4 rounded-xl border border-gray-100 bg-gray-50/50">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Fees Paid</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['paid']) }}</p>
        </div>
        <div class="p-4 rounded-xl border border-gray-100 bg-gray-50/50">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Awaiting Payment</p>
            <p class="text-3xl font-bold {{ $stats['awaiting'] > 0 ? 'text-amber-600' : 'text-gray-900' }} mt-2">
                {{ number_format($stats['awaiting']) }}
            </p>
        </div>
    </div>

    <!-- Status + split breakdowns -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">{{ $stats['status_title'] }}</p>
            <div class="grid grid-cols-3 gap-3">
                @foreach($stats['status_rows'] as $row)
                    @php $tone = $toneClasses[$row['tone']] ?? $toneClasses['amber']; @endphp
                    <div class="rounded-xl border p-3 {{ $tone['bg'] }}">
                        <p class="text-2xl font-bold {{ $tone['text'] }}">{{ number_format($row['value']) }}</p>
                        <p class="text-xs font-semibold {{ $tone['label'] }} mt-1">{{ $row['label'] }}</p>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 flex justify-between items-center pt-4 border-t border-gray-100">
                <div>
                    <span class="text-sm font-semibold text-gray-600">Fees Collected</span>
                    <span class="block text-[11px] text-gray-400">{{ $stats['collected_note'] }}</span>
                </div>
                <span class="text-lg font-bold text-[#0099b8]">₹{{ number_format($stats['collected'], 2) }}</span>
            </div>
        </div>

        <div>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">{{ $stats['split_title'] }}</p>
            <div class="space-y-2">
                @foreach($stats['split_rows'] as $row)
                    @php $pct = $stats['total'] > 0 ? round($row['value'] / $stats['total'] * 100) : 0; @endphp
                    <div class="p-3 rounded-xl border border-gray-100 bg-gray-50/50">
                        <div class="flex justify-between items-center text-sm">
                            <span class="font-semibold text-gray-700">{{ $row['label'] }}</span>
                            <span class="font-bold text-gray-900">{{ number_format($row['value']) }}</span>
                        </div>
                        <div class="mt-2 h-1.5 w-full rounded-full bg-gray-200 overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r from-[#00bde0] to-[#0099b8]" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Recent registrations -->
    <div>
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Recent Registrations</p>
            <a href="{{ $stats['list_url'] }}" class="text-sm font-semibold text-[#0099b8] hover:text-[#007b94]">View all</a>
        </div>
        <div class="overflow-x-auto rounded-xl border border-gray-100">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Student</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fee Plan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Payment</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Registered</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($stats['recent'] as $row)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.registrations.show', $row['id']) }}" class="text-sm font-semibold text-gray-900 hover:text-[#0099b8]">
                                    {{ $row['name'] }}
                                </a>
                                <p class="text-xs text-gray-500">{{ $row['contact'] ?: '—' }} &middot; {{ $row['context'] }}</p>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $row['plan'] ?: '—' }}
                                @if($row['amount'] !== null)
                                    <span class="block text-xs font-semibold text-[#0099b8]">₹{{ number_format((float) $row['amount'], 2) }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @include('school.registrations.partials.status-badge', ['status' => $row['status']])
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                {{ $row['date'] ? $row['date']->format('d/m/Y') : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-sm text-gray-500">No registrations in this channel yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
