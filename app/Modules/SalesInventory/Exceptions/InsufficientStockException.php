<?php

namespace App\Modules\SalesInventory\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    public function __construct(string $itemName, string $available, string $requested)
    {
        parent::__construct(
            "Stok tidak cukup untuk \"{$itemName}\": tersedia {$available}, diminta {$requested}."
        );
    }
}
