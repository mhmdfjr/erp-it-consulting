@props(['variant' => 'primary', 'type' => 'button', 'href' => null, 'size' => 'md'])

@php
$sizes = [
    'sm' => 'px-3 py-1.5 text-caption',
    'md' => 'px-4 py-2.5 text-body-sm',
    'lg' => 'px-6 py-3 text-body',
];

$variants = [
    'primary'   => 'bg-primary text-paper-white hover:bg-primary-hover shadow-subtle focus:shadow-focus-ring',
    'secondary' => 'bg-transparent text-ink-black border border-border-gray hover:bg-mist-gray',
    'dark'      => 'bg-ink-black text-paper-white hover:opacity-90',
    'danger'    => 'bg-transparent text-danger border border-danger hover:bg-danger-bg',
    'ghost'     => 'bg-transparent text-primary hover:bg-primary-tint',
];

$sizeClass = $sizes[$size] ?? $sizes['md'];
$variantClass = $variants[$variant] ?? $variants['primary'];
$baseClasses = "inline-flex items-center justify-center rounded-button font-medium transition-all duration-200 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed $sizeClass $variantClass";
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $baseClasses]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $baseClasses]) }}>{{ $slot }}</button>
@endif
