@php
    $isHeader = ! $account->is_postable;
    $paddingLeft = 24 + ($depth * 28);
    $typeLabel = strtolower($account->account_type ?? '');
@endphp

<tr class="transition-colors {{ $isHeader ? 'bg-fog-white/60 font-semibold' : 'hover:bg-mist-gray/40 font-normal' }}">
    {{-- Kode Akun dengan Indikator Hierarki --}}
    <td class="py-3.5 pr-6 text-body-sm" style="padding-left: {{ $paddingLeft }}px;">
        <div class="flex items-center gap-2.5">
            @if ($depth > 0)
                <span class="text-ash-gray/60 select-none">↳</span>
            @endif

            @if ($isHeader)
                <div class="h-6 w-6 rounded-input bg-primary-tint text-primary flex items-center justify-center shrink-0">
                    <x-dynamic-component component="lucide-folder" class="w-3.5 h-3.5" />
                </div>
                <span class="font-mono font-bold text-ink-black tracking-tight tabular-nums">
                    {{ $account->code }}
                </span>
            @else
                <div class="h-6 w-6 rounded-input bg-mist-gray text-slate-gray flex items-center justify-center shrink-0">
                    <x-dynamic-component component="lucide-file-text" class="w-3.5 h-3.5" />
                </div>
                <span class="font-mono text-slate-gray tabular-nums">
                    {{ $account->code }}
                </span>
            @endif
        </div>
    </td>

    {{-- Nama Akun --}}
    <td class="px-6 py-3.5 text-body-sm {{ $isHeader ? 'text-ink-black font-bold' : 'text-ink-black/90 font-medium' }}">
        {{ $account->name }}
    </td>

    {{-- Tipe Akun --}}
    <td class="px-6 py-3.5">
        <x-badge
            :status="match (true) {
                str_contains($typeLabel, 'asset')      => 'primary',
                str_contains($typeLabel, 'liability')  => 'peach',
                str_contains($typeLabel, 'equity')     => 'secondary',
                str_contains($typeLabel, 'revenue') || str_contains($typeLabel, 'income') => 'success',
                str_contains($typeLabel, 'expense')    => 'danger',
                default                                => 'neutral',
            }"
            variant="subtle"
        >
            {{ ucfirst($account->account_type) }}
        </x-badge>
    </td>

    {{-- Postable Status --}}
    <td class="px-6 py-3.5">
        @if ($account->is_postable)
            <x-badge status="success" variant="solid">
                Postable
            </x-badge>
        @else
            <x-badge status="neutral" variant="subtle">
                Header
            </x-badge>
        @endif
    </td>
</tr>

{{-- Render Sub-Akun Rekursif --}}
@if ($account->children && $account->children->isNotEmpty())
    @foreach ($account->children as $child)
        @include('finance::chart-of-accounts.partials.row', ['account' => $child, 'depth' => $depth + 1])
    @endforeach
@endif
