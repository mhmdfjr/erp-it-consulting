<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Models\ChartOfAccount;

class ChartOfAccountController extends Controller
{
    public function index()
    {
        $this->authorize('finance.coa.view');

        $accounts = ChartOfAccount::whereNull('parent_id')
            ->with('children')
            ->orderBy('code')
            ->get();

        return view('finance::chart-of-accounts.index', compact('accounts'));
    }
}
