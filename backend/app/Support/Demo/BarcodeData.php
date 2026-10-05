<?php

namespace App\Support\Demo;

use App\Support\DemoData;

/**
 * Sample data for Barcode labels (design: Barcodes.dc.html) and the barcode
 * look-ups used by Stock-in and Stock overview.
 */
final class BarcodeData
{
    /** Variants queued for printing in the design (from purchase #PO-0042). */
    public static function queue(): array
    {
        $row = fn (string $id, string $name, string $size, string $colour, string $sku, string $code, int $price, int $stock, int $po, string $img, string $tone) => [
            'id' => $id, 'name' => $name, 'size' => $size, 'colour' => $colour, 'sku' => $sku, 'code' => $code,
            'price' => DemoData::money($price), 'stock' => $stock, 'po' => $po, 'qty' => $po,
            'img' => DemoData::img($img), 'tone' => $tone,
        ];

        return [
            $row('a', 'Embroidered Cotton Panjabi', 'M', 'Off-white', 'PNJ-1024-OW-M', '8901234500127', 2450, 4, 4, 'p_panjabi_offwhite', '#E4DCCF'),
            $row('b', 'Embroidered Cotton Panjabi', 'L', 'Off-white', 'PNJ-1024-OW-L', '8901234500134', 2450, 11, 6, 'p_panjabi_offwhite', '#E4DCCF'),
            $row('c', 'Slim Fit Oxford Shirt', 'L', 'Sky blue', 'SHT-0457-SB-L', '8901234501148', 1650, 3, 3, 'p_shirt_oxford', '#D3DCE4'),
            $row('d', 'Block Print Cotton Kurti', 'M', 'Maroon', 'KRT-0882-MR-M', '8901234502312', 1350, 9, 5, 'p_kurti_block', '#E8D8D6'),
            $row('e', 'Premium Polo T-Shirt', 'M', 'Navy', 'TSH-0219-NV-M', '8901234503517', 890, 22, 6, 'p_polo', '#DCE0D6'),
        ];
    }

    public static function purchases(): array
    {
        return [
            '#PO-0042 · Supplier 03 (Dhaka) · 4 Oct · 24 pcs',
            '#PO-0041 · Supplier 01 (Narayanganj) · 1 Oct · 60 pcs',
            '#PO-0040 · Supplier 05 (Keraniganj) · 28 Sep · 36 pcs',
        ];
    }

    /**
     * Every sellable variant keyed by barcode, for scan / search look-ups.
     * The queued design items win over generated catalogue rows.
     */
    public static function lookup(): array
    {
        $map = [];
        foreach (DemoData::products() as $p) {
            foreach ($p['variants'] as $v) {
                $map[$v['barcode']] = [
                    'name' => $p['name'], 'size' => $v['size'], 'colour' => $v['colour'], 'hex' => ProductData::hex($v['colour']), 'sku' => $v['sku'], 'code' => $v['barcode'],
                    'price' => DemoData::money($v['selling_price']), 'cost' => $p['purchase_price'], 'stock' => $v['stock'],
                    'img' => $p['image_url'], 'tone' => $p['tone'],
                ];
            }
        }
        foreach (self::queue() as $q) {
            $map[$q['code']] = array_merge($map[$q['code']] ?? [], [
                'name' => $q['name'], 'size' => $q['size'], 'colour' => $q['colour'], 'hex' => ProductData::hex($q['colour']), 'sku' => $q['sku'], 'code' => $q['code'],
                'price' => $q['price'], 'stock' => $q['stock'], 'img' => $q['img'], 'tone' => $q['tone'],
            ]);
            $map[$q['code']]['cost'] ??= DemoData::findProduct(implode('-', array_slice(explode('-', $q['sku']), 0, 2)))['purchase_price'] ?? 0;
        }

        return $map;
    }

    /** Find one variant by SKU or barcode. */
    public static function find(string $key): ?array
    {
        foreach (self::lookup() as $code => $v) {
            if ($code === $key || $v['sku'] === $key) {
                return $v;
            }
        }

        return null;
    }
}
