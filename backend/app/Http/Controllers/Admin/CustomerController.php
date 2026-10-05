<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Demo\CustomersData;
use Illuminate\View\View;

/**
 * Customers (design: Customers.dc.html).
 * Passes sample data to the view; replace with real queries later.
 */
class CustomerController extends Controller
{
    public function index(): View
    {
        return view('admin.customers.index', [
            'stats' => CustomersData::stats(),
            'customers' => CustomersData::rows(),
            'blocked' => CustomersData::blocked(),
            'blockedTotal' => CustomersData::BLOCKED_TOTAL,
            'total' => CustomersData::total(),
        ]);
    }
}
