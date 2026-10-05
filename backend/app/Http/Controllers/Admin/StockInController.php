<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Demo\BarcodeData;
use App\Support\Demo\StockInData;
use App\Support\Demo\SupplierData;
use Illuminate\View\View;

/**
 * Stock-in / purchases (design: StockIn.dc.html).
 * Passes sample data to the view; replace with real queries later.
 */
class StockInController extends Controller
{
    public function index(): View
    {
        return view('admin.stock-in.index', [
            'recent' => StockInData::recent(),
            'lines' => StockInData::lines(),
            'suppliers' => StockInData::supplierOptions(),
            'defaults' => StockInData::defaults(),
            'refs' => SupplierData::paymentRefs('Cash voucher CV-0311'),
            'variants' => BarcodeData::lookup(),
        ]);
    }
}
