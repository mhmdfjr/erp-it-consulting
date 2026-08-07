<?php

namespace App\Modules\SalesInventory\Livewire;

use App\Modules\SalesInventory\Exceptions\InsufficientStockException;
use App\Modules\SalesInventory\Models\Customer;
use App\Modules\SalesInventory\Models\Item;
use App\Modules\SalesInventory\Services\SalesOrderService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CreateOrder extends Component
{
    public ?int $customer_id = null;
    public string $order_date;

    public array $items = [];
    public int $nextItemKey = 0;

    public function mount(): void
    {
        $this->order_date = now()->format('Y-m-d');
        $this->addItem();
    }

    public function addItem(): void
    {
        $key = $this->nextItemKey++;

        $this->items[$key] = [
            'item_id' => null,
            'quantity' => 1,
            'unit_price' => 0,
            'available_stock' => null,
        ];
    }

    public function removeItem(int $key): void
    {
        unset($this->items[$key]);

        if (empty($this->items)) {
            $this->addItem();
        }
    }

    /**
     * Dipanggil lewat wire:change saat item_id baris tertentu berubah.
     * Auto-isi unit_price, dan untuk physical_good hitung available stock.
     */
    public function itemSelected(int $key): void
    {
        $itemId = $this->items[$key]['item_id'];

        if (! $itemId) {
            $this->items[$key]['unit_price'] = 0;
            $this->items[$key]['available_stock'] = null;
            return;
        }

        $item = Item::with('stockLevel')->find($itemId);

        if (! $item) {
            return;
        }

        $this->items[$key]['unit_price'] = $item->unit_price;

        if ($item->isPhysicalGood() && $item->stockLevel) {
            $available = bcsub(
                (string) $item->stockLevel->quantity_on_hand,
                (string) $item->stockLevel->quantity_reserved,
                2,
            );
            $this->items[$key]['available_stock'] = (float) $available;
        } else {
            $this->items[$key]['available_stock'] = null;
        }

        $this->clampQuantity($key);
    }

    /**
     * Dipanggil otomatis oleh Livewire tiap kali ada public property berubah.
     * Dipakai di sini untuk clamp quantity real-time kalau user ketik manual
     */
    public function updated(string $name): void
    {
        if (preg_match('/^items\.(\d+)\.quantity$/', $name, $matches)) {
            $this->clampQuantity((int) $matches[1]);
        }
    }

    protected function clampQuantity(int $key): void
    {
        $available = $this->items[$key]['available_stock'] ?? null;

        if ($available !== null && $this->items[$key]['quantity'] > $available) {
            $this->items[$key]['quantity'] = $available > 0 ? (int) floor($available) : 1;
        }
    }

    #[Computed]
    public function total(): string
    {
        $total = '0.00';

        foreach ($this->items as $line) {
            $subtotal = bcmul(
                (string) ($line['quantity'] ?? 0),
                (string) ($line['unit_price'] ?? 0),
                2
            );

            $total = bcadd($total, $subtotal, 2);
        }

        return $total;
    }

    public function getSubtotal(array $line): string
    {
        return bcmul((string) ($line['quantity'] ?? 0), (string) ($line['unit_price'] ?? 0), 2);
    }

    public function save(SalesOrderService $service)
    {
        $this->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'order_date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $dataItems = [];
        foreach ($this->items as $line) {
            $item = Item::findOrFail($line['item_id']);

            $dataItems[] = [
                'item_id' => $item->id,
                'quantity' => $line['quantity'],
                'unit_price' => $item->unit_price,
            ];
        }

        try {
            $order = $service->createOrder([
                'customer_id' => $this->customer_id,
                'order_date' => $this->order_date,
                'items' => $dataItems,
            ]);
        } catch (InsufficientStockException $e) {
            $this->addError('items', $e->getMessage());
            return;
        }

        session()->flash('success', "Sales Order \"{$order->order_number}\" berhasil dibuat.");

        return redirect()->route('sales.orders.show', $order);
    }

    public function render()
    {
        return view('sales::livewire.create-order', [
            'customers' => Customer::orderBy('name')->get(),
            'availableItems' => Item::query()
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->where('item_type', 'service')
                        ->orWhereHas('stockLevel', function ($stockQuery) {
                            $stockQuery->whereColumn('quantity_on_hand', '>', 'quantity_reserved');
                        });
                })
                ->with('stockLevel')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
