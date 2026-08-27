{{-- resources/views/components/stat-card.blade.php --}}
@props([
    'label',
    'value',
    'delta' => null,
    'deltaType' => 'increase', // 'increase' | 'decrease' | 'neutral'
    'icon' => 'wallet',
    'sublabel' => null,
])

@php
    $deltaColors = [
        'increase' => 'text-success',
        'decrease' => 'text-danger',
        'neutral'  => 'text-slate-gray dark:text-ash-gray',
    ];
    $deltaColor = $deltaColors[$deltaType] ?? $deltaColors['increase'];
@endphp

<div class="bg-paper-white dark:bg-[#111c44] border border-border-gray/80 dark:border-border-gray/10 rounded-card p-4 shadow-subtle flex items-center justify-between transition-all hover:shadow-elevated">
    <!-- Left: Label, Value, and Delta -->
    <div class="flex flex-col justify-between">
        <span class="text-[11px] font-bold text-ash-gray dark:text-slate-gray uppercase tracking-wider select-none">
            {{ $label }}
        </span>

        <div class="flex items-baseline gap-2 mt-1">
            <span class="text-heading-sm md:text-heading font-bold text-ink-black dark:text-paper-white tabular-nums tracking-tight">
                {{ $value }}
            </span>

            @if ($delta)
                <span class="text-caption font-bold tabular-nums {{ $deltaColor }}">
                    {{ $delta }}
                </span>
            @endif
        </div>

        @if ($sublabel)
            <p class="text-caption text-slate-gray dark:text-ash-gray mt-0.5">
                {{ $sublabel }}
            </p>
        @endif
    </div>

    <!-- Right: Vibrant Icon Box -->
    <div class="h-11 w-11 rounded-card bg-primary text-paper-white flex items-center justify-center shadow-subtle shrink-0">
        <x-dynamic-component :component="'lucide-' . $icon" class="w-5 h-5 text-paper-white" />
    </div>
</div>
