<?php

namespace App\Modules\SalesInventory\Services;

use App\Modules\SalesInventory\Exceptions\InsufficientStockException;
use App\Modules\SalesInventory\Exceptions\ReasonCodeRequiredException;
use App\Modules\SalesInventory\Models\Item;
use App\Modules\SalesInventory\Models\StockLevel;
use App\Modules\SalesInventory\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryService
{
    public function increaseStock(Item $item, float $qty, array $referenceData = []): StockLevel
    {
        $this->assertPhysicalGood($item);

        return DB::transaction(function () use ($item, $qty, $referenceData) {
            $stockLevel = $this->lockStockLevel($item);

            $stockLevel->quantity_on_hand = bcadd((string) $stockLevel->quantity_on_hand, (string) $qty, 2);
            $stockLevel->save();

            return $stockLevel->fresh();
        });
    }

    public function decreaseStock(Item $item, float $qty, array $referenceData = []): StockLevel
    {
        $this->assertPhysicalGood($item);

        return DB::transaction(function () use ($item, $qty, $referenceData) {
            $stockLevel = $this->lockStockLevel($item);

            if (bccomp((string) $stockLevel->quantity_on_hand, (string) $qty, 2) < 0) {
                throw new InsufficientStockException(
                    $item->name,
                    (string) $stockLevel->quantity_on_hand,
                    (string) $qty,
                );
            }

            $stockLevel->quantity_on_hand = bcsub((string) $stockLevel->quantity_on_hand, (string) $qty, 2);
            $stockLevel->save();

            StockMovement::create([
                'item_id' => $item->id,
                'movement_type' => 'sale_out',
                'quantity' => $qty,
                'reference_type' => $referenceData['reference_type'] ?? null,
                'reference_id' => $referenceData['reference_id'] ?? null,
                'reason_code' => null,
                'note' => $referenceData['note'] ?? null,
                'created_by' => Auth::id() ?? $referenceData['created_by'] ?? null,
            ]);

            return $stockLevel->fresh();
        });
    }

    // Reservasi stok untuk sales order yang belum completed. Menolak kalau available stok tidak cukup,
    public function reserveStock(Item $item, float $qty): StockLevel
    {
        $this->assertPhysicalGood($item);

        return DB::transaction(function () use ($item, $qty) {
            $stockLevel = $this->lockStockLevel($item);

            $available = bcsub((string) $stockLevel->quantity_on_hand, (string) $stockLevel->quantity_reserved, 2);

            if (bccomp($available, (string) $qty, 2) < 0) {
                throw new InsufficientStockException($item->name, $available, (string) $qty);
            }

            $stockLevel->quantity_reserved = bcadd((string) $stockLevel->quantity_reserved, (string) $qty, 2);
            $stockLevel->save();

            return $stockLevel->fresh();
        });
    }


    // Lepas reservasi (dipakai SalesOrderService::cancelOrder()). Tidak insert
    // row stock_movements — ini bukan physical movement, barang tidak pernah keluar gudang
    public function releaseReservedStock(Item $item, float $qty): StockLevel
    {
        $this->assertPhysicalGood($item);

        return DB::transaction(function () use ($item, $qty) {
            $stockLevel = $this->lockStockLevel($item);

            $newReserved = bcsub((string) $stockLevel->quantity_reserved, (string) $qty, 2);

            // Clamp ke 0, jangan sampai negatif karena bug
            if (bccomp($newReserved, '0', 2) < 0) {
                $newReserved = '0';
            }

            $stockLevel->quantity_reserved = $newReserved;
            $stockLevel->save();

            return $stockLevel->fresh();
        });
    }


    // Realisasi stok keluar untuk item yang sudah direservasi sebelumnya. Berbeda dari
    // Method ini mengurangi quantity_reserved, karena reservasi saat createOrder() sekarang diconsume
    // dilepas kembali ke pool.
    public function fulfillReservedStock(Item $item, float $qty, array $referenceData = []): StockLevel
    {
        $this->assertPhysicalGood($item);

        return DB::transaction(function () use ($item, $qty, $referenceData) {
            $stockLevel = $this->lockStockLevel($item);

            if (bccomp((string) $stockLevel->quantity_on_hand, (string) $qty, 2) < 0) {
                throw new InsufficientStockException(
                    $item->name,
                    (string) $stockLevel->quantity_on_hand,
                    (string) $qty,
                );
            }

            $stockLevel->quantity_on_hand = bcsub((string) $stockLevel->quantity_on_hand, (string) $qty, 2);

            $newReserved = bcsub((string) $stockLevel->quantity_reserved, (string) $qty, 2);
            if (bccomp($newReserved, '0', 2) < 0) {
                $newReserved = '0'; // clamp, sama seperti releaseReservedStock()
            }
            $stockLevel->quantity_reserved = $newReserved;

            $stockLevel->save();

            StockMovement::create([
                'item_id' => $item->id,
                'movement_type' => 'sale_out',
                'quantity' => $qty,
                'reference_type' => $referenceData['reference_type'] ?? null,
                'reference_id' => $referenceData['reference_id'] ?? null,
                'reason_code' => null,
                'note' => $referenceData['note'] ?? null,
                'created_by' => Auth::id() ?? $referenceData['created_by'] ?? null,
            ]);

            return $stockLevel->fresh();
        });
    }

    /**
     * Adjustment manual (task 2.19 UI). reason_code wajib diisi sesuai
     * DATABASE.md Assumption 3.
     */
    public function recordAdjustment(
        Item $item,
        float $qty,
        string $direction,
        string $reasonCode,
        ?string $note = null,
    ): StockLevel {
        $this->assertPhysicalGood($item);

        if (! in_array($direction, ['in', 'out'], true)) {
            throw new InvalidArgumentException('direction harus "in" atau "out".');
        }

        if (trim($reasonCode) === '') {
            throw new ReasonCodeRequiredException();
        }

        return DB::transaction(function () use ($item, $qty, $direction, $reasonCode, $note) {
            $stockLevel = $this->lockStockLevel($item);

            if ($direction === 'in') {
                $stockLevel->quantity_on_hand = bcadd((string) $stockLevel->quantity_on_hand, (string) $qty, 2);
            } else {
                if (bccomp((string) $stockLevel->quantity_on_hand, (string) $qty, 2) < 0) {
                    throw new InsufficientStockException(
                        $item->name,
                        (string) $stockLevel->quantity_on_hand,
                        (string) $qty,
                    );
                }
                $stockLevel->quantity_on_hand = bcsub((string) $stockLevel->quantity_on_hand, (string) $qty, 2);
            }

            $stockLevel->save();

            StockMovement::create([
                'item_id' => $item->id,
                'movement_type' => $direction === 'in' ? 'adjustment_in' : 'adjustment_out',
                'quantity' => $qty,
                'reference_type' => null,
                'reference_id' => null,
                'reason_code' => $reasonCode,
                'note' => $note,
                'created_by' => Auth::id(),
            ]);

            return $stockLevel->fresh();
        });
    }

    protected function assertPhysicalGood(Item $item): void
    {
        if (! $item->isPhysicalGood()) {
            throw new InvalidArgumentException(
                "Item \"{$item->name}\" bertipe service, tidak punya stock tracking."
            );
        }
    }

    /**
     * Ambil (atau buat) row stock_levels dengan lockForUpdate, supaya concurrent
     * request terhadap item yang sama tidak race condition saat baca-ubah-simpan
     * quantity. WAJIB dipanggil di dalam DB::transaction() milik caller di atas.
     */
    protected function lockStockLevel(Item $item): StockLevel
    {
        $stockLevel = StockLevel::where('item_id', $item->id)->lockForUpdate()->first();

        if (! $stockLevel) {
            $stockLevel = StockLevel::create([
                'item_id' => $item->id,
                'quantity_on_hand' => 0,
                'quantity_reserved' => 0,
            ]);
        }

        return $stockLevel;
    }
}
