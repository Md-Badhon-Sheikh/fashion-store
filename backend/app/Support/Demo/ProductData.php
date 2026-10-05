<?php

namespace App\Support\Demo;

use App\Support\DemoData;

/**
 * Sample data for the Products list and the Add / Edit product form
 * (designs: Products.dc.html, ProductForm.dc.html).
 *
 * Builds on DemoData::products(); replace with Eloquent queries that return
 * the same shapes when wiring real data.
 */
final class ProductData
{
    /** Standard clothing sizes offered by the variant generator. */
    public const CLOTHING_SIZES = ['S', 'M', 'L', 'XL', 'XXL'];

    /** Colour name => swatch hex (shared by Products, ProductForm, Inventory, Stock-in). */
    public static function colourHex(): array
    {
        return [
            'Off-white' => '#F1ECE1', 'White' => '#FFFFFF', 'Black' => '#1C1C1E', 'Navy' => '#1F2A44',
            'Olive' => '#5D6B3A', 'Maroon' => '#6B1F2A', 'Sky blue' => '#9CC3E4', 'Pink' => '#E7A9B9',
            'Mustard' => '#C99A2E', 'Indigo' => '#3B4A7A', 'Green' => '#3E7A4E', 'Cream' => '#EFE6D0',
            'Mint' => '#BFE0CF', 'Sand' => '#D9C9A8', 'Rose' => '#D99AA5', 'Blue check' => '#8FA9C9',
            'Beige' => '#D9CBB0', 'Lilac' => '#C8B6D9', 'Red' => '#B83A3A', 'Lavender' => '#C9B8E0',
            'Yellow' => '#E8C547', 'Khaki' => '#B5A27A',
        ];
    }

    public static function hex(string $colour): string
    {
        return self::colourHex()[$colour] ?? '#E5E7EA';
    }

    /* =====================================================================
     * Products list
     * ===================================================================== */

    /** Stock-status tabs with all-catalogue counts. */
    public static function statusTabs(): array
    {
        return [
            'all' => ['label' => 'All', 'count' => 412],
            'available' => ['label' => 'Available', 'count' => 376],
            'low-stock' => ['label' => 'Low stock', 'count' => 23],
            'out-of-stock' => ['label' => 'Out of stock', 'count' => 9],
            'hidden' => ['label' => 'Hidden', 'count' => 4],
        ];
    }

    /** Filter selects: key => [label, options (value => text)]. Empty value = no filter. */
    public static function listFilters(): array
    {
        $colours = [];
        $fabrics = [];
        foreach (DemoData::adminProducts() as $p) {
            $colours = array_merge($colours, $p['colours']);
            $fabrics[] = $p['fabric'];
        }
        $colours = array_values(array_unique($colours));
        $fabrics = array_values(array_unique($fabrics));
        sort($fabrics);

        return [
            'category' => ['label' => 'Category', 'options' => ['' => 'All categories', 'men' => 'Men', 'women' => 'Women', 'kids' => 'Kids']],
            'brand' => ['label' => 'Brand', 'options' => ['' => 'All brands', 'YOUR BRAND' => 'YOUR BRAND', 'Partner brand' => 'Partner brand']],
            'size' => ['label' => 'Size', 'options' => ['' => 'Any size'] + array_combine(self::CLOTHING_SIZES, self::CLOTHING_SIZES)],
            'colour' => ['label' => 'Colour', 'options' => ['' => 'Any colour'] + array_combine($colours, $colours)],
            'status' => ['label' => 'Stock status', 'options' => ['' => 'Any status', 'available' => 'Available', 'low-stock' => 'Low stock', 'out-of-stock' => 'Out of stock']],
            'fabric' => ['label' => 'Fabric', 'options' => ['' => 'Any fabric'] + array_combine($fabrics, $fabrics)],
            'added' => ['label' => 'Added between', 'options' => ['d90' => 'Last 90 days', 'd30' => 'Last 30 days', 'year' => 'This year', 'all' => 'All time']],
        ];
    }

