<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Demo\ReturnsData;
use Illuminate\View\View;

/**
 * Returns & exchanges (design: Returns.dc.html).
 * Passes sample data to the view; replace with real queries later.
 */
class ReturnRequestController extends Controller
{
    public function index(): View
    {
        return view('admin.returns.index', [
            'tabs' => ReturnsData::tabs(),
            'tones' => ReturnsData::tones(),
            'methods' => ReturnsData::refundMethods(),
            'requests' => ReturnsData::requests(),
            'policyDays' => ReturnsData::POLICY_DAYS,
        ]);
    }
}
