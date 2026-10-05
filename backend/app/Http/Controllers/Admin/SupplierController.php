<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Demo\SupplierData;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Suppliers (design: Suppliers.dc.html).
 * Passes sample data to the view; replace with real queries later.
 * ?supplier=s05 opens that supplier's ledger (used by the Stock-in purchase cards).
 */
class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $suppliers = SupplierData::suppliers();
        $selected = (string) $request->query('supplier', 's03');
        if (! isset($suppliers[$selected])) {
            $selected = 's03';
        }

        return view('admin.suppliers.index', [
            'kpis' => SupplierData::kpis(),
            'suppliers' => $suppliers,
            'selected' => $selected,
            'refs' => SupplierData::paymentRefs(),
        ]);
    }
}
