<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Demo\AccountingData;
use Illuminate\View\View;

/**
 * Accounting (design: Accounting.dc.html).
 * Passes sample data to the view; replace with real queries later.
 */
class AccountingController extends Controller
{
    public function index(): View
    {
        return view('admin.accounting.index', [
            'defaultRange' => AccountingData::DEFAULT_RANGE,
            'rangeOptions' => AccountingData::rangeOptions(),
            'rangeMeta' => AccountingData::rangeMeta(),
            'byRange' => AccountingData::byRange(),
            'balances' => AccountingData::balances(),
            'months' => AccountingData::months(),
            'expenseCategories' => AccountingData::expenseCategories(),
            'paymentMethods' => AccountingData::paymentMethods(),
            'expenses' => AccountingData::recentExpenses(),
            'opening' => AccountingData::openingBalance(),
            'transactions' => AccountingData::transactions(),
        ]);
    }
}
