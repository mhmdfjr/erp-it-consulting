<x-app-layout>
    <x-slot name="header">
        Tambah Vendor Baru
    </x-slot>

    <div class="max-w-2xl bg-paper-white border border-border-gray/80 rounded-card p-6 shadow-subtle">
        <div class="border-b border-border-gray/60 pb-4 mb-6">
            <h3 class="text-heading-sm font-semibold text-ink-black">Informasi Rekanan Vendor</h3>
            <p class="text-caption text-slate-gray mt-0.5">Lengkapi data profil vendor untuk keperluan purchase order dan vendor bills.</p>
        </div>

        <form method="POST" action="{{ route('finance.vendors.store') }}">
            @csrf

            @include('finance::vendors.partials.form')

            <div class="flex items-center justify-end gap-3 pt-5 mt-6 border-t border-border-gray/60">
                <x-button variant="secondary" size="md" href="{{ route('finance.vendors.index') }}">
                    Batal
                </x-button>
                <x-button variant="primary" size="md" type="submit">
                    Simpan Vendor
                </x-button>
            </div>
        </form>
    </div>
</x-app-layout>
