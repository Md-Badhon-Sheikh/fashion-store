<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Demo\BarcodeData;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Barcode labels (design: Barcodes.dc.html).
 * Passes sample data to the view; replace with real queries later.
 * ?sku=PNJ-1024-OW-S (from "Print label" on Products) puts that variant first in the queue.
 */
class BarcodeController extends Controller
{
    public function index(Request $request): View
    {
        $queue = BarcodeData::queue();
        $usePo = true;

        $sku = (string) $request->query('sku', '');
        if ($sku !== '' && ($v = BarcodeData::find($sku)) !== null) {
            $queue = array_values(array_filter($queue, fn ($q) => $q['sku'] !== $v['sku']));
            array_unshift($queue, $v + ['id' => 'x', 'po' => 0, 'qty' => 1]);
            $usePo = false;
        }

        $scanCode = '8901234500127';

        return view('admin.barcodes.index', [
            'queue' => $queue,
            'usePo' => $usePo,
            'purchases' => BarcodeData::purchases(),
            'lookup' => BarcodeData::lookup(),
            'scanCode' => $scanCode,
            'scan' => BarcodeData::find($scanCode),
        ]);
    }
}
