<?php

namespace App\Support\Demo;

use App\Support\DemoData;

/**
 * Sample data for Stock-in / purchases (design: StockIn.dc.html).
 */
final class StockInData
{
    /** Recent purchase orders (cards at the top). */
    public static function recent(): array
    {
        $po = fn (string $id, string $supplierId, string $supplier, string $date, int $pcs, int $amount, int $paid, string $status, string $statusLabel) => [
            'id' => $id, 'supplier_id' => $supplierId, 'supplier' => $supplier, 'date' => $date, 'pcs' => $pcs,
            'amount' => $amount, 'paid' => $paid, 'due' => $amount - $paid, 'status' => $status, 'status_label' => $statusLabel,
        ];

        return [
            $po('#PO-0042', 's03', 'Supplier 03', '4 Oct', 24, 38640, 20000, 'partial', 'Partial'),
            $po('#PO-0041', 's01', 'Supplier 01', '1 Oct', 60, 72300, 72300, 'paid', 'Paid'),
            $po('#PO-0040', 's05', 'Supplier 05', '28 Sep', 36, 41850, 0, 'overdue', 'Unpaid'),
            $po('#PO-0039', 's02', 'Supplier 02', '24 Sep', 48, 28900, 28900, 'paid', 'Paid'),
        ];
    }

    /** Lines of the draft purchase #PO-0043. */
    public static function lines(): array
    {
        $l = fn (string $name, string $sku, string $size, string $colour, int $cost, int $stock, int $qty, string $img, string $tone) => [
            'name' => $name, 'sku' => $sku, 'size' => $size, 'colour' => $colour, 'hex' => ProductData::hex($colour),
            'cost' => $cost, 'stock' => $stock, 'qty' => $qty, 'img' => DemoData::img($img), 'tone' => $tone,
        ];

        return [
            $l('Embroidered Cotton Panjabi', 'PNJ-1024-OW-M', 'M', 'Off-white', 1420, 4, 10, 'p_panjabi_offwhite', '#E4DCCF'),
            $l('Embroidered Cotton Panjabi', 'PNJ-1024-OW-XL', 'XL', 'Off-white', 1420, 2, 8, 'p_panjabi_offwhite', '#E4DCCF'),
            $l('Embroidered Cotton Panjabi', 'PNJ-1024-OW-XXL', 'XXL', 'Off-white', 1420, 0, 6, 'p_panjabi_offwhite', '#E4DCCF'),
            $l('Printed Lawn Three-Piece', 'TPC-0311-PK-M', 'M', 'Pink', 1600, 0, 6, 'p_threepiece_lawn', '#E7D6DA'),
            $l('Slim Fit Oxford Shirt', 'SHT-0457-SB-L', 'L', 'Sky blue', 890, 3, 10, 'p_shirt_oxford', '#D3DCE4'),
        ];
    }

    /** Suppliers offered in the purchase form (all, with current due). */
    public static function supplierOptions(): array
    {
        return array_values(array_map(fn (array $s) => [
            'id' => $s['id'], 'name' => $s['name'], 'due' => $s['due'],
            'label' => $s['name'].' · '.$s['short'].($s['due'] > 0 ? ' · due '.DemoData::money($s['due']) : ''),
        ], SupplierData::suppliers()));
    }

    /** Form defaults from the design. */
    public static function defaults(): array
    {
        return [
            'po' => '#PO-0043',
            'supplier' => 's03',
            'invoice' => 'INV-1187',
            'date' => '2026-10-04',
            'discount' => 1580,
            'transport' => 800,
            'paid' => 30000,
            'method' => 'bKash',
            'print' => true,
            'note' => 'Eid restock for Off-white panjabi. 2 pcs XL had loose threads, supplier will replace next delivery.',
        ];
    }
}
