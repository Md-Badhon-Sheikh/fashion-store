<?php

namespace App\Support\Demo;

use App\Support\DemoData;

/**
 * Sample data for Marketing › Flash sale (design: FlashSale.dc.html).
 * Prices are ints (Taka). Replace with FlashSale / FlashSaleItem queries later.
 */
final class FlashSaleData
{
    /**
     * Products that can be put on flash sale, keyed by a short id.
     * reg = regular price, sale = flash price, limit = sale stock cap, sold = sold at sale price,
     * stock = current stock; category + tags are matched by the "add products" search
     * (a three-piece is a kurti + salwar + dupatta set, so it is tagged "kurti").
     */
    public static function products(): array
    {
        $p = fn (string $name, string $sku, string $variant, string $category, int $reg, int $sale, int $limit, int $sold, int $stock, string $img, string $tone) => [
            'name' => $name, 'sku' => $sku, 'variant' => $variant, 'category' => $category,
            'reg' => $reg, 'sale' => $sale, 'limit' => $limit, 'sold' => $sold, 'stock' => $stock,
            'img' => DemoData::img($img), 'tone' => $tone,
            'tags' => str_contains($category, 'Three-Piece') ? 'kurti salwar dupatta set' : '',
        ];

        return [
            'pnj_ow' => $p('Embroidered Cotton Panjabi', 'PNJ-1024', 'All sizes · Off-white', 'Men › Panjabi', 2450, 1790, 60, 41, 46, 'p_panjabi_offwhite', '#E4DCCF'),
            'pnj_linen' => $p('Linen Blend Panjabi', 'PNJ-1031', 'All sizes · Sand', 'Men › Panjabi', 2850, 2190, 40, 33, 52, 'p_panjabi_linen', '#E3DDD2'),
            'pnj_navy' => $p('Classic Navy Panjabi', 'PNJ-1008', 'M, L, XL · Navy', 'Men › Panjabi', 2650, 1990, 50, 18, 64, 'p_panjabi_navy', '#D3D8E0'),
            'pnj_print' => $p('Printed Viscose Panjabi', 'PNJ-1046', 'All sizes · Olive print', 'Men › Panjabi', 2250, 1690, 30, 30, 12, 'p_panjabi_printed', '#DDE0D2'),
            'kids' => $p('Kids Cotton Panjabi Set', 'KPN-0105', 'Age 4–10 · Cream', 'Kids › Panjabi', 1150, 890, 40, 22, 18, 'p_kids_panjabi', '#E8E0CF'),
            'krt_block' => $p('Block Print Cotton Kurti', 'KRT-0882', 'All sizes · Maroon', 'Women › Kurti', 1350, 990, 50, 0, 37, 'p_kurti_block', '#E8D8D6'),
            'krt_linen' => $p('Linen Straight Kurti', 'KRT-0907', 'S, M, L · Mint', 'Women › Kurti', 1550, 1150, 40, 0, 44, 'p_kurti_linen', '#DCE6DF'),
            'tp_lawn' => $p('Printed Lawn Three-Piece', 'TPC-0311', 'Free size · Pink', 'Women › Three-Piece', 2990, 2290, 30, 0, 26, 'p_threepiece_lawn', '#E7D6DA'),
            'tp_geo' => $p('Embroidered Georgette Three-Piece', 'TPC-0342', 'Free size · Teal', 'Women › Three-Piece', 4250, 3390, 20, 0, 21, 'p_threepiece_georgette', '#D6E2E2'),
            'polo' => $p('Premium Polo T-Shirt', 'TSH-0219', 'All sizes · Navy', 'Men › T-Shirts', 890, 650, 80, 0, 128, 'p_polo', '#DCE0D6'),
            'shirt_ox' => $p('Slim Fit Oxford Shirt', 'SHT-0457', 'All sizes · Sky blue', 'Men › Shirts', 1650, 1250, 40, 0, 64, 'p_shirt_oxford', '#D3DCE4'),
        ];
    }

    /**
     * Campaigns. status = running|scheduled|ended.
     * seconds = time left until the end (running) or the start (scheduled) at page load;
     * the page counts it down live.
     */
    public static function campaigns(): array
    {
        $dhms = fn (int $d, int $h, int $m, int $s) => $d * 86400 + $h * 3600 + $m * 60 + $s;

        return [
            ['id' => 'c1', 'name' => 'Eid Panjabi Fest', 'range' => '1 Oct, 10:00 AM – 5 Oct 2026, 11:59 PM', 'start' => '2026-10-01T10:00', 'end' => '2026-10-05T23:59', 'status' => 'running', 'seconds' => $dhms(1, 1, 18, 42), 'ended_label' => '', 'sold' => 144, 'products' => ['pnj_ow', 'pnj_linen', 'pnj_navy', 'pnj_print', 'kids']],
            ['id' => 'c2', 'name' => 'Weekend Kurti Flash', 'range' => '10 Oct, 6:00 PM – 11 Oct 2026, 11:59 PM', 'start' => '2026-10-10T18:00', 'end' => '2026-10-11T23:59', 'status' => 'scheduled', 'seconds' => $dhms(5, 19, 42, 10), 'ended_label' => '', 'sold' => 0, 'products' => ['krt_block', 'krt_linen']],
            ['id' => 'c3', 'name' => 'Puja Three-Piece Sale', 'range' => '18 Oct, 12:00 AM – 22 Oct 2026, 11:59 PM', 'start' => '2026-10-18T00:00', 'end' => '2026-10-22T23:59', 'status' => 'scheduled', 'seconds' => $dhms(13, 1, 42, 10), 'ended_label' => '', 'sold' => 0, 'products' => ['tp_lawn', 'tp_geo']],
            ['id' => 'c4', 'name' => '11.11 Mega Flash', 'range' => '11 Nov, 12:00 AM – 11 Nov 2026, 11:59 PM', 'start' => '2026-11-11T00:00', 'end' => '2026-11-11T23:59', 'status' => 'scheduled', 'seconds' => $dhms(37, 1, 42, 10), 'ended_label' => '', 'sold' => 0, 'products' => ['polo', 'shirt_ox', 'tp_lawn']],
            ['id' => 'c5', 'name' => 'Back to School Kids', 'range' => '1 Sep, 10:00 AM – 3 Sep 2026, 11:59 PM', 'start' => '2026-09-01T10:00', 'end' => '2026-09-03T23:59', 'status' => 'ended', 'seconds' => 0, 'ended_label' => 'Ended 3 Sep', 'sold' => 162, 'products' => ['kids', 'polo']],
            ['id' => 'c6', 'name' => 'Monsoon Polo Deal', 'range' => '15 Aug, 12:00 AM – 17 Aug 2026, 11:59 PM', 'start' => '2026-08-15T00:00', 'end' => '2026-08-17T23:59', 'status' => 'ended', 'seconds' => 0, 'ended_label' => 'Ended 17 Aug', 'sold' => 211, 'products' => ['polo']],
        ];
    }

    /** Banner image used by the campaign editor and the store preview. */
    public static function banner(): array
    {
        return ['img' => DemoData::img('promo1'), 'tone' => '#E9DCCB'];
    }
}
