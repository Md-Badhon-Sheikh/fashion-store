<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Demo\CouponsData;
use Illuminate\View\View;

/**
 * Coupons (design: Coupons.dc.html).
 * Passes sample data to the view; replace with real queries later.
 */
class CouponController extends Controller
{
    public function index(): View
    {
        return view('admin.coupons.index', [
            'kpis' => CouponsData::kpis(),
            'coupons' => CouponsData::coupons(),
            'codes' => CouponsData::generatedCodes(),
            'types' => CouponsData::discountTypes(),
            'cart' => CouponsData::previewCart(),
            'customerErrors' => CouponsData::errorMessages(),
        ]);
    }
}
