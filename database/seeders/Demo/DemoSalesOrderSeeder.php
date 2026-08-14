<?php
// database/seeders/Demo/DemoSalesOrderSeeder.php
// (revisi: buildOrderPayload dan seluruh baris items menyertakan unit_price eksplisit)

namespace Database\Seeders\Demo;

use App\Models\User;
use App\Modules\SalesInventory\Models\Customer;
use App\Modules\SalesInventory\Models\Item;
use App\Modules\SalesInventory\Services\SalesOrderService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class DemoSalesOrderSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            abort(403, 'DemoSalesOrderSeeder tidak boleh dijalankan di production.');
        }

        $previousQueueDefault = config('queue.default');
        config(['queue.default' => 'sync']);

        try {
            $this->seedOrders();
        } finally {
            config(['queue.default' => $previousQueueDefault]);
        }

        $this->command->info('Demo Sales Order selesai.');
    }

    private function seedOrders(): void
    {
        $service = app(SalesOrderService::class);
        $salesUsers = User::whereIn('email', [
            'dewi.sales@test.local',
            'rian.sales@test.local',
        ])->get()->keyBy('email');

        $customers = Customer::all()->keyBy('name');
        $items = Item::all()->keyBy('sku');

        foreach ($this->orderScenarios($customers, $items) as $scenario) {
            $actor = $salesUsers->get($scenario['created_by_email']) ?? $salesUsers->first();
            Auth::loginUsingId($actor->id);

            $order = $service->createOrder($this->buildOrderPayload($scenario));

            match ($scenario['final_status']) {
                'completed' => $service->completeOrder($order),
                'cancelled' => $service->cancelOrder($order, $scenario['cancel_reason']),
                default => null, // 'draft' -> dibiarkan
            };

            Auth::logout();
        }
    }

    private function buildOrderPayload(array $scenario): array
    {
        return [
            'customer_id' => $scenario['customer']->id,
            'order_date' => $scenario['order_date']->toDateString(),
            'items' => collect($scenario['items'])->map(fn ($line) => [
                'item_id' => $line['item']->id,
                'quantity' => $line['quantity'],
                'unit_price' => $line['item']->unit_price,
            ])->all(),
        ];
    }

    private function orderScenarios($customers, $items): array
    {
        $now = Carbon::now();

        return [
            [
                'customer' => $customers['PT Maju Bersama Sejahtera'],
                'order_date' => $now->copy()->subDays(20),
                'created_by_email' => 'dewi.sales@test.local',
                'items' => [
                    ['item' => $items['HW-LAPTOP-001'], 'quantity' => 2],
                    ['item' => $items['SVC-KONSUL-001'], 'quantity' => 1],
                ],
                'final_status' => 'completed',
            ],
            [
                'customer' => $customers['PT Sinergi Manufaktur'],
                'order_date' => $now->copy()->subDays(18),
                'created_by_email' => 'rian.sales@test.local',
                'items' => [
                    ['item' => $items['SVC-IMPL-001'], 'quantity' => 1],
                ],
                'final_status' => 'completed',
            ],
            [
                'customer' => $customers['PT Cahaya Retail Indonesia'],
                'order_date' => $now->copy()->subDays(15),
                'created_by_email' => 'dewi.sales@test.local',
                'items' => [
                    ['item' => $items['HW-MONITOR-001'], 'quantity' => 5],
                    ['item' => $items['NW-UPS-001'], 'quantity' => 3],
                ],
                'final_status' => 'completed',
            ],
            [
                'customer' => $customers['CV Berkah Logistik'],
                'order_date' => $now->copy()->subDays(12),
                'created_by_email' => 'rian.sales@test.local',
                'items' => [
                    ['item' => $items['SVC-SUPPORT-001'], 'quantity' => 1],
                    ['item' => $items['HW-MONITOR-001'], 'quantity' => 2],
                ],
                'final_status' => 'completed',
            ],
            [
                'customer' => $customers['PT Konstruksi Baja Perkasa'],
                'order_date' => $now->copy()->subDays(10),
                'created_by_email' => 'dewi.sales@test.local',
                'items' => [
                    ['item' => $items['NW-ROUTER-001'], 'quantity' => 2],
                    ['item' => $items['NW-SWITCH-001'], 'quantity' => 2],
                ],
                'final_status' => 'completed',
            ],
            [
                'customer' => $customers['PT Agro Teknologi Nusantara'],
                'order_date' => $now->copy()->subDays(8),
                'created_by_email' => 'rian.sales@test.local',
                'items' => [
                    ['item' => $items['SVC-KONSUL-002'], 'quantity' => 1],
                    ['item' => $items['SVC-TRAIN-001'], 'quantity' => 1],
                ],
                'final_status' => 'completed',
            ],
            [
                'customer' => $customers['PT Griya Sehat Farma'],
                'order_date' => $now->copy()->subDays(5),
                'created_by_email' => 'dewi.sales@test.local',
                'items' => [
                    ['item' => $items['OF-PRINTER-001'], 'quantity' => 2],
                ],
                'final_status' => 'completed',
            ],
            // draft -> belum di-complete, uji tombol Complete/Cancel langsung di UI.
            [
                'customer' => $customers['Yayasan Pendidikan Nusantara'],
                'order_date' => $now->copy()->subDays(2),
                'created_by_email' => 'rian.sales@test.local',
                'items' => [
                    ['item' => $items['SVC-KONSUL-001'], 'quantity' => 1],
                ],
                'final_status' => 'draft',
            ],
            // cancelled -> uji release reservation, stok kembali tersedia.
            [
                'customer' => $customers['Budi Santoso'],
                'order_date' => $now->copy()->subDays(6),
                'created_by_email' => 'dewi.sales@test.local',
                'items' => [
                    ['item' => $items['HW-LAPTOP-001'], 'quantity' => 1],
                ],
                'final_status' => 'cancelled',
                'cancel_reason' => 'Customer membatalkan pesanan, beralih ke vendor lain (data demo).',
            ],
        ];
    }
}
