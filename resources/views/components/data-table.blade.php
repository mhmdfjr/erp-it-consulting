@props([
    'title' => null,
    'subtitle' => null,
    'headers' => [],
    'empty' => false,
    'action' => null,
])

<div class="bg-paper-white border border-border-gray/80 rounded-card shadow-subtle overflow-hidden">
    {{-- Card Header --}}
    @if ($title || $action)
        <div class="px-6 py-5 flex items-center justify-between border-b border-border-gray/60">
            <div>
                @if ($title)
                    <h3 class="text-heading-sm font-semibold text-ink-black tracking-tight">{{ $title }}</h3>
                @endif
                @if ($subtitle)
                    <p class="text-caption text-slate-gray mt-0.5 flex items-center gap-1">
                        {{ $subtitle }}
                    </p>
                @endif
            </div>
            @if ($action)
                <div class="flex items-center gap-2">
                    {{ $action }}
                </div>
            @endif
        </div>
    @endif

    {{-- Table Wrapper --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-border-gray/60">
                    @foreach ($headers as $index => $head)
                        <th scope="col" class="px-6 py-3.5 text-[10px] font-bold text-ash-gray uppercase tracking-wider select-none {{ $head == 'Aksi' ? 'text-right' : '' }}">
                            {{ $head }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-border-gray/50">
                @if ($empty)
                    <tr>
                        <td colspan="{{ max(count($headers), 1) }}" class="py-16 text-center">
                            @if (isset($emptyState))
                                {{ $emptyState }}
                            @else
                                <div class="flex flex-col items-center justify-center text-slate-gray">
                                    <x-dynamic-component component="lucide-inbox" class="w-10 h-10 text-ash-gray mb-2" />
                                    <p class="text-body font-medium text-ink-black">Tidak ada data ditemukan</p>
                                    <p class="text-caption text-slate-gray mt-0.5">Belum ada baris data untuk ditampilkan saat ini.</p>
                                </div>
                            @endif
                        </td>
                    </tr>
                @else
                    {{ $slot }}
                @endif
            </tbody>
        </table>
    </div>

    {{-- Pagination Footer --}}
    @isset($pagination)
        <div class="border-t border-border-gray/60 px-6 py-4 bg-fog-white/30">
            {{ $pagination }}
        </div>
    @endisset
</div>
