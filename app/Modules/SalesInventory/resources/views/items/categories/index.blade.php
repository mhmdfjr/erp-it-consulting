<x-app-layout>
    <x-slot name="header">Kategori Barang & Jasa</x-slot>

    <form method="POST" action="{{ route('sales.categories.store') }}" class="flex gap-2 mb-6 max-w-2xl">
        @csrf
        <input type="text" name="name" required placeholder="Nama kategori baru"
            class="flex-1 rounded-input border-border-gray" value="{{ old('name') }}" />
        <x-button type="submit" variant="primary">+ Buat Kategori</x-button>
        <x-button type="submit" variant="secondary" href="{{ route('sales.items.index') }}">Kembali ke Item</x-button>
    </form>
    @error('name') <p class="text-caption text-danger mb-4">{{ $message }}</p> @enderror

    <x-data-table :headers="['Nama Kategori', 'Jumlah Item']" :empty="$categories->isEmpty()">
        <x-slot name="emptyState">
            <p class="text-body-sm text-slate-gray">Belum ada kategori, tambahkan lewat form di atas.</p>
        </x-slot>

        @foreach ($categories as $category)
            <tr>
                <td class="px-4 py-3">{{ $category->name }}</td>
                <td class="px-4 py-3 tabular-nums">{{ $category->items_count }}</td>
            </tr>
        @endforeach
    </x-data-table>
</x-app-layout>
