<?php

namespace App\Modules\SalesInventory\Exceptions;

use Exception;

class ReasonCodeRequiredException extends Exception
{
    public function __construct()
    {
        parent::__construct('reason_code wajib diisi untuk stock movement bertipe adjustment.');
    }
}
