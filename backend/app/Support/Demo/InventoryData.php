<?php

namespace App\Support\Demo;

use App\Support\DemoData;

/**
 * Sample data for Stock overview (design: Inventory.dc.html).
 */
final class InventoryData
{
    public const SIZES = ['S', 'M', 'L', 'XL', 'XXL'];

    /** Low-stock alert level used for the size cells. */
    public const LOW_AT = 5;

    public static function kpis(): array
    {
        return [
            ['label' => 'Total stock', 'value' => DemoData::number(8734).' pcs', 'sub' => '412 products · '.DemoData::number(1986).' variants', 'tone' => 'default'],
            ['label' => 'Stock value at cost', 'value' => DemoData::money(6418500), 'sub' => 'At selling price '.DemoData::money(11240000), 'tone' => 'default'],
            ['label' => 'Low stock variants', 'value' => '23', 'sub' => 'At or below alert level', 'tone' => 'pending'],
            ['label' => 'Out of stock variants', 'value' => '9', 'sub' => '4 have pending online orders', 'tone' => 'danger'],
        ];
    }

    public static function tabs(): array
    {
        return [
            'levels' => 'Stock levels',
            'adjustments' => ['label' => 'Adjustments', 'count' => 14],
            'history' => 'Movement history',
        ];
    }

    /**
     * "Stock by size" rows: one product colour per row with a cell per size
     * (null = size not offered).
     */
    public static function stockRows(): array
    {
        $defs = [
            ['Embroidered Cotton Panjabi', 'PNJ-1024', 'Panjabi', 'Off-white', '#F1ECE1', [6, 4, 11, 2, 0], 1420, 'p_panjabi_offwhite', '#E4DCCF'],
            ['Embroidered Cotton Panjabi', 'PNJ-1024', 'Panjabi', 'Navy', '#1F2A44', [8, 12, 10, 6, 3], 1420, 'p_panjabi_navy', '#D3D8E2'],
            ['Linen Blend Panjabi', 'PNJ-1031', 'Panjabi', 'Off-white', '#F1ECE1', [3, 7, 9, 4, 2], 1560, 'p_panjabi_linen', '#E3DDD2'],
            ['Slim Fit Oxford Shirt', 'SHT-0457', 'Shirts', 'Sky blue', '#9CC3E4', [9, 7, 3, 5, null], 890, 'p_shirt_oxford', '#D3DCE4'],
            ['Premium Polo T-Shirt', 'TSH-0219', 'T-Shirts', 'Navy', '#1F2A44', [24, 31, 28, 19, 5], 420, 'p_polo', '#DCE0D6'],
            ['Basic Crew Neck T-Shirt', 'TSH-0102', 'T-Shirts', 'Black', '#1C1C1E', [15, 22, 25, 18, 11], 260, 'p_tshirt_basic', '#D7D9DC'],
            ['Block Print Cotton Kurti', 'KRT-0882', 'Kurti', 'Maroon', '#6B1F2A', [4, 9, 12, 6, null], 720, 'p_kurti_block', '#E8D8D6'],
            ['Printed Lawn Three-Piece', 'TPC-0311', 'Three-Piece', 'Pink', '#E7A9B9', [null, 0, 0, 0, null], 1600, 'p_threepiece_lawn', '#E7D6DA'],
            ['Cotton Pajama', 'PJM-0140', 'Pajama', 'White', '#FFFFFF', [10, 14, 16, 12, 8], 380, 'p_pajama', '#EDEBE6'],
        ];

        return array_map(function (array $d) {
            [$name, $sku, $cat, $colour, $hex, $qty, $cost, $img, $tone] = $d;
            $offered = array_values(array_filter($qty, fn ($q) => $q !== null));
            $total = array_sum($offered);
            $outN = count(array_filter($offered, fn ($q) => $q === 0));
            $lowN = count(array_filter($offered, fn ($q) => $q > 0 && $q <= self::LOW_AT));

            [$status, $label] = match (true) {
                $total === 0 => ['out-of-stock', 'Out of stock'],
                $outN > 0 => ['out-of-stock', $outN.' size out · '.$lowN.' low'],
                $lowN > 0 => ['low-stock', 'Low stock'],
                default => ['available', 'Available'],
            };

            $cells = [];
            foreach (self::SIZES as $i => $size) {
                $q = $qty[$i];
                $cells[] = match (true) {
                    $q === null => ['size' => $size, 'n' => '–', 'tag' => '', 'tone' => 'none', 'title' => $size.': not offered'],
                    $q === 0 => ['size' => $size, 'n' => '0', 'tag' => 'Out', 'tone' => 'out', 'title' => $size.': out of stock'],
                    $q <= self::LOW_AT => ['size' => $size, 'n' => (string) $q, 'tag' => 'Low', 'tone' => 'low', 'title' => $size.': low stock'],
                    default => ['size' => $size, 'n' => (string) $q, 'tag' => '', 'tone' => 'ok', 'title' => $size.': '.$q.' pcs'],
                };
            }

            return [
                'name' => $name, 'sku' => $sku, 'cat' => $cat, 'colour' => $colour, 'hex' => $hex,
                'img' => DemoData::img($img), 'tone' => $tone, 'cells' => $cells,
                'total' => $total, 'value' => $total * $cost, 'status' => $status, 'status_label' => $label,
                'sizes' => array_values(array_filter(self::SIZES, fn ($s, $i) => $qty[$i] !== null, ARRAY_FILTER_USE_BOTH)),
                'low' => $lowN, 'out' => $outN,
            ];
        }, $defs);
    }

