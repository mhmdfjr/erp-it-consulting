<?php

namespace Tests\Unit\Modules\SalesInventory\Services;

use App\Models\User;
use App\Modules\SalesInventory\Exceptions\InsufficientStockException;
use App\Modules\SalesInventory\Exceptions\ReasonCodeRequiredException;
use App\Modules\SalesInventory\Models\Item;
use App\Modules\SalesInventory\Models\StockLevel;
use App\Modules\SalesInventory\Models\StockMovement;
use App\Modules\SalesInventory\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected InventoryService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
        $this->service = new InventoryService();
    }

    public function test_decrease_stock_menolak_kalau_stok_tidak_cukup(): void
    {
        $item = Item::factory()->create(['item_type' => 'physical_good']);
        StockLevel::create(['item_id' => $item->id, 'quantity_on_hand' => 5, 'quantity_reserved' => 0]);

        $this->expectException(InsufficientStockException::class);
        $this->service->decreaseStock($item, 10.0);
    }

    public function test_record_adjustment_menolak_kalau_reason_code_kosong(): void
    {
        $item = Item::factory()->create(['item_type' => 'physical_good']);
        StockLevel::create(['item_id' => $item->id, 'quantity_on_hand' => 10, 'quantity_reserved' => 0]);

        $this->expectException(ReasonCodeRequiredException::class);
        $this->service->recordAdjustment($item, 5.0, 'in', '');
    }

    public function test_release_reserved_stock_mengurangi_reserved_tanpa_stock_movement_baru(): void
    {
        $item = Item::factory()->create(['item_type' => 'physical_good']);
        StockLevel::create(['item_id' => $item->id, 'quantity_on_hand' => 10, 'quantity_reserved' => 6]);

        $movementCountBefore = StockMovement::count();

        $stockLevel = $this->service->releaseReservedStock($item, 6.0);

        $this->assertEquals(0, $stockLevel->quantity_reserved);
        $this->assertEquals($movementCountBefore, StockMovement::count());
    }

    public function test_release_reserved_stock_tidak_pernah_negatif(): void
    {
        $item = Item::factory()->create(['item_type' => 'physical_good']);
        StockLevel::create(['item_id' => $item->id, 'quantity_on_hand' => 10, 'quantity_reserved' => 2]);

        // Release lebih besar dari yang direservasi skenario defensif kalau
        $stockLevel = $this->service->releaseReservedStock($item, 5.0);

        $this->assertEquals(0, $stockLevel->quantity_reserved);
    }

    public function test_reserve_stock_menolak_kalau_available_tidak_cukup(): void
    {
        $item = Item::factory()->create(['item_type' => 'physical_good']);
        StockLevel::create(['item_id' => $item->id, 'quantity_on_hand' => 10, 'quantity_reserved' => 8]);

        // Available cuma 2 (10 - 8), reserve 5 harus ditolak.
        $this->expectException(InsufficientStockException::class);
        $this->service->reserveStock($item, 5.0);
    }

    public function test_record_adjustment_menolak_direction_out_kalau_stok_tidak_cukup(): void
    {
        $item = Item::factory()->create(['item_type' => 'physical_good']);
        StockLevel::create(['item_id' => $item->id, 'quantity_on_hand' => 3, 'quantity_reserved' => 0]);

        $this->expectException(InsufficientStockException::class);
        $this->service->recordAdjustment($item, 5.0, 'out', 'stock_opname');
    }

    public function test_fulfill_reserved_stock_mengurangi_on_hand_dan_reserved_sekaligus(): void
    {
        $item = Item::factory()->create(['item_type' => 'physical_good']);
        StockLevel::create(['item_id' => $item->id, 'quantity_on_hand' => 10, 'quantity_reserved' => 4]);

        $stockLevel = $this->service->fulfillReservedStock($item, 4.0);

        $this->assertEquals(6, $stockLevel->quantity_on_hand);
        $this->assertEquals(0, $stockLevel->quantity_reserved);
        $this->assertDatabaseHas('stock_movements', [
            'item_id' => $item->id,
            'movement_type' => 'sale_out',
            'quantity' => 4,
        ]);
    }
}
