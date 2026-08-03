<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Http\Requests\VoidJournalEntryRequest;
use App\Modules\Finance\Models\JournalEntry;
use App\Modules\Finance\Services\JournalEntryService;

class JournalEntryController extends Controller
{
    public function index()
    {
        $this->authorize('finance.journal.view');

        $entries = JournalEntry::query()
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->paginate(20);

        return view('finance::journal-entries.index', compact('entries'));
    }

    public function create()
    {
        $this->authorize('finance.journal.create');

        return view('finance::journal-entries.create');
    }

    public function show(JournalEntry $journalEntry)
    {
        $this->authorize('finance.journal.view');

        $journalEntry->load('lines.account', 'creator');

        return view('finance::journal-entries.show', compact('journalEntry'));
    }

    public function void(VoidJournalEntryRequest $request, JournalEntry $journalEntry, JournalEntryService $service)
    {
        if ($journalEntry->isVoid()) {
            return back()->with('error', 'Journal entry ini sudah berstatus void.');
        }

        $service->voidEntry($journalEntry, $request->validated('void_reason'));

        return redirect()
            ->route('finance.journal-entries.show', $journalEntry)
            ->with('success', "Journal entry {$journalEntry->entry_number} berhasil di-void.");
    }
}
