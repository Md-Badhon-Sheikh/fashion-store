<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Demo\BarcodeData;
use App\Support\Demo\InventoryData;
use Illuminate\View\View;

/**
 * Stock overview (design: Inventory.dc.html).
 * Passes sample data to the view; replace with real queries later.
 */
class InventoryController extends Controller
{
    public function index(): View
    {
        $rows = InventoryData::stockRows();

        return view('admin.inventory.index', [
            'kpis' => InventoryData::kpis(),
            'tabs' => InventoryData::tabs(),
            'rows' => $rows,
            'filters' => InventoryData::filterOptions($rows),
            'sizes' => InventoryData::SIZES,
            'ledger' => InventoryData::ledger(),
            'adjustments' => InventoryData::adjustments(),
            'history' => InventoryData::history(),
            'variants' => BarcodeData::lookup(),
            'adjustSku' => 'PNJ-1024-OW-XL',
            'adjustVariant' => BarcodeData::find('PNJ-1024-OW-XL'),
        ]);
    }
}
