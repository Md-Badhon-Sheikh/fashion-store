<?php

namespace App\Support\Demo;

use App\Support\DemoData;

/**
 * Sample data for the Customers page (Customers.dc.html).
 *
 * Customer profiles come from DemoData::customers(); this class adds the KPI
 * tiles, the per-customer order history shown in the detail panel and the
 * initial blocked list. Replace with queries (customers + their latest orders)
 * returning the same keys when wiring real data.
 */
final class CustomersData
{
    /** KPI tiles above the list. tone: sub-line colour ('up' = green). */
    public static function stats(): array
    {
        return [
            ['key' => 'total', 'label' => 'Total customers', 'value' => DemoData::number(3846), 'sub' => 'Online '.DemoData::number(2410).' · POS '.DemoData::number(1436), 'sub_tone' => null],
            ['key' => 'new', 'label' => 'New this month', 'value' => '214', 'sub' => '+18% vs September', 'sub_tone' => 'up'],
            ['key' => 'repeat', 'label' => 'Repeat purchase rate', 'value' => '38.6%', 'sub' => 'Customers with 2+ orders', 'sub_tone' => null],
            ['key' => 'blocked', 'label' => 'Blocked', 'value' => (string) self::BLOCKED_TOTAL, 'sub' => 'Mostly refused COD parcels', 'sub_tone' => null],
        ];
    }

    /** Total blocked customers shown in the KPI tile (includes the ones listed below). */
    public const BLOCKED_TOTAL = 12;

    /** Customer ids blocked on page load. */
    public static function blocked(): array
    {
        return ['c06'];
    }

    /** Pagination footer. */
    public static function total(): int
    {
        return 3846;
    }

    /**
     * Rows of the customer list with their detail-panel data.
     * Shape: DemoData::customers() keys + history[{id, date, name, image_url, tone, amount, status, exists}]
     */
    public static function rows(): array
    {
        $history = self::history();
        $rows = [];

        foreach (array_slice(DemoData::customers(), 0, 8) as $c) {
            $c['history'] = $history[$c['id']] ?? [];
            $rows[] = $c;
        }

        return $rows;
    }

    /** Latest orders per customer (detail panel "Order history"). */
    private static function history(): array
    {
        // product key => [name, image key, tone]
        $p = [
            'panjabi' => ['Embroidered Cotton Panjabi', 'p_panjabi_offwhite', '#E4DCCF'],
            'kurti' => ['Block Print Cotton Kurti', 'p_kurti_block', '#E8D8D6'],
            'polo' => ['Premium Polo T-Shirt', 'p_polo', '#DCE0D6'],
            'shirt' => ['Slim Fit Oxford Shirt', 'p_shirt_oxford', '#D3DCE4'],
            'lawn' => ['Printed Lawn Three-Piece', 'p_threepiece_lawn', '#E7D6DA'],
            'kids' => ['Kids Cotton Panjabi Set', 'p_kids_panjabi', '#E8E0CF'],
            'chino' => ['Stretch Cotton Chino', 'p_chino', '#DDD6C8'],
        ];
        $h = function (string $id, string $date, string $product, int $amount, string $status) use ($p) {
            [$name, $img, $tone] = $p[$product];

            return [
                'id' => $id,
                'number' => '#'.$id,
                'date' => $date,
                'name' => $name,
                'image_url' => DemoData::img($img),
                'tone' => $tone,
                'amount' => $amount,
                'status' => $status,
                // Only orders present in the demo data get a detail link.
                'exists' => DemoData::findOrder($id) !== null,
            ];
        };

        return [
            'c01' => [$h('WB-10482', '4 Oct', 'panjabi', 4705, 'processing'), $h('WB-10377', '12 Sep', 'kurti', 2450, 'delivered'), $h('POS-2104', '28 Aug', 'polo', 3890, 'delivered'), $h('WB-10102', '2 Jul', 'lawn', 2990, 'returned')],
            'c02' => [$h('WB-10491', '4 Oct', 'shirt', 3240, 'pending')],
            'c03' => [$h('WB-10487', '4 Oct', 'chino', 2840, 'confirmed'), $h('POS-2187', '19 Sep', 'panjabi', 4900, 'delivered'), $h('POS-2033', '21 Aug', 'kids', 2300, 'delivered')],
            'c04' => [$h('PH-0217', '4 Oct', 'lawn', 5980, 'pending'), $h('WB-10476', '3 Oct', 'kurti', 5140, 'shipped'), $h('WB-10211', '9 Jul', 'polo', 1780, 'delivered')],
            'c05' => [$h('FB-0388', '3 Oct', 'shirt', 2190, 'shipped'), $h('WB-10302', '22 Aug', 'polo', 1890, 'delivered')],
            'c06' => [$h('WB-10451', '30 Sep', 'panjabi', 2450, 'returned'), $h('WB-10318', '27 Aug', 'chino', 1990, 'cancelled'), $h('WB-10150', '14 Jun', 'shirt', 2460, 'delivered')],
            'c07' => [$h('WB-10470', '2 Oct', 'kurti', 3560, 'delivered'), $h('POS-2150', '6 Sep', 'lawn', 2990, 'delivered')],
            'c08' => [$h('POS-2244', '28 Sep', 'kids', 1150, 'delivered')],
        ];
    }
}
