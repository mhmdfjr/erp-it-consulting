<?php

namespace App\Modules\Finance\Exceptions;

use RuntimeException;

class VoidReasonRequiredException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Alasan void wajib diisi, tidak boleh kosong.');
    }
}
