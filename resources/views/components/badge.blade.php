@props(['status' => 'info', 'variant' => 'subtle'])

@php
$variants = [
    'subtle' => [
        'primary'   => 'bg-primary-tint text-primary border border-primary/20',
        'secondary' => 'bg-secondary-tint text-secondary border border-secondary/20',
        'peach'     => 'bg-accent-peach-tint text-[#c2410c] border border-accent-peach/30',
        'pink'      => 'bg-accent-pink-tint text-[#be185d] border border-accent-pink/30',
        'success'   => 'bg-success-bg text-success border border-success/20',
        'warning'   => 'bg-warning-bg text-warning border border-warning/20',
        'danger'    => 'bg-danger-bg text-danger border border-danger/20',
        'info'      => 'bg-info-bg text-info border border-info/20',
        'neutral'   => 'bg-mist-gray text-slate-gray border border-border-gray',
    ],
    'solid' => [
        'primary'   => 'bg-primary text-paper-white',
        'secondary' => 'bg-secondary text-paper-white',
        'peach'     => 'bg-accent-peach text-[#15131b]',
        'pink'      => 'bg-accent-pink text-paper-white',
        'success'   => 'bg-success text-paper-white',
        'warning'   => 'bg-warning text-paper-white',
        'danger'    => 'bg-danger text-paper-white',
        'info'      => 'bg-info text-paper-white',
        'neutral'   => 'bg-slate-gray text-paper-white',
    ]
];

$selectedVariant = $variants[$variant] ?? $variants['subtle'];
$classes = $selectedVariant[$status] ?? $selectedVariant['info'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center justify-center rounded-badge px-2.5 py-0.5 text-[11px] font-semibold tracking-wide uppercase leading-tight $classes"]) }}>
    {{ $slot }}
</span>
