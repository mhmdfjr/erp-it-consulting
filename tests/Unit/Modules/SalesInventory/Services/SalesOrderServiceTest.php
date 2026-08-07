<?php

namespace Tests\Unit\Modules\SalesInventory\Services;

use App\Modules\SalesInventory\Events\SalesOrderCompleted;
use App\Modules\SalesInventory\Exceptions\CancelReasonRequiredException;
use App\Modules\SalesInventory\Exceptions\OrderNotCancellableException;
use App\Modules\SalesInventory\Models\Customer;
use App\Modules\SalesInventory\Models\Item;
use App\Modules\SalesInventory\Models\StockLevel;
use App\Modules\SalesInventory\Services\InventoryService;
use App\Modules\SalesInventory\Services\SalesOrderService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SalesOrderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SalesOrderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();

        $this->actingAs(User::factory()->create());
        $this->service = new SalesOrderService(new InventoryService());
    }

    public function test_cancel_order_melepas_reservasi_dengan_benar(): void
    {
        $item = Item::factory()->create(['item_type' => 'physical_good']);
        StockLevel::create(['item_id' => $item->id, 'quantity_on_hand' => 10, 'quantity_reserved' => 0]);
        $customer = Customer::factory()->create();

        $order = $this->service->createOrder([
            'customer_id' => $customer->id,
            'order_date' => now(),
            'items' => [
                ['item_id' => $item->id, 'quantity' => 4, 'unit_price' => 100000],
            ],
        ]);

        $this->assertEquals(4, $item->stockLevel->fresh()->quantity_reserved);

        $cancelled = $this->service->cancelOrder($order, 'Customer batal, budget dipangkas.');

        $this->assertEquals('cancelled', $cancelled->status);
        $this->assertEquals('Customer batal, budget dipangkas.', $cancelled->cancel_reason);
        $this->assertEquals(0, $item->stockLevel->fresh()->quantity_reserved);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_cancel_order_ditolak_kalau_status_sudah_completed(): void
    {
        $item = Item::factory()->create(['item_type' => 'physical_good']);
        StockLevel::create(['item_id' => $item->id, 'quantity_on_hand' => 10, 'quantity_reserved' => 0]);
        $customer = Customer::factory()->create();

        $order = $this->service->createOrder([
            'customer_id' => $customer->id,
            'order_date' => now(),
            'items' => [
                ['item_id' => $item->id, 'quantity' => 2, 'unit_price' => 100000],
            ],
        ]);

        $this->service->completeOrder($order->fresh());

        $this->expectException(OrderNotCancellableException::class);
        $this->service->cancelOrder($order->fresh(), 'Coba batalkan setelah completed.');
    }

    public function test_cancel_order_ditolak_kalau_reason_kosong(): void
    {
        $customer = Customer::factory()->create();
        $item = Item::factory()->create(['item_type' => 'service']);

        $order = $this->service->createOrder([
            'customer_id' => $customer->id,
            'order_date' => now(),
            'items' => [
                ['item_id' => $item->id, 'quantity' => 1, 'unit_price' => 5000000],
            ],
        ]);

        $this->expectException(CancelReasonRequiredException::class);
        $this->service->cancelOrder($order, '');
    }

    public function test_complete_order_menghasilkan_invoice_dengan_format_dan_amount_benar(): void
    {
        $customer = Customer::factory()->create();
        $item = Item::factory()->create(['item_type' => 'service']);

        $order = $this->service->createOrder([
            'customer_id' => $customer->id,
            'order_date' => now(),
            'items' => [
                ['item_id' => $item->id, 'quantity' => 1, 'unit_price' => 15000000],
            ],
        ]);

        $completed = $this->service->completeOrder($order);

        $this->assertEquals('completed', $completed->status);
        $this->assertNotNull($completed->invoice);
        $this->assertMatchesRegularExpression('/^INV-\d{4}-\d{6}$/', $completed->invoice->invoice_number);
        $this->assertEquals(15000000, $completed->invoice->amount);
        $this->assertEquals('unpaid', $completed->invoice->status);
    }
}
