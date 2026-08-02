@props(['name', 'label' => null, 'type' => 'text', 'error' => null])

@php
    $errorMessage = $error ?: (($errors ?? null)?->first($name));
@endphp

<div>
    @if ($label)
        <label for="{{ $name }}" class="block text-label text-slate-gray mb-1">{{ $label }}</label>
    @endif

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        {{ $attributes->merge([
            'class' => 'w-full rounded-input border px-3 py-2 text-body text-ink-black placeholder-ash-gray focus:outline-none focus:shadow-focus-ring '
                . ($errorMessage ? 'border-danger' : 'border-border-gray focus:border-ink-black'),
        ]) }}
    />

    @if ($errorMessage)
        <p class="mt-1 text-caption text-danger">{{ $errorMessage }}</p>
    @endif
</div>
