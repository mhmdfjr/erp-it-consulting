@props(['variant' => 'primary', 'type' => 'button', 'href' => null])

@php
$variants = [
    'primary' => 'bg-ink-black text-paper-white hover:opacity-90 border border-transparent',
    'secondary' => 'bg-transparent text-ink-black border border-border-gray hover:bg-mist-gray',
    'danger' => 'bg-transparent text-danger border border-danger hover:bg-danger-bg',
];
$classes = $variants[$variant] ?? $variants['primary'];
$baseClasses = "inline-flex items-center justify-center rounded-button px-4 py-2 text-body font-medium transition disabled:opacity-50 disabled:cursor-not-allowed $classes";
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $baseClasses]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $baseClasses]) }}>{{ $slot }}</button>
@endif
