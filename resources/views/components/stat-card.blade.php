{{-- resources/views/components/stat-card.blade.php --}}
@props([
    'label',
    'value',
    'sublabel' => null,
])

<div class="bg-paper-white border border-border-gray rounded-card p-4 shadow-subtle">
    <p class="text-label text-slate-gray">{{ $label }}</p>
    <p class="text-heading-lg font-medium text-ink-black mt-1 tabular-nums">{{ $value }}</p>
    @if($sublabel)
        <p class="text-caption text-ash-gray mt-1">{{ $sublabel }}</p>
    @endif
</div>