    public static function filterOptions(array $rows): array
    {
        $cats = array_values(array_unique(array_column($rows, 'cat')));
        $colours = array_values(array_unique(array_column($rows, 'colour')));

        return [
            'cat' => ['label' => 'Category', 'options' => ['' => 'All categories'] + array_combine($cats, $cats)],
            'size' => ['label' => 'Size', 'options' => ['' => 'Any size'] + array_combine(self::SIZES, self::SIZES)],
            'colour' => ['label' => 'Colour', 'options' => ['' => 'Any colour'] + array_combine($colours, $colours)],
            'status' => ['label' => 'Status', 'options' => ['' => 'Any status', 'available' => 'Available', 'low' => 'Low stock', 'out' => 'Out of stock']],
        ];
    }

    /** Movement ledger of one variant (PNJ-1024-OW-M), newest first. */
    public static function ledger(): array
    {
        $led = fn (string $date, string $time, string $type, string $ref, string $note, int $in, int $out, int $bal, string $by) => [
            'date' => $date, 'time' => $time, 'type' => $type, 'tone' => self::typeTone($type), 'ref' => $ref, 'href' => self::refUrl($ref),
            'note' => $note, 'in' => $in ? '+'.$in : '', 'out' => $out ? '−'.$out : '', 'bal' => $bal, 'by' => $by,
        ];

        return [
            $led('4 Oct 2026', '12:30 PM', 'Sale-POS', '#POS-2290', 'Counter 1', 0, 1, 4, 'Sales Staff 01'),
            $led('3 Oct 2026', '6:45 PM', 'Sale-POS', '#POS-2271', 'Counter 2', 0, 2, 5, 'Sales Staff 02'),
            $led('2 Oct 2026', '9:12 PM', 'Sale-Online', '#WB-10452', 'COD · Dhaka', 0, 1, 7, 'System'),
            $led('30 Sep 2026', '5:20 PM', 'Sale-POS', '#POS-2241', 'Counter 1', 0, 4, 8, 'Sales Staff 01'),
            $led('27 Sep 2026', '11:03 AM', 'Sale-Online', '#WB-10398', 'COD · Chattogram', 0, 2, 12, 'System'),
            $led('22 Sep 2026', '3:40 PM', 'Adjustment', '#ADJ-0019', 'Damaged · stain', 0, 1, 14, 'Store Manager'),
            $led('19 Sep 2026', '7:55 PM', 'Sale-POS', '#POS-2166', 'Counter 2', 0, 3, 15, 'Sales Staff 02'),
            $led('14 Sep 2026', '1:10 PM', 'Return', '#RT-0087', 'From #WB-10233 · resaleable', 1, 0, 18, 'Store Manager'),
            $led('8 Sep 2026', '4:25 PM', 'Sale-POS', '#POS-2104', 'Counter 1', 0, 2, 17, 'Sales Staff 01'),
            $led('5 Sep 2026', '10:48 PM', 'Sale-Online', '#WB-10211', 'COD · Sylhet', 0, 1, 19, 'System'),
            $led('1 Sep 2026', '10:15 AM', 'Stock-in', '#PO-0036', 'Supplier 03', 20, 0, 20, 'Store Manager'),
        ];
    }

