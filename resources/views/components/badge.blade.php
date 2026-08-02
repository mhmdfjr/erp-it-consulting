@props(['status' => 'info'])

@php
$colors = [
    'success' => 'bg-success-bg text-success',
    'warning' => 'bg-warning-bg text-warning',
    'danger' => 'bg-danger-bg text-danger',
    'info' => 'bg-info-bg text-info',
];
$classes = $colors[$status] ?? $colors['info'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-badge px-[10px] py-[2px] text-caption font-medium $classes"]) }}>
    {{ $slot }}
</span>
