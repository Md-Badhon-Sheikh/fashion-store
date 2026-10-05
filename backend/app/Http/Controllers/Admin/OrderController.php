<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Demo\OrdersData;
use App\Support\DemoData;
use Illuminate\View\View;

/**
 * Orders list, order detail and printable documents
 * (designs: Orders, M-AdminOrders, OrderDetail, Invoice).
 *
 * {order} is the order id without "#", e.g. /admin/orders/WB-10482.
 */
class OrderController extends Controller
{
    public function index(): View
    {
        return view('admin.orders.index', [
            'rows' => OrdersData::rows(),
            'tabs' => OrdersData::tabs(),
            'tabCounts' => OrdersData::tabCounts(),
            'todayCounts' => OrdersData::todayCounts(),
            'nextActions' => OrdersData::nextActions(),
        ]);
    }

    public function show(string $order): View
    {
        $order = $this->findOrFail($order);

        return view('admin.orders.show', [
            'order' => $order,
            'detail' => OrdersData::detail($order),
            'flow' => OrdersData::FLOW,
        ]);
    }

    public function invoice(string $order): View
    {
        return view('admin.orders.invoice', $this->printData($order));
    }

    public function receipt(string $order): View
    {
        return view('admin.orders.receipt', $this->printData($order));
    }

    public function packingSlip(string $order): View
    {
        return view('admin.orders.packing-slip', $this->printData($order));
    }

    /** Order + derived print fields (App\Support\Demo\OrderPrintData) + seller details. */
    private function printData(string $id): array
    {
        $order = $this->findOrFail($id);

        return [
            'order' => $order,
            'doc' => \App\Support\Demo\OrderPrintData::document($order),
            'store' => \App\Support\Demo\OrderPrintData::store(),
        ];
    }

    private function findOrFail(string $id): array
    {
        $order = DemoData::findOrder($id);
        abort_if($order === null, 404);

        return $order;
    }
}
