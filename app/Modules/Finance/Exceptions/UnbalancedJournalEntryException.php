<?php

namespace App\Modules\Finance\Exceptions;

use RuntimeException;

class UnbalancedJournalEntryException extends RuntimeException
{
    public function __construct(string $totalDebit, string $totalCredit)
    {
        parent::__construct(
            "Journal entry tidak balance: total debit ({$totalDebit}) tidak sama dengan total credit ({$totalCredit})."
        );
    }
}
