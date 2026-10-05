<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Demo\CategoryData;
use Illuminate\View\View;

/**
 * Categories & brands (design: Categories.dc.html).
 * Passes sample data to the view; replace with real queries later.
 */
class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', [
            'tabs' => CategoryData::tabs(),
            'rows' => CategoryData::rows(),
            'parents' => CategoryData::parentOptions(),
            'attrs' => CategoryData::attributes(),
            'brands' => CategoryData::brands(),
            'sizeCharts' => CategoryData::sizeCharts(),
            'selected' => 'pnj',
        ]);
    }
}
