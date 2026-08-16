{{-- resources/views/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-heading font-medium text-ink-black">Dashboard</h2>
    </x-slot>


    <div class="max-w-full mx-auto space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stat-card
                label="Revenue Bulan Ini"
                value="Rp {{ number_format((float) $monthlyRevenue['amount'], 0, ',', '.') }}"
                :sublabel="$monthlyRevenue['period_label']"
            />
            <x-stat-card
                label="Outstanding Invoice"
                value="Rp {{ number_format((float) $outstandingInvoice['total_amount'], 0, ',', '.') }}"
                sublabel="{{ $outstandingInvoice['count'] }} invoice belum dibayar"
            />
            <x-stat-card
                label="Employee Aktif"
                :value="$activeEmployeeCount"
                sublabel="Karyawan berstatus active"
            />
            <x-stat-card
                label="Item Stock Rendah"
                :value="$lowStockItems->count()"
                sublabel="Ambang batas: {{ config('erp.low_stock_threshold', 5) }} unit"
            />
        </div>

        @if($lowStockItems->isNotEmpty())
            <div>
                <x-data-table :headers="['SKU', 'Nama Item', 'On Hand', 'Reserved', 'Available', 'Status']" :empty="false">
                    @foreach ($lowStockItems as $item)
                        @php $available = $item->quantity_on_hand - $item->quantity_reserved; @endphp
                        <tr class="hover:bg-mist-gray">
                            <td class="px-4 py-3 text-body-sm text-slate-gray">{{ $item->sku }}</td>
                            <td class="px-4 py-3 text-body">{{ $item->name }}</td>
                            <td class="px-4 py-3 text-body tabular-nums">{{ number_format($item->quantity_on_hand, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-body tabular-nums">{{ number_format($item->quantity_reserved, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-body-lg tabular-nums font-medium">{{ number_format($available, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <x-badge :status="$available <= 0 ? 'danger' : 'warning'">
                                    {{ $available <= 0 ? 'Habis' : 'Rendah' }}
                                </x-badge>
                            </td>
                        </tr>
                    @endforeach
                </x-data-table>
            </div>
        @endif

        @if($charts['sales'] || $charts['finance'] || $charts['hr'])
            <div class="grid grid-cols-2 lg:grid-cols-8 gap-4">
                @if($charts['sales'])
                    <div class="bg-paper-white border border-border-gray rounded-card p-4 shadow-subtle col-span-2 lg:col-span-4">
                        <h3 class="text-ink-black mb-3">Tren Penjualan: {{ $charts['sales']['monthly_trend']['current_year_label'] }} vs {{ $charts['sales']['monthly_trend']['previous_year_label'] }}</h3>
                        <canvas id="chart-sales-trend" height="150"></canvas>
                    </div>
                    <div class="bg-paper-white border border-border-gray rounded-card p-4 shadow-subtle col-span-1 lg:col-span-2">
                        <h3 class="text-ink-black mb-3">Produk &amp; Jasa Terlaris</h3>
                        <canvas id="chart-sales-items" height="150"></canvas>
                    </div>
                    <div class="bg-paper-white border border-border-gray rounded-card p-4 shadow-subtle col-span-1 lg:col-span-2">
                        <h3 class="text-ink-black mb-3">Distribusi Status Sales Order</h3>
                        <canvas id="chart-sales-status" height="150"></canvas>
                    </div>
                @endif

                @if($charts['finance'])
                    <div class="bg-paper-white border border-border-gray rounded-card p-4 shadow-subtle col-span-1 lg:col-span-2">
                        <h3 class="text-ink-black mb-3">Umur Piutang Belum Dibayar</h3>
                        <canvas id="chart-finance-aging" height="150"></canvas>
                    </div>
                    <div class="bg-paper-white border border-border-gray rounded-card p-4 shadow-subtle col-span-1 lg:col-span-2">
                        <h3 class="text-ink-black mb-3">Distribusi Metode Pembayaran</h3>
                        <canvas id="chart-finance-payment-method" height="150"></canvas>
                    </div>
                    <div class="bg-paper-white border border-border-gray rounded-card p-4 shadow-subtle col-span-2 lg:col-span-4">
                        <h3 class="text-ink-black mb-3">Pendapatan vs Beban (6 Bulan Terakhir)</h3>
                        <canvas id="chart-finance-revexp" height="150"></canvas>
                    </div>
                @endif

                @if($charts['hr'])
                    <div class="bg-paper-white border border-border-gray rounded-card p-4 shadow-subtle col-span-2 lg:col-span-4">
                        <h3 class="text-ink-black mb-3">Headcount per Department</h3>
                        <canvas id="chart-hr-headcount" height="150"></canvas>
                    </div>
                    @if($charts['hr']['attendance_breakdown'])
                        <div class="bg-paper-white border border-border-gray rounded-card p-4 shadow-subtle col-span-1 lg:col-span-2">
                            <h3 class="text-ink-black mb-3">
                                Status Kehadiran - {{ $charts['hr']['attendance_breakdown']['period_label'] }}
                            </h3>
                            <canvas id="chart-hr-attendance" height="300"></canvas>
                        </div>
                    @endif
                    @if($charts['hr']['payroll_cost_breakdown'])
                        <div class="bg-paper-white border border-border-gray rounded-card p-4 shadow-subtle col-span-1 lg:col-span-2">
                            <h3 class="text-ink-black mb-3">
                                Komposisi Biaya Payroll - {{ $charts['hr']['payroll_cost_breakdown']['period_label'] }}
                            </h3>
                            <canvas id="chart-hr-payroll" height="150"></canvas>
                        </div>
                    @endif
                @endif
            </div>
        @endif
    </div>

    @if($charts['sales'] || $charts['finance'] || $charts['hr'])
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const palette = ['#17191c', '#777b86', '#979799', '#e5e5e7', '#fbe1d1', '#5d2a1a'];

        @if($charts['sales'])
        new Chart(document.getElementById('chart-sales-trend'), {
            type: 'line',
            data: {
                labels: @json($charts['sales']['monthly_trend']['labels']),
                datasets: [
                    {
                        label: 'Revenue {{ $charts['sales']['monthly_trend']['current_year_label'] }}',
                        data: @json($charts['sales']['monthly_trend']['current_year_values']),
                        borderColor: palette[0],
                        backgroundColor: 'rgba(23, 25, 28, 0.08)',
                        fill: true,
                        tension: 0.3,
                        pointStyle: 'circle',
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        pointBackgroundColor: palette[0],
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                    },
                    {
                        label: 'Revenue {{ $charts['sales']['monthly_trend']['previous_year_label'] }}',
                        data: @json($charts['sales']['monthly_trend']['previous_year_values']),
                        borderColor: palette[1],
                        backgroundColor: 'transparent',
                        borderDash: [6, 4],
                        fill: false,
                        tension: 0.3,
                        pointStyle: 'circle',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: palette[1],
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                    },
                ],
            },
            options: {
                plugins: { legend: { display: true, position: 'top', align: 'end' } },
                scales: { y: { beginAtZero: true } },
            },
        });

        new Chart(document.getElementById('chart-sales-items'), {
            type: 'pie',
            data: {
                labels: @json($charts['sales']['item_breakdown']['labels']),
                datasets: [{ data: @json($charts['sales']['item_breakdown']['values']), backgroundColor: palette }],
            },
        });

        new Chart(document.getElementById('chart-sales-status'), {
            type: 'doughnut',
            data: {
                labels: @json($charts['sales']['status_distribution']['labels']),
                datasets: [{ data: @json($charts['sales']['status_distribution']['values']), backgroundColor: palette }],
            },
        });
        @endif

        @if($charts['finance'])
        new Chart(document.getElementById('chart-finance-revexp'), {
            type: 'bar',
            data: {
                labels: @json($charts['finance']['revenue_expense']['labels']),
                datasets: [
                    { label: 'Pendapatan', data: @json($charts['finance']['revenue_expense']['revenue']), backgroundColor: palette[0] },
                    { label: 'Beban', data: @json($charts['finance']['revenue_expense']['expense']), backgroundColor: palette[5] },
                ],
            },
            options: { scales: { y: { beginAtZero: true } } },
        });

        new Chart(document.getElementById('chart-finance-aging'), {
            type: 'doughnut',
            data: {
                labels: @json($charts['finance']['invoice_aging']['labels']),
                datasets: [{ data: @json($charts['finance']['invoice_aging']['values']), backgroundColor: palette }],
            },
        });

        new Chart(document.getElementById('chart-finance-payment-method'), {
            type: 'pie',
            data: {
                labels: @json($charts['finance']['payment_method_distribution']['labels']),
                datasets: [{ data: @json($charts['finance']['payment_method_distribution']['values']), backgroundColor: palette }],
            },
        });
        @endif

        @if($charts['hr'])
        new Chart(document.getElementById('chart-hr-headcount'), {
            type: 'bar',
            data: {
                labels: @json($charts['hr']['headcount_by_department']['labels']),
                datasets: [{ label: 'Jumlah Karyawan', data: @json($charts['hr']['headcount_by_department']['values']), backgroundColor: palette[0] }],
            },
            options: { indexAxis: 'y', plugins: { legend: { display: false } } },
        });

        @if($charts['hr']['attendance_breakdown'])
        new Chart(document.getElementById('chart-hr-attendance'), {
            type: 'bar',
            data: {
                labels: @json($charts['hr']['attendance_breakdown']['labels']),
                datasets: [{ label: 'Jumlah Hari', data: @json($charts['hr']['attendance_breakdown']['values']), backgroundColor: palette }],
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } },
        });
        @endif

        @if($charts['hr']['payroll_cost_breakdown'])
        new Chart(document.getElementById('chart-hr-payroll'), {
            type: 'pie',
            data: {
                labels: @json($charts['hr']['payroll_cost_breakdown']['labels']),
                datasets: [{ data: @json($charts['hr']['payroll_cost_breakdown']['values']), backgroundColor: palette }],
            },
        });
        @endif
        @endif
    });
    </script>
    @endif
</x-app-layout>
