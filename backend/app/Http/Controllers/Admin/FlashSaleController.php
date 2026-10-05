<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Demo\FlashSaleData;
use Illuminate\View\View;

/**
 * Flash sale (design: FlashSale.dc.html).
 * Passes sample data to the view; replace with real queries later.
 */
class FlashSaleController extends Controller
{
    public function index(): View
    {
        return view('admin.flash-sales.index', [
            'campaigns' => FlashSaleData::campaigns(),
            'products' => FlashSaleData::products(),
            'banner' => FlashSaleData::banner(),
            'selected' => 'c1',
        ]);
    }
}
