<?php

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Exceptions\NonPostableAccountException;
use App\Modules\Finance\Exceptions\UnbalancedJournalEntryException;
use App\Modules\Finance\Exceptions\VoidReasonRequiredException;
use App\Modules\Finance\Models\ChartOfAccount;
use App\Modules\Finance\Models\JournalEntry;
use App\Shared\Support\GeneratesSequentialNumber;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class JournalEntryService
{
    use GeneratesSequentialNumber;

    /**
     * Buat journal entry baru dengan balance check dan validasi akun postable.
     *
     * $data:
     *   - entry_date (string|Carbon, required)
     *   - reference_type (string|null)
     *   - reference_id (int|null)
     *   - description (string|null)
     *   - created_by (int|null)
     *   - lines (array, required) tiap elemen: ['account_id' => int, 'debit' => numeric, 'credit' => numeric, 'description' => string|null]
     */
    public function createEntry(array $data): JournalEntry
    {
        $lines = $data['lines'] ?? [];

        if (count($lines) < 2) {
            throw new InvalidArgumentException('Journal entry butuh minimal 2 baris (lines).');
        }

        $this->assertBalanced($lines);
        $this->assertAllAccountsPostable($lines);

        return DB::transaction(function () use ($data, $lines) {
            $entryDate = Carbon::parse($data['entry_date']);

            $entryNumber = $this->generateSequentialNumber(
                table: 'journal_entries',
                column: 'entry_number',
                prefix: 'JE',
                year: $entryDate->year,
            );

            $entry = JournalEntry::create([
                'entry_number' => $entryNumber,
                'entry_date' => $entryDate,
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'description' => $data['description'] ?? null,
                'created_by' => $data['created_by'] ?? null,
                'status' => 'posted',
            ]);

            foreach ($lines as $line) {
                $entry->lines()->create([
                    'account_id' => $line['account_id'],
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                    'description' => $line['description'] ?? null,
                ]);
            }

            return $entry->fresh('lines');
        });
    }

    /**
     * Void journal entry. Tidak pernah mengubah nilai debit/credit,
     * cuma flip status dan wajib isi alasan (lihat ARCHITECTURE.md Section 5b).
     */
    public function voidEntry(JournalEntry $entry, string $reason): JournalEntry
    {
        if (trim($reason) === '') {
            throw new VoidReasonRequiredException();
        }

        $entry->update([
            'status' => 'void',
            'void_reason' => $reason,
        ]);

        return $entry->fresh('lines');
    }

    protected function assertBalanced(array $lines): void
    {
        $totalDebit = '0.00';
        $totalCredit = '0.00';

        foreach ($lines as $line) {
            $totalDebit = bcadd($totalDebit, (string) ($line['debit'] ?? 0), 2);
            $totalCredit = bcadd($totalCredit, (string) ($line['credit'] ?? 0), 2);
        }

        if (bccomp($totalDebit, $totalCredit, 2) !== 0) {
            throw new UnbalancedJournalEntryException($totalDebit, $totalCredit);
        }
    }

    protected function assertAllAccountsPostable(array $lines): void
    {
        $accountIds = collect($lines)->pluck('account_id')->unique();

        $postableAccounts = ChartOfAccount::whereIn('id', $accountIds)
            ->where('is_postable', true)
            ->pluck('code', 'id');

        $invalid = $accountIds->diff($postableAccounts->keys());

        if ($invalid->isNotEmpty()) {
            $invalidCodes = ChartOfAccount::whereIn('id', $invalid)->pluck('code')->all();

            // account_id yang sama sekali tidak ditemukan tidak akan punya code, tandai eksplisit
            $missingCount = $invalid->count() - count($invalidCodes);
            if ($missingCount > 0) {
                $invalidCodes[] = "{$missingCount} account_id tidak ditemukan";
            }

            throw new NonPostableAccountException($invalidCodes);
        }
    }
}
