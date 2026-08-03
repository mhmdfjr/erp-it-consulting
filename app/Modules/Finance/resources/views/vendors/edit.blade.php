<x-app-layout>
    <x-slot name="header"><h1 class="text-heading font-medium text-ink-black">Edit Vendor</h1></x-slot>

    <form method="POST" action="{{ route('finance.vendors.update', $vendor) }}" class="bg-paper-white border border-border-gray rounded-card shadow-subtle p-6 max-w-xl">
        @csrf @method('PUT')
        @include('finance::vendors.partials.form', ['vendor' => $vendor])
        <div class="flex justify-end gap-3 mt-6">
            <x-button variant="secondary" href="{{ route('finance.vendors.index') }}">Batal</x-button>
            <x-button variant="primary" type="submit">Perbarui</x-button>
        </div>
    </form>
</x-app-layout>
