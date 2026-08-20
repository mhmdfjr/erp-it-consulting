@props(['name', 'label' => null, 'type' => 'text', 'error' => null, 'placeholder' => null])

@php
    $errorMessage = $error ?: (($errors ?? null)?->first($name));
@endphp

<div class="w-full">
    @if ($label)
        <label for="{{ $name }}" class="block text-label font-medium text-slate-gray mb-1.5">{{ $label }}</label>
    @endif

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge([
            'class' => 'w-full rounded-input border bg-paper-white px-3.5 py-2.5 text-body text-ink-black placeholder-ash-gray transition-colors focus:outline-none focus:shadow-focus-ring '
                . ($errorMessage
                    ? 'border-danger focus:border-danger'
                    : 'border-border-gray focus:border-primary')
        ]) }}
    />

    @if ($errorMessage)
        <p class="mt-1.5 text-caption font-medium text-danger">{{ $errorMessage }}</p>
    @endif
</div>