    /** Recent adjustments (Adjustments tab). */
    public static function adjustments(): array
    {
        $a = fn (string $ref, string $date, string $sku, string $name, string $type, int $qty, string $note, string $by) => [
            'ref' => $ref, 'date' => $date, 'sku' => $sku, 'name' => $name, 'type' => $type,
            'qty' => ($qty > 0 ? '+' : '−').abs($qty), 'positive' => $qty > 0, 'note' => $note, 'by' => $by,
        ];

        return [
            $a('#ADJ-0024', '3 Oct 2026', 'TSH-0219-NV-XXL', 'Premium Polo T-Shirt · XXL · Navy', 'Correction', 2, 'Count after display change', 'Store Manager'),
            $a('#ADJ-0023', '1 Oct 2026', 'KRT-0882-MR-S', 'Block Print Cotton Kurti · S · Maroon', 'Damaged', -1, 'Colour bleed after trial', 'Sales Staff 01'),
            $a('#ADJ-0022', '28 Sep 2026', 'TPC-0311-PK-M', 'Printed Lawn Three-Piece · M · Pink', 'Return to supplier', -3, 'Print defect, Supplier 05', 'Store Manager'),
            $a('#ADJ-0021', '26 Sep 2026', 'SHT-0457-SB-L', 'Slim Fit Oxford Shirt · L · Sky blue', 'Lost', -1, 'Missing after stock count', 'Store Manager'),
            $a('#ADJ-0020', '24 Sep 2026', 'PNJ-1024-NV-M', 'Embroidered Cotton Panjabi · M · Navy', 'Correction', -2, 'Double entry on #PO-0036', 'Store Manager'),
            $a('#ADJ-0019', '22 Sep 2026', 'PNJ-1024-OW-M', 'Embroidered Cotton Panjabi · M · Off-white', 'Damaged', -1, 'Stain on front', 'Store Manager'),
        ];
    }

    /** Latest movements across all products (Movement history tab). */
    public static function history(): array
    {
        $h = fn (string $when, string $type, string $ref, string $item, int $qty, string $by) => [
            'when' => $when, 'type' => $type, 'tone' => self::typeTone($type), 'ref' => $ref, 'href' => self::refUrl($ref),
            'item' => $item, 'qty' => ($qty > 0 ? '+' : '−').abs($qty), 'positive' => $qty > 0, 'by' => $by,
        ];

        return [
            $h('4 Oct, 2:14 PM', 'Sale-Online', '#WB-10491', 'Slim Fit Oxford Shirt · L · Sky blue', -1, 'System'),
            $h('4 Oct, 1:05 PM', 'Sale-Online', '#WB-10487', 'Stretch Chino Pant · 32 · Khaki', -1, 'System'),
            $h('4 Oct, 12:41 PM', 'Sale-Online', '#WB-10482', 'Embroidered Cotton Panjabi · M · Off-white', -1, 'System'),
            $h('4 Oct, 12:30 PM', 'Sale-POS', '#POS-2291', 'Premium Polo T-Shirt · M · Navy', -1, 'Sales Staff 01'),
            $h('4 Oct, 11:40 AM', 'Sale-POS', '#POS-2290', 'Embroidered Cotton Panjabi · XL · Navy', -1, 'Sales Staff 02'),
            $h('4 Oct, 10:20 AM', 'Stock-in', '#PO-0042', '24 pcs · panjabi, shirts', 24, 'Store Manager'),
            $h('3 Oct, 6:10 PM', 'Adjustment', '#ADJ-0024', 'Premium Polo T-Shirt · XXL · Navy', 2, 'Store Manager'),
            $h('3 Oct, 4:02 PM', 'Return', '#RT-0091', 'Block Print Cotton Kurti · L · Maroon', 1, 'Store Manager'),
        ];
    }

    public static function typeTone(string $type): string
    {
        return match ($type) {
            'Stock-in' => 'green',
            'Sale-POS' => 'blue',
            'Sale-Online' => 'violet',
            'Adjustment' => 'amber',
            default => 'gray',
        };
    }

    /** Where a ledger reference links to (orders that exist in the demo open their detail page). */
    public static function refUrl(string $ref): ?string
    {
        $id = ltrim($ref, '#');

        return match (true) {
            str_starts_with($id, 'PO-') => route('admin.stock-in'),
            str_starts_with($id, 'RT-') => route('admin.returns'),
            str_starts_with($id, 'ADJ-') => null,
            DemoData::findOrder($id) !== null => route('admin.orders.show', $id),
            default => route('admin.orders.index', ['q' => $id]),
        };
    }
}
