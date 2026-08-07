<x-app-layout>
    <x-slot name="header">
        <h1 class="text-heading font-medium text-ink-black">Chart of Accounts</h1>
    </x-slot>

    <x-data-table
        :headers="['Kode', 'Nama Akun', 'Tipe', 'Postable']"
        :empty="$accounts->isEmpty()"
    >
        <x-slot name="emptyState">
            <p class="text-body-sm text-slate-gray">Belum ada akun, jalankan seeder Chart of Accounts.</p>
        </x-slot>

        @foreach ($accounts as $account)
            @include('finance::chart-of-accounts.partials.row', ['account' => $account, 'depth' => 0])
        @endforeach
    </x-data-table>
</x-app-layout>
