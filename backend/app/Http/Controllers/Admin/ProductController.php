<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Demo\ProductData;
use App\Support\DemoData;
use Illuminate\View\View;

/**
 * Products list and add/edit form (designs: Products, ProductForm).
 * create() and edit() share one view: admin/products/form.blade.php
 * ($product is null when adding).
 */
class ProductController extends Controller
{
    public function index(): View
    {
        $rows = ProductData::listRows();

        return view('admin.products.index', [
            'products' => $rows,
            'meta' => ProductData::listMeta($rows),
            'tabs' => ProductData::statusTabs(),
            'filters' => ProductData::listFilters(),
        ]);
    }

    public function create(): View
    {
        return $this->form(null);
    }

    public function edit(string $product): View
    {
        $found = DemoData::findProduct($product);
        abort_if($found === null, 404);

        return $this->form($found);
    }

    private function form(?array $product): View
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => DemoData::categories(),
            'state' => ProductData::formState($product),
            'tree' => ProductData::categoryTree(),
            'skuPrefixes' => ProductData::skuPrefixes(),
            'nextSku' => ProductData::nextSkuNumbers(),
            'sizeCharts' => ProductData::sizeCharts(),
            'relatedPool' => ProductData::relatedPool(),
        ]);
    }
}
