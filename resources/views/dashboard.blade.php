{{-- resources/views/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-heading-sm font-semibold text-ink-black tracking-tight">Dashboard Overview</h2>
    </x-slot>

    <div class="max-w-full mx-auto space-y-6">
        {{-- Top Stat Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stat-card
                label="Revenue Bulan Ini"
                value="Rp {{ number_format((float) $monthlyRevenue['amount'], 0, ',', '.') }}"
                :sublabel="$monthlyRevenue['period_label']"
                icon="wallet"
            />
            <x-stat-card
                label="Outstanding Invoice"
                value="Rp {{ number_format((float) $outstandingInvoice['total_amount'], 0, ',', '.') }}"
                sublabel="{{ $outstandingInvoice['count'] }} invoice belum dibayar"
                icon="file-text"
            />
            <x-stat-card
                label="Employee Aktif"
                :value="$activeEmployeeCount"
                sublabel="Karyawan berstatus active"
                icon="users"
            />
            <x-stat-card
                label="Item Stock Rendah"
                :value="$lowStockItems->count()"
                sublabel="Ambang batas: {{ config('erp.low_stock_threshold', 5) }} unit"
                icon="alert-triangle"
            />
        </div>

        {{-- Low Stock Table Alert --}}
        @if($lowStockItems->isNotEmpty())
            <div>
                <x-data-table
                    title="Peringatan Stok Rendah"
                    subtitle="Daftar item inventaris yang mendekati atau melewati batas minimum kuantitas"
                    :headers="['SKU', 'Nama Item', 'On Hand', 'Reserved', 'Available', 'Status']"
                    :empty="false"
                >
                    @foreach ($lowStockItems as $item)
                        @php $available = $item->quantity_on_hand - $item->quantity_reserved; @endphp
                        <tr class="hover:bg-mist-gray/40 transition-colors">
                            <td class="px-6 py-4 text-caption font-semibold text-slate-gray">{{ $item->sku }}</td>
                            <td class="px-6 py-4 text-body-sm font-medium text-ink-black">{{ $item->name }}</td>
                            <td class="px-6 py-4 text-body-sm tabular-nums text-slate-gray">{{ number_format($item->quantity_on_hand, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-body-sm tabular-nums text-slate-gray">{{ number_format($item->quantity_reserved, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-body-sm tabular-nums font-bold text-ink-black">{{ number_format($available, 0, ',', '.') }}</td>
                            <td class="px-6 py-4">
                                <x-badge :status="$available <= 0 ? 'danger' : 'warning'" variant="solid">
                                    {{ $available <= 0 ? 'Habis' : 'Rendah' }}
                                </x-badge>
                            </td>
                        </tr>
                    @endforeach
                </x-data-table>
            </div>
        @endif

        {{-- Analytics & Charts Grid --}}
        @if($charts['sales'] || $charts['finance'] || $charts['hr'])
            <div class="grid grid-cols-2 lg:grid-cols-8 gap-4">
                @if($charts['sales'])
                    <div class="bg-paper-white border border-border-gray/80 rounded-card p-5 shadow-subtle col-span-2 lg:col-span-4 flex flex-col justify-between">
                        <div class="mb-4">
                            <h3 class="text-body-sm font-bold text-ink-black tracking-tight">Tren Penjualan</h3>
                            <p class="text-caption text-slate-gray mt-0.5">{{ $charts['sales']['monthly_trend']['current_year_label'] }} vs {{ $charts['sales']['monthly_trend']['previous_year_label'] }}</p>
                        </div>
                        <canvas id="chart-sales-trend" height="300"></canvas>
                    </div>
                    <div class="bg-paper-white border border-border-gray/80 rounded-card p-5 shadow-subtle col-span-1 lg:col-span-2 flex flex-col justify-between">
                        <div class="mb-4">
                            <h3 class="text-body-sm font-bold text-ink-black tracking-tight">Produk &amp; Jasa Terlaris</h3>
                            <p class="text-caption text-slate-gray mt-0.5">Berdasarkan volume transaksi</p>
                        </div>
                        <canvas id="chart-sales-items" height="150"></canvas>
                    </div>
                    <div class="bg-paper-white border border-border-gray/80 rounded-card p-5 shadow-subtle col-span-1 lg:col-span-2 flex flex-col justify-between">
                        <div class="mb-4">
                            <h3 class="text-body-sm font-bold text-ink-black tracking-tight">Status Sales Order</h3>
                            <p class="text-caption text-slate-gray mt-0.5">Distribusi pemenuhan order</p>
                        </div>
                        <canvas id="chart-sales-status" height="150"></canvas>
                    </div>
                @endif

                @if($charts['finance'])
                    <div class="bg-paper-white border border-border-gray/80 rounded-card p-5 shadow-subtle col-span-1 lg:col-span-2 flex flex-col justify-between">
                        <div class="mb-4">
                            <h3 class="text-body-sm font-bold text-ink-black tracking-tight">Umur Piutang</h3>
                            <p class="text-caption text-slate-gray mt-0.5">Invoice belum dibayar</p>
                        </div>
                        <canvas id="chart-finance-aging" height="150"></canvas>
                    </div>
                    <div class="bg-paper-white border border-border-gray/80 rounded-card p-5 shadow-subtle col-span-1 lg:col-span-2 flex flex-col justify-between">
                        <div class="mb-4">
                            <h3 class="text-body-sm font-bold text-ink-black tracking-tight">Metode Pembayaran</h3>
                            <p class="text-caption text-slate-gray mt-0.5">Channel penerimaan kas</p>
                        </div>
                        <canvas id="chart-finance-payment-method" height="150"></canvas>
                    </div>
                    <div class="bg-paper-white border border-border-gray/80 rounded-card p-5 shadow-subtle col-span-2 lg:col-span-4 flex flex-col justify-between">
                        <div class="mb-4">
                            <h3 class="text-body-sm font-bold text-ink-black tracking-tight">Pendapatan vs Beban</h3>
                            <p class="text-caption text-slate-gray mt-0.5">Kinerja operasional 6 bulan terakhir</p>
                        </div>
                        <canvas id="chart-finance-revexp" height="300"></canvas>
                    </div>
                @endif

                @if($charts['hr'])
                    <div class="bg-paper-white border border-border-gray/80 rounded-card p-5 shadow-subtle col-span-2 lg:col-span-4 flex flex-col justify-between">
                        <div class="mb-4">
                            <h3 class="text-body-sm font-bold text-ink-black tracking-tight">Headcount per Department</h3>
                            <p class="text-caption text-slate-gray mt-0.5">Distribusi divisi operasional</p>
                        </div>
                        <canvas id="chart-hr-headcount" height="300"></canvas>
                    </div>
                    @if($charts['hr']['attendance_breakdown'])
                        <div class="bg-paper-white border border-border-gray/80 rounded-card p-5 shadow-subtle col-span-1 lg:col-span-2 flex flex-col justify-between">
                            <div class="mb-4">
                                <h3 class="text-body-sm font-bold text-ink-black tracking-tight">Status Kehadiran</h3>
                                <p class="text-caption text-slate-gray mt-0.5">{{ $charts['hr']['attendance_breakdown']['period_label'] }}</p>
                            </div>
                            <canvas id="chart-hr-attendance" height="300"></canvas>
                        </div>
                    @endif
                    @if($charts['hr']['payroll_cost_breakdown'])
                        <div class="bg-paper-white border border-border-gray/80 rounded-card p-5 shadow-subtle col-span-1 lg:col-span-2 flex flex-col justify-between">
                            <div class="mb-4">
                                <h3 class="text-body-sm font-bold text-ink-black tracking-tight">Komposisi Payroll</h3>
                                <p class="text-caption text-slate-gray mt-0.5">{{ $charts['hr']['payroll_cost_breakdown']['period_label'] }}</p>
                            </div>
                            <canvas id="chart-hr-payroll" height="150"></canvas>
                        </div>
                    @endif
                @endif
            </div>
        @endif
    </div>

    {{-- ChartJS Configuration --}}
    @if($charts['sales'] || $charts['finance'] || $charts['hr'])
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Color Hunt Palette: Primary (#8100d1), Secondary (#b500b2), Accent Pink (#ff52a0), Accent Peach (#ffa47f)
        const brandPalette = ['#8100d1', '#b500b2', '#ff52a0', '#ffa47f', '#6f6b7d', '#e7e5ed'];

        @if($charts['sales'])
        new Chart(document.getElementById('chart-sales-trend'), {
            type: 'line',
            data: {
                labels: @json($charts['sales']['monthly_trend']['labels']),
                datasets: [
                    {
                        label: 'Revenue {{ $charts['sales']['monthly_trend']['current_year_label'] }}',
                        data: @json($charts['sales']['monthly_trend']['current_year_values']),
                        borderColor: '#8100d1',
                        backgroundColor: 'rgba(129, 0, 209, 0.12)',
                        fill: true,
                        tension: 0.4,
                        pointStyle: 'circle',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#8100d1',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                    },
                    {
                        label: 'Revenue {{ $charts['sales']['monthly_trend']['previous_year_label'] }}',
                        data: @json($charts['sales']['monthly_trend']['previous_year_values']),
                        borderColor: '#6f6b7d',
                        backgroundColor: 'transparent',
                        borderDash: [5, 5],
                        fill: false,
                        tension: 0.4,
                        pointStyle: 'circle',
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#6f6b7d',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: true, position: 'top', align: 'end', labels: { boxWidth: 12, font: { size: 11, family: 'Inter' } } }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f4f3f7' } },
                    x: { grid: { display: false } }
                },
            },
        });

        new Chart(document.getElementById('chart-sales-items'), {
            type: 'pie',
            data: {
                labels: @json($charts['sales']['item_breakdown']['labels']),
                datasets: [{ data: @json($charts['sales']['item_breakdown']['values']), backgroundColor: brandPalette }],
            },
            options: {
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10, family: 'Inter' } } } }
            }
        });

        new Chart(document.getElementById('chart-sales-status'), {
            type: 'doughnut',
            data: {
                labels: @json($charts['sales']['status_distribution']['labels']),
                datasets: [{ data: @json($charts['sales']['status_distribution']['values']), backgroundColor: brandPalette }],
            },
            options: {
                cutout: '70%',
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10, family: 'Inter' } } } }
            }
        });
        @endif

        @if($charts['finance'])
        new Chart(document.getElementById('chart-finance-revexp'), {
            type: 'bar',
            data: {
                labels: @json($charts['finance']['revenue_expense']['labels']),
                datasets: [
                    { label: 'Pendapatan', data: @json($charts['finance']['revenue_expense']['revenue']), backgroundColor: '#8100d1', borderRadius: 6 },
                    { label: 'Beban', data: @json($charts['finance']['revenue_expense']['expense']), backgroundColor: '#ffa47f', borderRadius: 6 },
                ],
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'top', align: 'end', labels: { boxWidth: 12, font: { size: 11, family: 'Inter' } } } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f4f3f7' } },
                    x: { grid: { display: false } }
                }
            },
        });

        new Chart(document.getElementById('chart-finance-aging'), {
            type: 'doughnut',
            data: {
                labels: @json($charts['finance']['invoice_aging']['labels']),
                datasets: [{ data: @json($charts['finance']['invoice_aging']['values']), backgroundColor: brandPalette }],
            },
            options: {
                cutout: '70%',
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10, family: 'Inter' } } } }
            }
        });

        new Chart(document.getElementById('chart-finance-payment-method'), {
            type: 'pie',
            data: {
                labels: @json($charts['finance']['payment_method_distribution']['labels']),
                datasets: [{ data: @json($charts['finance']['payment_method_distribution']['values']), backgroundColor: brandPalette }],
            },
            options: {
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10, family: 'Inter' } } } }
            }
        });
        @endif

        @if($charts['hr'])
        new Chart(document.getElementById('chart-hr-headcount'), {
            type: 'bar',
            data: {
                labels: @json($charts['hr']['headcount_by_department']['labels']),
                datasets: [{ label: 'Jumlah Karyawan', data: @json($charts['hr']['headcount_by_department']['values']), backgroundColor: '#b500b2', borderRadius: 6 }],
            },
            options: {
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, grid: { color: '#f4f3f7' } },
                    y: { grid: { display: false } }
                }
            },
        });

        @if($charts['hr']['attendance_breakdown'])
        new Chart(document.getElementById('chart-hr-attendance'), {
            type: 'bar',
            data: {
                labels: @json($charts['hr']['attendance_breakdown']['labels']),
                datasets: [{ label: 'Jumlah Hari', data: @json($charts['hr']['attendance_breakdown']['values']), backgroundColor: brandPalette, borderRadius: 6 }],
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f4f3f7' } },
                    x: { grid: { display: false } }
                }
            },
        });
        @endif

        @if($charts['hr']['payroll_cost_breakdown'])
        new Chart(document.getElementById('chart-hr-payroll'), {
            type: 'pie',
            data: {
                labels: @json($charts['hr']['payroll_cost_breakdown']['labels']),
                datasets: [{ data: @json($charts['hr']['payroll_cost_breakdown']['values']), backgroundColor: brandPalette }],
            },
            options: {
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10, family: 'Inter' } } } }
            }
        });
        @endif
        @endif
    });
    </script>
    @endif
</x-app-layout>
