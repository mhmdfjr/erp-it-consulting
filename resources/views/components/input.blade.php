@props(['name', 'label' => null, 'type' => 'text', 'error' => null, 'placeholder' => null])

@php
    $errorMessage = $error ?: (($errors ?? null)?->first($name));
@endphp

<div class="w-full">
    @if ($label)
        <label for="{{ $name }}" class="block text-label font-medium text-slate-gray dark:text-ash-gray mb-1.5">{{ $label }}</label>
    @endif

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge([
            'class' => 'w-full rounded-input border bg-paper-white dark:bg-paper-white/5 px-3.5 py-2.5 text-body text-ink-black dark:text-paper-white placeholder-ash-gray dark:placeholder-slate-gray transition-colors focus:outline-none focus:shadow-focus-ring '
                . ($errorMessage
                    ? 'border-danger focus:border-danger'
                    : 'border-border-gray dark:border-border-gray/20 focus:border-primary')
        ]) }}
    />

    @if ($errorMessage)
        <p class="mt-1.5 text-caption font-medium text-danger">{{ $errorMessage }}</p>
    @endif
</div>
