<x-app-layout>
    <x-slot name="header">
        Bagan Akun (Chart of Accounts)
    </x-slot>

    {{-- Alert Messages --}}
    @if (session('success'))
        <div class="mb-5 rounded-input bg-success-bg border border-success/20 text-success px-4 py-3 text-body-sm flex items-center gap-2">
            <x-dynamic-component component="lucide-check-circle-2" class="w-4 h-4 shrink-0" />
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- COA Tree Table Card --}}
    <x-data-table
        title="Daftar Bagan Akun (COA)"
        subtitle="Struktur hierarki klasifikasi akun aset, liabilitas, ekuitas, pendapatan, dan beban"
        :headers="['Kode Akun', 'Nama Akun', 'Klasifikasi / Tipe', 'Sifat Akun']"
        :empty="$accounts->isEmpty()"
    >
        <x-slot name="emptyState">
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="w-12 h-12 rounded-full bg-primary-tint text-primary flex items-center justify-center mb-3">
                    <x-dynamic-component component="lucide-landmark" class="w-6 h-6" />
                </div>
                <h4 class="text-body-lg font-semibold text-ink-black">Bagan Akun Kosong</h4>
                <p class="text-caption text-slate-gray mt-1 max-w-sm">Belum ada struktur akun keuangan yang terdaftar. Jalankan seeder COA sistem.</p>
            </div>
        </x-slot>

        @foreach ($accounts as $account)
            @include('finance::chart-of-accounts.partials.row', ['account' => $account, 'depth' => 0])
        @endforeach
    </x-data-table>
</x-app-layout>
