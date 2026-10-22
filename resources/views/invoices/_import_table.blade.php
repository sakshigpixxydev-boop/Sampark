{{-- Shared scrollable report table for the payment CSV import screen. --}}
<div class="overflow-x-auto border border-gray-200 rounded-lg" style="max-height: 26rem; overflow-y: auto;">
    <table class="min-w-full text-sm">
        <thead class="bg-gray-50 sticky top-0">
            <tr>
                @foreach ($headers as $header)
                    <th class="text-left font-medium text-gray-600 px-3 py-2 whitespace-nowrap">{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($rows as $row)
                <tr class="hover:bg-gray-50">
                    @foreach ($keys as $key)
                        <td class="px-3 py-1.5 text-gray-800 whitespace-nowrap">{{ $row[$key] ?? '' }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($headers) }}" class="px-3 py-3 text-gray-500">Nothing to show.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
