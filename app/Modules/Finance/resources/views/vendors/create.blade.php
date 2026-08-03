<x-app-layout>
    <x-slot name="header"><h1 class="text-heading font-medium text-ink-black">Tambah Vendor</h1></x-slot>

    <form method="POST" action="{{ route('finance.vendors.store') }}" class="bg-paper-white border border-border-gray rounded-card shadow-subtle p-6 max-w-xl">
        @csrf
        @include('finance::vendors.partials.form')
        <div class="flex justify-end gap-3 mt-6">
            <x-button variant="secondary" href="{{ route('finance.vendors.index') }}">Batal</x-button>
            <x-button variant="primary" type="submit">Simpan</x-button>
        </div>
    </form>
</x-app-layout>