    /**
     * Rows for the Products table: the six admin products with their variants
     * grouped by colour (for the expandable variant matrix).
     */
    public static function listRows(): array
    {
        return array_map(function (array $p) {
            $groups = [];
            foreach ($p['variants'] as $v) {
                $code = $v['colour_code'];
                $groups[$code] ??= ['code' => $code, 'name' => $v['colour'], 'hex' => self::hex($v['colour']), 'variants' => []];
                $groups[$code]['variants'][] = [
                    'size' => $v['size'],
                    'sku' => $v['sku'],
                    'barcode' => $v['barcode'],
                    'selling_price' => $v['selling_price'],
                    'stock' => $v['stock'],
                ];
            }

            return $p + [
                'main' => explode('/', $p['category_slug'])[0],
                'variant_count' => count($p['variants']),
                'colour_groups' => array_values($groups),
            ];
        }, DemoData::adminProducts());
    }

    /** Lightweight per-row metadata the Alpine filters work on. */
    public static function listMeta(array $rows): array
    {
        $meta = [];
        foreach ($rows as $r) {
            $meta[$r['id']] = [
                'main' => $r['main'],
                'brand' => $r['brand'],
                'sizes' => $r['sizes'],
                'colours' => $r['colours'],
                'status' => $r['status'],
                'fabric' => $r['fabric'],
                'hidden' => $r['visibility'] === 'Hidden',
            ];
        }

        return $meta;
    }

    /* =====================================================================
     * Add / edit product form
     * ===================================================================== */

    /** Sub-category slug => SKU prefix (product code is "<PREFIX>-<number>"). */
    public static function skuPrefixes(): array
    {
        return [
            'men/panjabi' => 'PNJ', 'men/shirts' => 'SHT', 'men/t-shirts-polo' => 'TSH', 'men/pants-chino' => 'PNT', 'men/pajama' => 'PJM',
            'women/kurti' => 'KRT', 'women/three-piece' => 'TPC', 'women/salwar-palazzo' => 'SLW', 'women/dupatta' => 'DUP',
            'kids/boys-panjabi' => 'KPN', 'kids/girls' => 'KGF', 'kids/t-shirts' => 'KTS',
        ];
    }

    /** Next free product number per SKU prefix, e.g. ['PNJ' => 1056, …]. */
    public static function nextSkuNumbers(): array
    {
        $next = [];
        foreach (self::skuPrefixes() as $prefix) {
            $next[$prefix] = 1001;
        }
        foreach (DemoData::products() as $p) {
            [$prefix, $num] = array_pad(explode('-', $p['sku'], 2), 2, '0');
            $next[$prefix] = max($next[$prefix] ?? 1001, (int) $num + 1);
        }

        return $next;
    }

    public static function fabrics(): array
    {
        return ['100% Cotton', 'Linen blend', 'Lawn', 'Georgette', 'Silk blend'];
    }

    public static function sizeCharts(): array
    {
        return ["Men's Panjabi (inch)", "Men's Shirt (inch)", "Women's Kurti (inch)", 'Kids (age)', 'None'];
    }

    /** Main categories (with their sub-categories) offered in the form selects. */
    public static function categoryTree(): array
    {
        $tree = [];
        foreach (DemoData::categories() as $c) {
            if ($c['level'] === 0) {
                $tree[$c['id']] = ['id' => $c['id'], 'name' => $c['name'], 'slug' => $c['slug'], 'subs' => []];
            }
        }
        foreach (DemoData::categories() as $c) {
            if ($c['level'] === 1 && isset($tree[$c['parent_id']])) {
                $tree[$c['parent_id']]['subs'][] = ['name' => $c['name'], 'slug' => $c['slug']];
            }
        }

        // The product form only offers main categories that have sub-categories.
        return array_values(array_filter($tree, fn ($m) => $m['subs'] !== []));
    }

