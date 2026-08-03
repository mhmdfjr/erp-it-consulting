<?php

namespace App\Modules\Finance\Exceptions;

use RuntimeException;

class NonPostableAccountException extends RuntimeException
{
    public function __construct(array $invalidCodes)
    {
        $codes = implode(', ', $invalidCodes);

        parent::__construct(
            "Tidak bisa posting jurnal ke akun berikut karena bukan akun postable atau tidak ditemukan: {$codes}."
        );
    }
}
