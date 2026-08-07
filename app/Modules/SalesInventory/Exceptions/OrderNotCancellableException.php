<?php

namespace App\Modules\SalesInventory\Exceptions;

use Exception;

class OrderNotCancellableException extends Exception
{
    public function __construct(string $currentStatus)
    {
        parent::__construct(
            "Sales Order berstatus \"{$currentStatus}\" tidak bisa dibatalkan. Cancel cuma berlaku untuk status draft."
        );
    }
}