    /**
     * Initial Alpine state for the product form.
     * $product null => "Add product" (empty fields, sensible defaults).
     */
    public static function formState(?array $product): array
    {
        $prefixes = self::skuPrefixes();
        $next = self::nextSkuNumbers();
        $fabrics = self::fabrics();

        if ($product === null) {
            $sub = 'men/panjabi';

            return [
                'mode' => 'create',
                'productId' => count(DemoData::products()) + 1,
                'name' => '',
                'main' => 'men',
                'sub' => $sub,
                'brand' => 'YOUR BRAND',
                'sku' => $prefixes[$sub].'-'.$next[$prefixes[$sub]],
                'fabric' => $fabrics[0],
                'fabrics' => $fabrics,
                'shortDesc' => '',
                'longDesc' => '',
                'care' => 'Machine wash cold with similar colours. Do not bleach. Iron on reverse at medium heat. Dry in shade.',
                'video' => '',
                'purchase' => '',
                'selling' => '',
                'sale' => '',
                'sizeOptions' => self::CLOTHING_SIZES,
                'sizes' => self::CLOTHING_SIZES,
                'colourOptions' => self::colourOptions([]),
                'colours' => [],
                'existing' => (object) [],
                'photos' => [],
                'status' => 'Published',
                'visibility' => 'Online + POS',
                'labels' => ['featured' => false, 'newArrival' => true, 'best' => false],
                'sizeChart' => self::sizeCharts()[0],
                'related' => [],
                'slug' => '',
                'slugTouched' => false,
                'metaTitle' => '',
                'metaTitleTouched' => false,
                'metaDesc' => '',
            ];
        }

        $isDesignProduct = $product['sku'] === 'PNJ-1024';
        $sub = $product['category_slug'];
        $fabric = $product['fabric'] === 'Cotton' ? '100% Cotton' : $product['fabric'];
        if (! in_array($fabric, $fabrics, true)) {
            $fabrics[] = $fabric;
        }

        $colourCodes = [];
        $existing = [];
        foreach ($product['variants'] as $v) {
            $colourCodes[$v['colour']] = $v['colour_code'];
            $existing[$v['colour_code'].'-'.$v['size']] = [
                'barcode' => $v['barcode'],
                'selling' => $v['selling_price'],
                'stock' => $v['stock'],
            ];
        }

        $allClothing = array_diff($product['sizes'], self::CLOTHING_SIZES) === [];

        return [
            'mode' => 'edit',
            'productId' => $product['id'],
            'name' => $product['name'],
            'main' => explode('/', $sub)[0],
            'sub' => $sub,
            'brand' => $product['brand'],
            'sku' => $product['sku'],
            'fabric' => $fabric,
            'fabrics' => $fabrics,
            'shortDesc' => $isDesignProduct
                ? 'Fine machine embroidery on the placket, soft breathable cotton for Eid and everyday wear.'
                : $product['name'].' in '.strtolower($product['fabric']).', made for everyday comfort.',
            'longDesc' => $isDesignProduct
                ? "A classic regular-fit panjabi in soft combed cotton with tone-on-tone embroidery along the placket and cuffs.\n\n• Regular fit, mid-thigh length\n• Hidden button placket, side pockets\n• Model is 5'10\" and wears size M"
                : $product['description'],
            'care' => 'Machine wash cold with similar colours. Do not bleach. Iron on reverse at medium heat. Dry in shade.',
            'video' => $isDesignProduct ? 'https://youtube.com/watch?v=[VIDEO-ID]' : '',
            'purchase' => DemoData::number($product['purchase_price']),
            'selling' => DemoData::number($product['selling_price']),
            'sale' => $product['sale_price'] ? DemoData::number($product['sale_price']) : '',
            'sizeOptions' => $allClothing ? self::CLOTHING_SIZES : $product['sizes'],
            'sizes' => $product['sizes'],
            'colourOptions' => self::colourOptions($colourCodes),
            'colours' => array_values($colourCodes),
            'existing' => $existing,
            'photos' => self::photos($product),
            'status' => 'Published',
            'visibility' => $product['visibility'],
            'labels' => ['featured' => $isDesignProduct, 'newArrival' => $isDesignProduct, 'best' => false],
            'sizeChart' => self::defaultSizeChart($sub),
            'related' => self::related($product),
            'slug' => $product['slug'],
            'slugTouched' => true,
            'metaTitle' => $product['name'].($isDesignProduct ? ' for Men' : '').' | YOUR BRAND',
            'metaTitleTouched' => true,
            'metaDesc' => 'Shop the '.$product['name'].' in '.self::humanList($product['colours']).'. '
                .ucfirst(strtolower($product['fabric'])).', sizes '.reset($product['sizes']).' to '.end($product['sizes'])
                .'. Cash on delivery all over Bangladesh.',
        ];
    }

