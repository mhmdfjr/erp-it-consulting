@props([
    'title' => null,
    'subtitle' => null,
    'headers' => [],
    'empty' => false,
    'action' => null,
])

<div class="bg-paper-white dark:bg-[#111c44] border border-border-gray/80 dark:border-border-gray/10 rounded-card shadow-subtle overflow-hidden">
    {{-- Card Header --}}
    @if ($title || $action)
        <div class="px-6 py-5 flex items-center justify-between border-b border-border-gray/60 dark:border-border-gray/10">
            <div>
                @if ($title)
                    <h3 class="text-heading-sm font-semibold text-ink-black dark:text-paper-white tracking-tight">{{ $title }}</h3>
                @endif
                @if ($subtitle)
                    <p class="text-caption text-slate-gray dark:text-ash-gray mt-0.5 flex items-center gap-1">
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
                <tr class="border-b border-border-gray/60 dark:border-border-gray/10">
                    @foreach ($headers as $index => $head)
                        <th scope="col" class="px-6 py-3.5 text-[10px] font-bold text-ash-gray dark:text-slate-gray uppercase tracking-wider select-none {{ $head == 'Aksi' ? 'text-right' : '' }}">
                            {{ $head }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-border-gray/50 dark:divide-border-gray/10">
                @if ($empty)
                    <tr>
                        <td colspan="{{ max(count($headers), 1) }}" class="py-16 text-center">
                            @if (isset($emptyState))
                                {{ $emptyState }}
                            @else
                                <div class="flex flex-col items-center justify-center text-slate-gray dark:text-ash-gray">
                                    <x-dynamic-component component="lucide-inbox" class="w-10 h-10 text-ash-gray dark:text-slate-gray mb-2" />
                                    <p class="text-body font-medium text-ink-black dark:text-paper-white">Tidak ada data ditemukan</p>
                                    <p class="text-caption text-slate-gray dark:text-ash-gray mt-0.5">Belum ada baris data untuk ditampilkan saat ini.</p>
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
        <div class="border-t border-border-gray/60 dark:border-border-gray/10 px-6 py-4 bg-fog-white/30 dark:bg-paper-white/5">
            {{ $pagination }}
        </div>
    @endisset
</div>
