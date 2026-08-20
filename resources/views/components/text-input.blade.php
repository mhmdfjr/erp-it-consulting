@props(['disabled' => false])

<input
    @disabled($disabled)
    {{ $attributes->merge([
        'class' => 'w-full rounded-input border border-border-gray bg-paper-white px-3.5 py-2 text-body text-ink-black placeholder-ash-gray transition-all focus:border-primary focus:outline-none focus:shadow-focus-ring focus:ring-0 disabled:bg-mist-gray disabled:cursor-not-allowed'
    ]) }}
>
