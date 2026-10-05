<?php

namespace App\Support\Demo;

use App\Support\DemoData;

/**
 * Sample data for the POS counter (designs: POS.dc.html, T-POS.dc.html).
 *
 * Every product is one sellable size/colour variant with its own barcode,
 * which is what a barcode scanner returns at the counter. When wiring real
 * data, return the same shape from a variants query (joined with products
 * and stock) and keep the keys used by the Alpine POS component:
 * id, name, variant, sku, code, price, stock, cat, tone, img.
 */
final class PosData
{
    /** Register / counter header (top bar of the POS). */
    public static function register(): array
    {
        return [
            'counter' => 'Counter 1',
            'cashier' => 'Sales Staff 02',
            'opened_at' => '10:02 AM',
            'next_receipt' => 'POS-2292',
            'merchant_number' => '[PHONE]',
        ];
    }

    /** Category chips, in display order ("All" first). */
    public static function categories(): array
    {
        return ['All', 'Panjabi', 'Shirts', 'T-Shirts', 'Kurti', 'Three-piece', 'Pants', 'Kids'];
    }

    /** Sellable variants shown in the product grid and matched by the scanner. */
    public static function catalog(): array
    {
        // id, name, variant, sku, barcode, price, stock, category, tone, image key
        $rows = [
            ['p1', 'Embroidered Cotton Panjabi', 'M · Off-white', 'PNJ-1024-OW-M', '8901234500127', 2450, 4, 'Panjabi', '#E4DCCF', 'p_panjabi_offwhite'],
            ['p2', 'Linen Blend Panjabi', 'L · Off-white', 'PNJ-1042-SD-L', '8901234504101', 2650, 7, 'Panjabi', '#E3DDD2', 'p_panjabi_linen'],
            ['p3', 'Navy Cotton Panjabi', 'L · Navy', 'PNJ-1031-NV-L', '8901234509668', 2350, 11, 'Panjabi', '#D5D9E0', 'p_panjabi_navy'],
            ['s1', 'Slim Fit Oxford Shirt', 'L · Sky blue', 'SHT-0457-SB-L', '8901234501148', 1650, 3, 'Shirts', '#D3DCE4', 'p_shirt_oxford'],
            ['s2', 'Check Casual Shirt', 'M · Blue', 'SHT-0463-BC-M', '8901234510774', 1450, 12, 'Shirts', '#D8DDE6', 'p_shirt_check'],
            ['t1', 'Premium Polo T-Shirt', 'M · Navy', 'TSH-0219-NV-M', '8901234503517', 890, 22, 'T-Shirts', '#DCE0D6', 'p_polo'],
            ['t2', 'Basic Crew Neck T-Shirt', 'M · Black', 'TSH-0225-BK-M', '8901234507442', 550, 31, 'T-Shirts', '#D7D9DC', 'p_tshirt_basic'],
            ['k1', 'Block Print Cotton Kurti', 'M · Maroon', 'KRT-0882-MR-M', '8901234502312', 1350, 9, 'Kurti', '#E8D8D6', 'p_kurti_block'],
            ['k2', 'Printed Lawn Three-Piece', 'L · Green', 'TPC-0311-GR-L', '8901234511887', 2850, 6, 'Three-piece', '#DDE6D6', 'p_threepiece_lawn'],
            ['c1', 'Stretch Chino Pant', '32 · Khaki', 'PNT-0601-KH-32', '8901234506339', 1550, 6, 'Pants', '#DDD8CC', 'p_chino'],
            ['j1', 'Cotton Pajama', 'L · White', 'PNJ-1055-WH-L', '8901234505226', 750, 18, 'Pants', '#EDEBE6', 'p_pajama'],
            ['d1', 'Kids Cotton Panjabi Set', '6Y · Off-white', 'KPN-0105-CR-6Y', '8901234508555', 1150, 5, 'Kids', '#E8E0CF', 'p_kids_panjabi'],
        ];

        return array_map(fn (array $r) => [
            'id' => $r[0],
            'name' => $r[1],
            'variant' => $r[2],
            'sku' => $r[3],
            'code' => $r[4],
            'price' => $r[5],
            'stock' => $r[6],
            'cat' => $r[7],
            'tone' => $r[8],
            'img' => DemoData::img($r[9]),
        ], $rows);
    }

    /** Cart lines already scanned when the screen opens (design state). */
    public static function initialCart(): array
    {
        return [
            ['id' => 'p1', 'qty' => 1],
            ['id' => 's1', 'qty' => 1],
            ['id' => 'k1', 'qty' => 2],
        ];
    }

    /** Id of the "Last scanned" product. */
    public static function lastScanned(): string
    {
        return 'k1';
    }

    /** Opening discount on the current sale (design: flat ৳200). */
    public static function initialDiscount(): array
    {
        return ['type' => 'amount', 'value' => 200, 'coupon' => null];
    }

    /** Coupons the counter can apply (static lookup). type: amount | percent. */
    public static function coupons(): array
    {
        return [
            'EID10' => ['type' => 'percent', 'value' => 10, 'label' => '10% off'],
            'FLAT500' => ['type' => 'amount', 'value' => 500, 'label' => '৳500 off'],
            'WELCOME200' => ['type' => 'amount', 'value' => 200, 'label' => '৳200 off'],
        ];
    }

    /** Sales parked with "Hold sale" earlier today ("Held sales (2)"). */
    public static function heldSales(): array
    {
        return [
            ['ref' => 'HOLD-01', 'time' => '11:48 AM', 'phone' => '01912-345204', 'lines' => [['id' => 't1', 'qty' => 2], ['id' => 'c1', 'qty' => 1]]],
            ['ref' => 'HOLD-02', 'time' => '12:15 PM', 'phone' => '', 'lines' => [['id' => 'k2', 'qty' => 1]]],
        ];
    }

    /**
     * Customer phone lookup (static). Keys are digits only, e.g. "01712345678".
     * Built from DemoData::customers() so the counter recognises known customers.
     */
    public static function customerLookup(): array
    {
        $map = [];
        foreach (DemoData::customers() as $c) {
            $digits = preg_replace('/\D/', '', $c['phone']);
            $map[$digits] = [
                'name' => $c['name'],
                'orders' => $c['orders'],
                'account' => $c['account'] === 'Registered' ? 'Registered customer' : $c['account'],
            ];
        }

        return $map;
    }

    /** Phone pre-filled in the customer field (design). */
    public static function initialPhone(): string
    {
        return '01712-345678';
    }

    /** Sample completed POS sale whose receipt opens after "Complete sale". */
    public static function sampleReceiptOrder(): string
    {
        return 'POS-2291';
    }
}
