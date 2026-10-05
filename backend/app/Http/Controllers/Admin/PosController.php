<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Demo\PosData;
use Illuminate\View\View;

/**
 * POS (design: POS.dc.html / T-POS.dc.html).
 * Passes sample data to the view; replace with real queries later.
 * The cart, payment and hold logic run client-side (Alpine) in the view.
 */
class PosController extends Controller
{
    public function index(): View
    {
        return view('admin.pos.index', [
            'register' => PosData::register(),
            'categories' => PosData::categories(),
            'catalog' => PosData::catalog(),
            'cart' => PosData::initialCart(),
            'lastScanned' => PosData::lastScanned(),
            'discount' => PosData::initialDiscount(),
            'coupons' => PosData::coupons(),
            'heldSales' => PosData::heldSales(),
            'customers' => PosData::customerLookup(),
            'phone' => PosData::initialPhone(),
            'receiptUrl' => route('admin.orders.receipt', PosData::sampleReceiptOrder()),
        ]);
    }
}
