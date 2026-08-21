<x-app-layout>
    <x-slot name="header">
        Kategori Barang & Jasa
    </x-slot>

    {{-- Alert Messages --}}
    @if (session('success'))
        <div class="mb-5 rounded-input bg-success-bg border border-success/20 text-success px-4 py-3 text-body-sm flex items-center gap-2">
            <x-dynamic-component component="lucide-check-circle-2" class="w-4 h-4 shrink-0" />
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-5 rounded-input bg-danger-bg border border-danger/20 text-danger px-4 py-3 text-body-sm flex items-center gap-2">
            <x-dynamic-component component="lucide-alert-circle" class="w-4 h-4 shrink-0" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="max-w-4xl space-y-6">
        {{-- Quick Create Category Form Card --}}
        <div class="bg-paper-white border border-border-gray/80 rounded-card p-5 shadow-subtle">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-border-gray/60">
                <h3 class="text-heading-sm font-semibold text-ink-black tracking-tight">Tambah Kategori Baru</h3>
                <x-button variant="secondary" size="sm" href="{{ route('sales.items.index') }}" class="gap-1.5">
                    <x-dynamic-component component="lucide-arrow-left" class="w-4 h-4" />
                    <span>Kembali ke Katalog</span>
                </x-button>
            </div>

            <form method="POST" action="{{ route('sales.categories.store') }}" class="flex flex-col sm:flex-row items-center gap-3">
                @csrf
                <div class="flex-1 w-full">
                    <input
                        type="text"
                        name="name"
                        required
                        placeholder="Masukkan nama kategori baru (contoh: Hardware Networking)"
                        value="{{ old('name') }}"
                        class="w-full rounded-input border bg-paper-white px-3.5 py-2.5 text-body text-ink-black placeholder-ash-gray transition focus:ring-0 focus:outline-none focus:shadow-focus-ring {{ $errors->has('name') ? 'border-danger focus:border-danger' : 'border-border-gray focus:border-primary' }}"
                    />
                </div>
                <x-button type="submit" variant="primary" size="md" class="w-full sm:w-auto gap-1.5">
                    <x-dynamic-component component="lucide-plus" class="w-4 h-4" />
                    <span>Buat Kategori</span>
                </x-button>
            </form>
            @error('name')
                <p class="text-caption font-medium text-danger mt-2">{{ $message }}</p>
            @enderror
        </div>

        {{-- Categories Table Card --}}
        <x-data-table
            title="Daftar Kategori Katalog"
            subtitle="Klasifikasi pengelompokan produk dan layanan jasa"
            :headers="['Nama Kategori', 'Total Item Terhubung']"
            :empty="$categories->isEmpty()"
        >
            <x-slot name="emptyState">
                <div class="flex flex-col items-center justify-center py-8 text-center">
                    <x-dynamic-component component="lucide-tag" class="w-8 h-8 text-ash-gray mb-2" />
                    <p class="text-body font-medium text-ink-black">Belum Ada Kategori</p>
                    <p class="text-caption text-slate-gray mt-0.5">Tambahkan kategori melalui form di atas.</p>
                </div>
            </x-slot>

            @foreach ($categories as $category)
                <tr class="hover:bg-mist-gray/40 transition-colors">
                    {{-- Nama Kategori --}}
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2.5">
                            <div class="h-8 w-8 rounded-card bg-fog-white border border-border-gray/80 text-primary flex items-center justify-center shrink-0">
                                <x-dynamic-component component="lucide-tag" class="w-4 h-4" />
                            </div>
                            <span class="text-body-sm font-semibold text-ink-black">{{ $category->name }}</span>
                        </div>
                    </td>

                    {{-- Jumlah Item --}}
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center gap-1.5 text-caption font-semibold text-slate-gray tabular-nums bg-fog-white border border-border-gray/80 px-2.5 py-1 rounded-input">
                            <x-dynamic-component component="lucide-box" class="w-3.5 h-3.5 text-ash-gray" />
                            {{ $category->items_count ?? 0 }} Item
                        </span>
                    </td>
                </tr>
            @endforeach
        </x-data-table>
    </div>
</x-app-layout>
