{{-- resources/views/documentation.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-heading font-medium text-ink-black dark:text-paper-white">Dokumentasi</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- Kolom Kiri: Penjelasan ERP, Fitur Utama & Kredensial --}}
            <div class="lg:col-span-5 space-y-6 lg:sticky lg:top-6">

                {{-- Card Overview & Fitur --}}
                <div class="bg-paper-white dark:bg-[#111c44] border border-border-gray dark:border-border-gray/10 rounded-card p-6 shadow-subtle space-y-5">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center w-10 h-10 rounded-input bg-primary/10 text-primary shrink-0">
                            <x-dynamic-component component="lucide-book-open" class="w-5 h-5" />
                        </span>
                        <div>
                            <h3 class="text-heading-sm font-semibold text-ink-black dark:text-paper-white">Tentang Sistem ERP</h3>
                            <p class="text-caption text-slate-gray dark:text-ash-gray">Panduan & Arsitektur Operasional</p>
                        </div>
                    </div>

                    <p class="text-body-sm text-slate-gray dark:text-ash-gray leading-relaxed">
                        Sistem ERP ini terintegrasi secara *real-time* lintas operasional bisnis mulai dari Penjualan, Keuangan, Inventaris, hingga SDM.
                    </p>

                    <div class="pt-4 border-t border-border-gray/60 dark:border-border-gray/10 space-y-4">
                        <span class="text-[11px] font-bold text-ash-gray uppercase tracking-wider block">Ringkasan Modul Utama</span>

                        <div class="space-y-3">
                            <div class="flex items-start gap-3">
                                <x-dynamic-component component="lucide-shield-check" class="w-4 h-4 text-primary shrink-0 mt-0.5" />
                                <div>
                                    <p class="text-body-sm font-medium text-ink-black dark:text-paper-white">Identity & Access</p>
                                    <p class="text-caption text-slate-gray dark:text-ash-gray">RBAC granular berbasis permission & audit log perubahan data kritikal.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <x-dynamic-component component="lucide-wallet" class="w-4 h-4 text-primary shrink-0 mt-0.5" />
                                <div>
                                    <p class="text-body-sm font-medium text-ink-black dark:text-paper-white">Finance & Accounting</p>
                                    <p class="text-caption text-slate-gray dark:text-ash-gray">Journal Entry balance check (`debit = credit`), AP/AR, Laba Rugi & Neraca otomatis.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <x-dynamic-component component="lucide-shopping-cart" class="w-4 h-4 text-primary shrink-0 mt-0.5" />
                                <div>
                                    <p class="text-body-sm font-medium text-ink-black dark:text-paper-white">Sales & Inventory</p>
                                    <p class="text-caption text-slate-gray dark:text-ash-gray">Katalog campuran barang/jasa, reservasi stok otomatis, serta integrasi jurnal HPP/Pendapatan.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <x-dynamic-component component="lucide-users" class="w-4 h-4 text-primary shrink-0 mt-0.5" />
                                <div>
                                    <p class="text-body-sm font-medium text-ink-black dark:text-paper-white">HR & Payroll</p>
                                    <p class="text-caption text-slate-gray dark:text-ash-gray">Prorate absen, perhitungan PPh21 TER (PMK 168/2023), BPJS, dan jurnal payroll agregat.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <x-dynamic-component component="lucide-layout-dashboard" class="w-4 h-4 text-primary shrink-0 mt-0.5" />
                                <div>
                                    <p class="text-body-sm font-medium text-ink-black dark:text-paper-white">Dashboard Analytics</p>
                                    <p class="text-caption text-slate-gray dark:text-ash-gray">Visualisasi data kontekstual yang bergerak dinamis mengikuti hak akses user.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card Demo Kredensial --}}
                <div class="bg-paper-white dark:bg-[#111c44] border border-border-gray dark:border-border-gray/10 rounded-card p-6 shadow-subtle space-y-4">
                    <div class="flex items-center gap-2">
                        <x-dynamic-component component="lucide-key-round" class="w-4 h-4 text-primary" />
                        <h4 class="text-body-sm font-bold text-ink-black dark:text-paper-white uppercase tracking-wider">Demo Kredensial & Role</h4>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-border-gray/60 dark:border-border-gray/10 text-[10px] font-bold text-ash-gray uppercase">
                                    <th class="pb-2">Role</th>
                                    <th class="pb-2">Cakupan Akses</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-gray/50 dark:divide-border-gray/10 text-caption">
                                <tr>
                                    <td class="py-2.5 font-medium text-ink-black dark:text-paper-white">Sales Staff</td>
                                    <td class="py-2.5 text-slate-gray dark:text-ash-gray">Item, Customer, Sales Order</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 font-medium text-ink-black dark:text-paper-white">Finance Staff</td>
                                    <td class="py-2.5 text-slate-gray dark:text-ash-gray">CoA, Journal Entry, Vendor, Invoice (Tanpa void)</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 font-medium text-ink-black dark:text-paper-white">Finance Manager</td>
                                    <td class="py-2.5 text-slate-gray dark:text-ash-gray">Akses Finance Staff + Void Journal Entry</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 font-medium text-ink-black dark:text-paper-white">HR Staff</td>
                                    <td class="py-2.5 text-slate-gray dark:text-ash-gray">Employee, Attendance (Tanpa proses payroll)</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 font-medium text-ink-black dark:text-paper-white">HR Manager</td>
                                    <td class="py-2.5 text-slate-gray dark:text-ash-gray">Akses HR Staff + Proses Payroll</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 font-medium text-ink-black dark:text-paper-white">Admin</td>
                                    <td class="py-2.5 text-slate-gray dark:text-ash-gray">Akses Penuh Finance + Sales + HR (Tanpa Identity)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-[11px] text-slate-gray dark:text-ash-gray italic pt-2 border-t border-border-gray/60 dark:border-border-gray/10">
                        * Kredensial Super Admin (Akses penuh termasuk User & Role Management) tersedia terpisah.
                    </p>
                </div>

            </div>

            {{-- Kolom Kanan: Collapsible Components --}}
            <div class="lg:col-span-7 space-y-4" x-data="{ openSection: null, openItem: null }">
                @forelse ($sections as $sIndex => $section)
                    <div class="bg-paper-white dark:bg-[#111c44] border border-border-gray dark:border-border-gray/10 rounded-card shadow-subtle overflow-hidden">
                        <button
                            type="button"
                            @click="openSection = (openSection === {{ $sIndex }} ? null : {{ $sIndex }})"
                            class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-mist-gray dark:hover:bg-paper-white/5 transition-colors"
                        >
                            <span class="flex items-center gap-3">
                                <span class="flex items-center justify-center w-8 h-8 rounded-input bg-primary/10 text-primary shrink-0">
                                    <x-dynamic-component :component="'lucide-' . $section['icon']" class="w-4 h-4" />
                                </span>
                                <span class="text-heading-sm font-medium text-ink-black dark:text-paper-white">{{ $section['title'] }}</span>
                            </span>
                            <x-dynamic-component
                                component="lucide-chevron-down"
                                class="w-4 h-4 text-slate-gray dark:text-ash-gray transition-transform shrink-0"
                                x-bind:class="openSection === {{ $sIndex }} ? 'rotate-180' : ''"
                            />
                        </button>

                        <div x-show="openSection === {{ $sIndex }}" x-collapse class="border-t border-border-gray dark:border-border-gray/10">
                            @foreach ($section['items'] as $iIndex => $item)
                                @php $itemKey = $sIndex . '-' . $iIndex; @endphp
                                <div class="border-b border-border-gray/60 dark:border-border-gray/10 last:border-b-0">
                                    <button
                                        type="button"
                                        @click="openItem = (openItem === '{{ $itemKey }}' ? null : '{{ $itemKey }}')"
                                        class="w-full flex items-center justify-between gap-3 px-6 py-3.5 text-left hover:bg-fog-white dark:hover:bg-paper-white/5 transition-colors"
                                    >
                                        <span class="text-body-sm font-medium text-ink-black dark:text-paper-white">{{ $item['question'] }}</span>
                                        <x-dynamic-component
                                            component="lucide-plus"
                                            class="w-3.5 h-3.5 text-slate-gray dark:text-ash-gray shrink-0 transition-transform"
                                            x-bind:class="openItem === '{{ $itemKey }}' ? 'rotate-45' : ''"
                                        />
                                    </button>
                                    <div x-show="openItem === '{{ $itemKey }}'" x-collapse class="px-6 pb-4">
                                        <p class="text-body-sm text-slate-gray dark:text-ash-gray leading-relaxed">{{ $item['answer'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="bg-paper-white dark:bg-[#111c44] border border-border-gray dark:border-border-gray/10 rounded-card p-8 shadow-subtle text-center">
                        <p class="text-body-sm text-slate-gray dark:text-ash-gray">Belum ada dokumentasi yang tersedia untuk hak akses Anda.</p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