    /**
     * Colour chips for the generator: the product's own colours first, then
     * the common palette until at least four chips are shown.
     *
     * @param  array<string, string>  $own  colour name => code
     */
    private static function colourOptions(array $own): array
    {
        $palette = ['Off-white' => 'OW', 'Navy' => 'NV', 'Olive' => 'OL', 'Maroon' => 'MR'];
        $options = [];
        foreach ($own as $name => $code) {
            $options[] = ['code' => $code, 'name' => $name, 'hex' => self::hex($name)];
        }
        foreach ($palette as $name => $code) {
            if (count($options) >= 4) {
                break;
            }
            if (! isset($own[$name]) && ! in_array($code, $own, true)) {
                $options[] = ['code' => $code, 'name' => $name, 'hex' => self::hex($name)];
            }
        }

        return $options;
    }

    private static function photos(array $product): array
    {
        if ($product['sku'] === 'PNJ-1024') {
            return [
                ['title' => 'Front', 'colour' => 'Off-white', 'hex' => '#F1ECE1', 'alt' => 'Embroidered Cotton Panjabi, off-white, front', 'src' => DemoData::img('p_panjabi_offwhite'), 'tone' => '#E4DCCF'],
                ['title' => 'Navy', 'colour' => 'Navy', 'hex' => '#1F2A44', 'alt' => 'Embroidered Cotton Panjabi, navy', 'src' => DemoData::img('p_panjabi_navy'), 'tone' => '#D3D8E2'],
                ['title' => 'Olive', 'colour' => 'Olive', 'hex' => '#5D6B3A', 'alt' => 'Embroidered Cotton Panjabi, olive', 'src' => DemoData::img('p_panjabi_olive'), 'tone' => '#DCDFCF'],
                ['title' => 'Detail', 'colour' => 'Off-white', 'hex' => '#F1ECE1', 'alt' => 'Embroidery detail on placket', 'src' => DemoData::img('p_panjabi_linen'), 'tone' => '#E3DDD2'],
            ];
        }
        $colour = $product['colours'][0] ?? '';

        return [[
            'title' => 'Front', 'colour' => $colour, 'hex' => self::hex($colour),
            'alt' => $product['name'].', '.strtolower($colour).', front', 'src' => $product['image_url'], 'tone' => $product['tone'],
        ]];
    }

    private static function related(array $product): array
    {
        if ($product['sku'] === 'PNJ-1024') {
            return [
                ['name' => 'Cotton Pajama', 'sku' => 'PJM-0140', 'price' => '৳750', 'img' => DemoData::img('p_pajama'), 'tone' => '#EDEBE6'],
                ['name' => 'Linen Blend Panjabi', 'sku' => 'PNJ-1031', 'price' => '৳2,650', 'img' => DemoData::img('p_panjabi_linen'), 'tone' => '#E3DDD2'],
                ['name' => 'Kids Cotton Panjabi Set', 'sku' => 'KPN-0105', 'price' => '৳1,150', 'img' => DemoData::img('p_kids_panjabi'), 'tone' => '#E8E0CF'],
            ];
        }
        $main = explode('/', $product['category_slug'])[0];
        $out = [];
        foreach (self::relatedPool() as $r) {
            if ($r['main'] === $main && $r['sku'] !== $product['sku'] && count($out) < 2) {
                $out[] = $r;
            }
        }

        return $out;
    }

    /** All products as small search results for "Related products". */
    public static function relatedPool(): array
    {
        return array_map(fn (array $p) => [
            'name' => $p['name'],
            'sku' => $p['sku'],
            'price' => DemoData::money($p['price']),
            'img' => $p['image_url'],
            'tone' => $p['tone'],
            'main' => explode('/', $p['category_slug'])[0],
        ], DemoData::products());
    }

    private static function defaultSizeChart(string $sub): string
    {
        return match (true) {
            str_starts_with($sub, 'kids') => 'Kids (age)',
            str_starts_with($sub, 'women') => "Women's Kurti (inch)",
            $sub === 'men/shirts' || $sub === 'men/t-shirts-polo' => "Men's Shirt (inch)",
            default => "Men's Panjabi (inch)",
        };
    }

    /** ['Off-white', 'Navy', 'Maroon'] => "Off-white, Navy and Maroon". */
    private static function humanList(array $items): string
    {
        if (count($items) < 2) {
            return (string) ($items[0] ?? '');
        }
        $last = array_pop($items);

        return implode(', ', $items).' and '.$last;
    }
}
