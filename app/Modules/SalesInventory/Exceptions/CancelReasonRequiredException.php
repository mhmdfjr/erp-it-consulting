<?php

namespace App\Modules\SalesInventory\Exceptions;

use Exception;

class CancelReasonRequiredException extends Exception
{
    public function __construct()
    {
        parent::__construct('Alasan pembatalan (cancel_reason) wajib diisi.');
    }
}
