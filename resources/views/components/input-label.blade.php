<label {{ $attributes->merge(['class' => 'block text-label text-slate-gray mb-1']) }}>
    {{ $value ?? $slot }}
</label>
