<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Demo\BannersData;
use Illuminate\View\View;

/**
 * Banners & pages (design: Banners.dc.html).
 * Passes sample data to the view; replace with real queries later.
 */
class BannerController extends Controller
{
    public function index(): View
    {
        return view('admin.banners.index', [
            'sections' => BannersData::sections(),
            'slides' => BannersData::slides(),
            'promos' => BannersData::promos(),
            'announcement' => BannersData::announcement(),
            'pages' => BannersData::pages(),
            'menus' => BannersData::menus(),
        ]);
    }
}
