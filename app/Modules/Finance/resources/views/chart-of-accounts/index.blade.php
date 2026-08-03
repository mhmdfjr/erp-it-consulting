<x-app-layout>
    <x-slot name="header">
        <h1 class="text-heading font-medium text-ink-black">Chart of Accounts</h1>
    </x-slot>

    <div class="bg-paper-white border border-border-gray rounded-card shadow-subtle overflow-hidden">
        <table class="w-full text-body">
            <thead class="bg-fog-white text-label text-slate-gray">
                <tr>
                    <th class="text-left px-4 py-3">Kode</th>
                    <th class="text-left px-4 py-3">Nama Akun</th>
                    <th class="text-left px-4 py-3">Tipe</th>
                    <th class="text-left px-4 py-3">Postable</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($accounts as $account)
                    @include('finance::chart-of-accounts.partials.row', ['account' => $account, 'depth' => 0])
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>
