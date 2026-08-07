<?php

namespace Tests\Feature\Modules\SalesInventory;

use App\Models\User;
use App\Modules\Finance\Models\ChartOfAccount;
use App\Modules\SalesInventory\Models\Customer;
use App\Modules\SalesInventory\Models\Item;
use App\Modules\SalesInventory\Models\StockLevel;
use App\Modules\SalesInventory\Services\SalesOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesOrderCompletionTest extends TestCase
{
    use RefreshDatabase;

    protected SalesOrderService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Listener CreateJournalEntryFromSalesOrder ShouldQueue. Tanpa ini, event masuk antrian dan tidak dieksekusi dalam test.
        //Paksa sync khusus di test supaya listener benar-benar jalan synchronous tanpa perlu worker terpisah.
        config(['queue.default' => 'sync']);

        $this->seedChartOfAccounts();
        $this->actingAs(User::factory()->create());
        $this->service = app(SalesOrderService::class);
    }

    public function test_complete_order_barang_fisik_menghasilkan_stok_berkurang_invoice_dan_journal_entry_benar(): void
    {
        $item = Item::factory()->create([
            'item_type' => 'physical_good',
            'unit_price' => 1000000,
            'cost_price' => 600000,
        ]);
        StockLevel::create(['item_id' => $item->id, 'quantity_on_hand' => 10, 'quantity_reserved' => 0]);
        $customer = Customer::factory()->create();

        $order = $this->service->createOrder([
            'customer_id' => $customer->id,
            'order_date' => now(),
            'items' => [
                ['item_id' => $item->id, 'quantity' => 3, 'unit_price' => 1000000],
            ],
        ]);

        $completed = $this->service->completeOrder($order);

        // Stok berkurang (bukan cuma reservasi dilepas — realisasi keluar).
        $stockLevel = $item->stockLevel()->first();
        $this->assertEquals(7, $stockLevel->quantity_on_hand);
        $this->assertEquals(0, $stockLevel->quantity_reserved);

        // Invoice terbuat sync, tanpa perlu Queue::fake() untuk bagian ini.
        $this->assertNotNull($completed->invoice);
        $this->assertEquals(3000000, $completed->invoice->amount);
        $this->assertMatchesRegularExpression('/^INV-\d{4}-\d{6}$/', $completed->invoice->invoice_number);

        // Journal entry: piutang 3jt, pendapatan barang 3jt, HPP 1.8jt, persediaan 1.8jt.
        $entry = \App\Modules\Finance\Models\JournalEntry::where('reference_type', \App\Modules\SalesInventory\Models\SalesOrder::class)
            ->where('reference_id', $order->id)
            ->first();

        $this->assertNotNull($entry, 'Journal entry tidak ditemukan — cek apakah listener benar-benar jalan sync di test ini.');

        $piutang = $this->accountId('103');
        $pendapatanBarang = $this->accountId('402');
        $hpp = $this->accountId('501');
        $persediaan = $this->accountId('105');

        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $entry->id, 'account_id' => $piutang, 'debit' => 3000000, 'credit' => 0,
        ]);
        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $entry->id, 'account_id' => $pendapatanBarang, 'debit' => 0, 'credit' => 3000000,
        ]);
        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $entry->id, 'account_id' => $hpp, 'debit' => 1800000, 'credit' => 0,
        ]);
        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $entry->id, 'account_id' => $persediaan, 'debit' => 0, 'credit' => 1800000,
        ]);

        // Balance check: total debit = total credit di seluruh baris entry ini.
        $totalDebit = $entry->lines()->sum('debit');
        $totalCredit = $entry->lines()->sum('credit');
        $this->assertEquals($totalDebit, $totalCredit);
    }

    public function test_complete_order_campuran_barang_dan_jasa_mengalokasikan_akun_pendapatan_terpisah(): void
    {
        $physicalItem = Item::factory()->create([
            'item_type' => 'physical_good', 'unit_price' => 2000000, 'cost_price' => 1200000,
        ]);
        StockLevel::create(['item_id' => $physicalItem->id, 'quantity_on_hand' => 5, 'quantity_reserved' => 0]);

        $serviceItem = Item::factory()->create([
            'item_type' => 'service', 'unit_price' => 5000000, 'cost_price' => null,
        ]);

        $customer = Customer::factory()->create();

        $order = $this->service->createOrder([
            'customer_id' => $customer->id,
            'order_date' => now(),
            'items' => [
                ['item_id' => $physicalItem->id, 'quantity' => 1, 'unit_price' => 2000000],
                ['item_id' => $serviceItem->id, 'quantity' => 1, 'unit_price' => 5000000],
            ],
        ]);

        $completed = $this->service->completeOrder($order);

        $entry = \App\Modules\Finance\Models\JournalEntry::where('reference_type', \App\Modules\SalesInventory\Models\SalesOrder::class)
            ->where('reference_id', $order->id)
            ->first();

        $piutang = $this->accountId('103');
        $pendapatanBarang = $this->accountId('402');
        $pendapatanJasa = $this->accountId('401');
        $hpp = $this->accountId('501');
        $persediaan = $this->accountId('105');

        // Piutang gabungan: 2jt + 5jt = 7jt, satu baris, bukan dipecah per item_type
        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $entry->id, 'account_id' => $piutang, 'debit' => 7000000, 'credit' => 0,
        ]);

        // Pendapatan barang dan jasa harus terpisah, bukan tercampur ke satu akun
        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $entry->id, 'account_id' => $pendapatanBarang, 'debit' => 0, 'credit' => 2000000,
        ]);
        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $entry->id, 'account_id' => $pendapatanJasa, 'debit' => 0, 'credit' => 5000000,
        ]);

        // HPP cuma dari item fisik, item jasa tidak menyumbang HPP
        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $entry->id, 'account_id' => $hpp, 'debit' => 1200000, 'credit' => 0,
        ]);
        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $entry->id, 'account_id' => $persediaan, 'debit' => 0, 'credit' => 1200000,
        ]);

        // Total baris cuma 5, pastikan tidak ada baris duplikat atau baris kosong (credit=0 AND debit=0) yang lolos ke journal entry.
        $this->assertEquals(5, $entry->lines()->count());

        $this->assertEquals($entry->lines()->sum('debit'), $entry->lines()->sum('credit'));
    }

    public function test_cancel_order_melepas_reservasi_tanpa_stock_movement_baru(): void
    {
        $item = Item::factory()->create(['item_type' => 'physical_good']);
        StockLevel::create(['item_id' => $item->id, 'quantity_on_hand' => 10, 'quantity_reserved' => 0]);
        $customer = Customer::factory()->create();

        $order = $this->service->createOrder([
            'customer_id' => $customer->id,
            'order_date' => now(),
            'items' => [['item_id' => $item->id, 'quantity' => 4, 'unit_price' => 100000]],
        ]);

        $this->assertEquals(4, $item->stockLevel()->first()->quantity_reserved);
        $movementCountBefore = \App\Modules\SalesInventory\Models\StockMovement::count();

        $cancelled = $this->service->cancelOrder($order, 'Customer batal.');

        $this->assertEquals('cancelled', $cancelled->status);
        $this->assertEquals(0, $item->stockLevel()->first()->quantity_reserved);
        // Reservasi dilepas yanpa insert row stock_movements baru, bukan physical movement
        $this->assertEquals($movementCountBefore, \App\Modules\SalesInventory\Models\StockMovement::count());

        // Order yang sudah completed tidak boleh bisa di-cancel.
        $completedOrder = $this->service->createOrder([
            'customer_id' => $customer->id,
            'order_date' => now(),
            'items' => [['item_id' => $item->id, 'quantity' => 1, 'unit_price' => 100000]],
        ]);
        $this->service->completeOrder($completedOrder->fresh());

        $this->expectException(\App\Modules\SalesInventory\Exceptions\OrderNotCancellableException::class);
        $this->service->cancelOrder($completedOrder->fresh(), 'Coba batalkan setelah completed.');
    }

    protected function accountId(string $code): int
    {
        return ChartOfAccount::where('code', $code)->firstOrFail()->id;
    }

    // Seed minimal akun yang dipakai listener, bukan seluruh Appendix C.
    protected function seedChartOfAccounts(): void
    {
        $accounts = [
            ['code' => '101', 'name' => 'Kas', 'account_type' => 'asset'],
            ['code' => '102', 'name' => 'Bank', 'account_type' => 'asset'],
            ['code' => '103', 'name' => 'Piutang Usaha', 'account_type' => 'asset'],
            ['code' => '105', 'name' => 'Persediaan Barang Dagang', 'account_type' => 'asset'],
            ['code' => '401', 'name' => 'Pendapatan Jasa Konsultasi IT', 'account_type' => 'revenue'],
            ['code' => '402', 'name' => 'Pendapatan Penjualan Barang Teknologi', 'account_type' => 'revenue'],
            ['code' => '501', 'name' => 'Harga Pokok Penjualan Barang', 'account_type' => 'expense'],
        ];

        foreach ($accounts as $account) {
            ChartOfAccount::create($account + ['is_postable' => true, 'is_active' => true]);
        }
    }
}
