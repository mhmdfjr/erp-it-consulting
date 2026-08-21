<x-app-layout>
    <x-slot name="header">
        Daftar Vendor & Rekanan
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

    {{-- Alpine Wrapper untuk Modal Hapus --}}
    <div x-data="{
        deleteUrl: '',
        vendorName: '',
        openDeleteModal(url, name) {
            this.deleteUrl = url;
            this.vendorName = name;
            $dispatch('open-modal', 'confirm-delete-vendor');
        }
    }">
        {{-- Vendors Table Card --}}
        <x-data-table
            title="Daftar Vendor & Rekanan Usaha"
            subtitle="Kelola data mitra penyedia barang, jasa pengadaan, dan tagihan faktur vendor"
            :headers="['Nama Vendor & Email', 'NPWP Badan', 'No. Telepon', 'Alamat Kantor', 'Aksi']"
            :empty="$vendors->isEmpty()"
        >
            <x-slot name="action">
                @can('finance.vendor.manage')
                    <x-button href="{{ route('finance.vendors.create') }}" variant="primary" size="sm" class="gap-1.5">
                        <x-dynamic-component component="lucide-building-2" class="w-4 h-4" />
                        <span>Tambah Vendor</span>
                    </x-button>
                @endcan
            </x-slot>

            <x-slot name="emptyState">
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="w-12 h-12 rounded-full bg-primary-tint text-primary flex items-center justify-center mb-3">
                        <x-dynamic-component component="lucide-truck" class="w-6 h-6" />
                    </div>
                    <h4 class="text-body-lg font-semibold text-ink-black">Belum Ada Vendor Terdaftar</h4>
                    <p class="text-caption text-slate-gray mt-1 max-w-sm mb-4">Tambahkan vendor atau supplier rekanan untuk mulai mencatat transaksi tagihan pembelian (vendor bills).</p>
                    @can('finance.vendor.manage')
                        <x-button variant="primary" href="{{ route('finance.vendors.create') }}" size="sm">
                            + Tambah Vendor
                        </x-button>
                    @endcan
                </div>
            </x-slot>

            @foreach ($vendors as $vendor)
                <tr class="hover:bg-mist-gray/40 transition-colors">
                    {{-- Avatar & Nama Vendor --}}
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-card bg-primary-tint text-primary font-bold flex items-center justify-center text-caption shrink-0 shadow-subtle">
                                {{ strtoupper(substr($vendor->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="text-body-sm font-semibold text-ink-black leading-tight">{{ $vendor->name }}</p>
                                <p class="text-caption text-slate-gray font-medium">{{ $vendor->email ?: 'Tidak ada email' }}</p>
                            </div>
                        </div>
                    </td>

                    {{-- NPWP --}}
                    <td class="px-6 py-4">
                        @if ($vendor->npwp)
                            <span class="font-mono text-caption font-semibold text-slate-gray bg-fog-white border border-border-gray/80 px-2.5 py-1 rounded-input tabular-nums">
                                {{ $vendor->npwp }}
                            </span>
                        @else
                            <span class="text-caption text-ash-gray">-</span>
                        @endif
                    </td>

                    {{-- Telepon --}}
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-1.5 text-body-sm text-ink-black font-medium tabular-nums">
                            @if ($vendor->phone)
                                <x-dynamic-component component="lucide-phone" class="w-3.5 h-3.5 text-ash-gray" />
                                <span>{{ $vendor->phone }}</span>
                            @else
                                <span class="text-slate-gray">-</span>
                            @endif
                        </div>
                    </td>

                    {{-- Alamat --}}
                    <td class="px-6 py-4">
                        <p class="text-body-sm text-slate-gray leading-snug line-clamp-1 max-w-xs">
                            {{ $vendor->address ?: '-' }}
                        </p>
                    </td>

                    {{-- Aksi --}}
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            @can('finance.vendor.manage')
                                <a href="{{ route('finance.vendors.edit', $vendor) }}"
                                   class="p-1.5 rounded-input text-slate-gray hover:text-primary hover:bg-mist-gray transition"
                                   title="Edit Data Vendor">
                                    <x-dynamic-component component="lucide-pencil" class="w-4 h-4" />
                                </a>

                                <button type="button"
                                        @click="openDeleteModal('{{ route('finance.vendors.destroy', $vendor) }}', '{{ addslashes($vendor->name) }}')"
                                        class="p-1.5 rounded-input text-danger hover:bg-danger-bg transition"
                                        title="Hapus Vendor">
                                    <x-dynamic-component component="lucide-trash-2" class="w-4 h-4" />
                                </button>
                            @endcan
                        </div>
                    </td>
                </tr>
            @endforeach

            <x-slot name="pagination">
                {{ $vendors->links() }}
            </x-slot>
        </x-data-table>

        {{-- Modal Konfirmasi Hapus Vendor --}}
        <x-modal name="confirm-delete-vendor" maxWidth="md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-danger-bg text-danger flex items-center justify-center shrink-0">
                    <x-dynamic-component component="lucide-alert-triangle" class="w-5 h-5" />
                </div>
                <div>
                    <h3 class="text-heading-sm font-semibold text-ink-black">Hapus Vendor?</h3>
                    <p class="text-caption text-slate-gray mt-0.5">Tindakan ini tidak dapat dibatalkan</p>
                </div>
            </div>

            <p class="text-body-sm text-slate-gray mb-6">
                Apakah Anda yakin ingin menghapus data rekanan vendor <strong class="text-ink-black" x-text="vendorName"></strong>?
            </p>

            <form :action="deleteUrl" method="POST" class="flex items-center justify-end gap-3 pt-4 border-t border-border-gray/60">
                @csrf
                @method('DELETE')

                <x-button variant="secondary" size="sm" type="button" x-on:click="$dispatch('close-modal', 'confirm-delete-vendor')">
                    Batal
                </x-button>

                <x-button variant="danger" size="sm" type="submit" class="bg-danger text-paper-white hover:opacity-90">
                    Ya, Hapus Vendor
                </x-button>
            </form>
        </x-modal>
    </div>
</x-app-layout>
