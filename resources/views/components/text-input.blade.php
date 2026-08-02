@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-input border-border-gray text-ink-black placeholder-ash-gray focus:border-ink-black focus:shadow-focus-ring focus:ring-0']) }}>
