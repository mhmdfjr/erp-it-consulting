<?php

namespace App\Modules\Finance\Livewire;

use App\Modules\Finance\Exceptions\NonPostableAccountException;
use App\Modules\Finance\Exceptions\UnbalancedJournalEntryException;
use App\Modules\Finance\Models\ChartOfAccount;
use App\Modules\Finance\Services\JournalEntryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class CreateJournalEntry extends Component
{
    public string $entry_date;
    public ?string $description = null;

    /**
     * Associative array, KEY-nya sendiri jadi identitas baris (bukan posisi/index).
     * Format: [key => ['account_id' => ..., 'debit' => ..., 'credit' => ..., 'description' => ...]]
     * Tidak pernah di-reindex, supaya wire:key, wire:model, dan wire:click selalu
     * mengacu ke baris yang sama meski baris lain ditambah/dihapus.
     */
    public array $lines = [];

    public int $nextLineKey = 0;

    public function mount(): void
    {
        Gate::authorize('finance.journal.create');

        $this->entry_date = now()->toDateString();
        $this->addLine();
        $this->addLine();
    }

    public function addLine(): void
    {
        $key = $this->nextLineKey++;

        $this->lines[$key] = [
            'account_id' => null,
            'debit' => null,
            'credit' => null,
            'description' => null,
        ];
    }

    public function removeLine(int $key): void
    {
        if (count($this->lines) <= 2) {
            return;
        }

        unset($this->lines[$key]);
    }

    public function getTotalDebitProperty(): string
    {
        return number_format(collect($this->lines)->sum(fn ($line) => (float) ($line['debit'] ?? 0)), 2);
    }

    public function getTotalCreditProperty(): string
    {
        return number_format(collect($this->lines)->sum(fn ($line) => (float) ($line['credit'] ?? 0)), 2);
    }

    public function save(JournalEntryService $service)
    {
        Gate::authorize('finance.journal.create');

        $this->validate([
            'entry_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['required', 'exists:chart_of_accounts,id'],
            'lines.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $entry = $service->createEntry([
                'entry_date' => $this->entry_date,
                'description' => $this->description,
                'created_by' => Auth::id(),
                'lines' => collect($this->lines)->map(fn ($line) => [
                    'account_id' => $line['account_id'],
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                    'description' => $line['description'] ?? null,
                ])->values()->all(),
            ]);
        } catch (UnbalancedJournalEntryException|NonPostableAccountException $e) {
            $this->addError('lines', $e->getMessage());
            return;
        }

        session()->flash('success', "Journal entry {$entry->entry_number} berhasil dibuat.");

        $this->redirect(route('finance.journal-entries.show', $entry));
    }

    public function render()
    {
        return view('finance::livewire.create-journal-entry', [
            'accounts' => ChartOfAccount::where('is_postable', true)->where('is_active', true)->orderBy('code')->get(),
        ]);
    }
}
