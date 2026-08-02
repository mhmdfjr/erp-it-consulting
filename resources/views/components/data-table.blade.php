@props(['headers' => [], 'empty' => false])

<div class="bg-paper-white border border-border-gray rounded-table overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-fog-white border-b border-border-gray">
            <tr>
                @foreach ($headers as $head)
                    <th class="text-left text-label text-slate-gray px-4 py-3">{{ $head }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-border-gray">
            @if ($empty)
                <tr>
                    <td colspan="{{ count($headers) }}" class="py-16 text-center">
                        {{ $emptyState ?? '' }}
                    </td>
                </tr>
            @else
                {{ $slot }}
            @endif
        </tbody>
    </table>

    @isset($pagination)
        <div class="border-t border-border-gray px-4 py-3">
            {{ $pagination }}
        </div>
    @endisset
</div>
