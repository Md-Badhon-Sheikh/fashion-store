<?php

namespace App\Support;

/**
 * Static sample data for the admin panel design export.
 *
 * Every method returns plain arrays so views never depend on a database.
 * When wiring real data, replace a method body with an Eloquent query that
 * returns the same shape (or switch the controller to the model directly).
 *
 * Page agents: ADD methods below the "Page data" marker; never delete or
 * change the shape of existing methods, other pages depend on them.
 *
 * Conventions:
 * - Money is stored as int (Taka). Format with DemoData::money(4705) => "৳4,705".
 * - Status values are lowercase slugs ("processing", "low-stock") and are
 *   rendered with <x-admin.status-chip :status="..."/>.
 * - Images are keys of /public/images/<key>.jpg; use DemoData::img($key).
 */
final class DemoData
{
    /* =====================================================================
     * Formatting helpers
     * ===================================================================== */

    /** 214600 => "2,14,600" (Indian / en-IN digit grouping). */
    public static function number(int|float $n): string
    {
        $neg = $n < 0;
        $s = (string) (int) round(abs($n));

        if (strlen($s) > 3) {
            $last3 = substr($s, -3);
            $rest = substr($s, 0, -3);
            $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
            $s = $rest.','.$last3;
        }

        return ($neg ? '−' : '').$s;
    }

    /** 214600 => "৳2,14,600"; -515 => "−৳515". */
    public static function money(int|float $n): string
    {
        return ($n < 0 ? '−' : '').'৳'.self::number(abs($n));
    }

    /** Public URL of a design image key, e.g. img('p_polo') => "/images/p_polo.jpg". */
    public static function img(?string $key): ?string
    {
        return $key ? '/images/'.$key.'.jpg' : null;
    }

    /** Store-front URL (React app) used for "View online store" links. */
    public static function storeUrl(): string
    {
        return rtrim((string) config('app.frontend_url', 'http://localhost:5173'), '/');
    }

    /* =====================================================================
     * Shell / navigation
     * ===================================================================== */

    /** Sidebar badges (unread / waiting counts). */
    public static function navBadges(): array
    {
        return [
            'orders' => 12,
            'returns' => 3,
            'reviews' => 5,
            'notifications' => 4,
        ];
    }

    /** Signed-in staff member shown in the topbar and drawer. */
    public static function currentUser(): array
    {
        return [
            'name' => 'Admin',
            'role' => 'Super Admin',
            'initials' => 'SA',
            'permissions' => 'all permissions',
            'counter' => 'Counter 1',
            'channel' => 'Online store + POS',
            'last_login' => 'Last login today, 10:02 AM',
            'version' => 'v1.0',
            'phone_masked' => '017•••••120',
        ];
    }

    /* =====================================================================
     * Catalog
     * ===================================================================== */

    /**
     * All products with their size/colour variants.
     *
     * Shape of one product:
     *  id, slug, name, sku, category, category_slug, brand, fabric,
     *  purchase_price, selling_price, sale_price|null, compare_at_price|null,
     *  price (current store price), stock, status, visibility, image, image_url,
     *  tone, variants_label, sizes[], colours[], low_stock_threshold, added_on,
     *  description, variants[] (size, colour, colour_code, sku, barcode,
     *  selling_price, stock, status)
     */
    public static function products(): array
    {
        // [id, slug, name, sku, category, category_slug, brand, fabric, purchase, selling, sale, compare, status, visibility, image, tone, variants_label, added_on]
        // then: sizes, colours [name => code], stock matrix [colourCode => [stock per size]]
        $defs = [
            [1, 'embroidered-cotton-panjabi', 'Embroidered Cotton Panjabi', 'PNJ-1024', 'Men › Panjabi', 'men/panjabi', 'YOUR BRAND', 'Cotton', 1420, 2450, 1890, 2450, 'low-stock', 'Online + POS', 'p_panjabi_offwhite', '#E4DCCF', '15 (5 sizes × 3 colours)', '2026-08-12',
                ['S', 'M', 'L', 'XL', 'XXL'], ['Off-white' => 'OW', 'Navy' => 'NV', 'Maroon' => 'MR'],
                ['OW' => [6, 4, 11, 2, 0], 'NV' => [3, 4, 5, 2, 1], 'MR' => [1, 2, 3, 1, 1]]],
            [2, 'slim-fit-oxford-shirt', 'Slim Fit Oxford Shirt', 'SHT-0457', 'Men › Shirts', 'men/shirts', 'YOUR BRAND', 'Cotton oxford', 890, 1650, 1190, 1650, 'available', 'Online + POS', 'p_shirt_oxford', '#D3DCE4', '8 (4 × 2)', '2026-07-30',
                ['S', 'M', 'L', 'XL'], ['Sky blue' => 'SB', 'White' => 'WH'],
                ['SB' => [8, 10, 3, 9], 'WH' => [7, 12, 9, 6]]],
            [3, 'block-print-cotton-kurti', 'Block Print Cotton Kurti', 'KRT-0882', 'Women › Kurti', 'women/kurti', 'YOUR BRAND', 'Cotton', 720, 1350, null, 1590, 'available', 'Online + POS', 'p_kurti_block', '#E8D8D6', '12 (4 × 3)', '2026-08-02',
                ['S', 'M', 'L', 'XL'], ['Maroon' => 'MR', 'Indigo' => 'IN', 'Mustard' => 'MS'],
                ['MR' => [4, 6, 5, 3], 'IN' => [3, 4, 4, 2], 'MS' => [1, 2, 2, 1]]],
            [4, 'printed-lawn-three-piece', 'Printed Lawn Three-Piece', 'TPC-0311', 'Women › Three-Piece', 'women/three-piece', 'Partner brand', 'Lawn', 1600, 2990, 2290, 2990, 'out-of-stock', 'Online + POS', 'p_threepiece_lawn', '#E7D6DA', '6 (3 × 2)', '2026-06-18',
                ['S', 'M', 'L'], ['Pink' => 'PK', 'Green' => 'GR'],
                ['PK' => [0, 0, 0], 'GR' => [0, 0, 0]]],
            [5, 'premium-polo-t-shirt', 'Premium Polo T-Shirt', 'TSH-0219', 'Men › T-Shirts', 'men/t-shirts-polo', 'YOUR BRAND', 'Pique cotton', 420, 890, null, null, 'available', 'Online + POS', 'p_polo', '#DCE0D6', '15 (5 × 3)', '2026-05-22',
                ['S', 'M', 'L', 'XL', 'XXL'], ['Navy' => 'NV', 'White' => 'WH', 'Olive' => 'OL'],
                ['NV' => [10, 14, 12, 9, 5], 'WH' => [9, 12, 11, 8, 6], 'OL' => [6, 8, 8, 6, 4]]],
            [6, 'kids-cotton-panjabi-set', 'Kids Cotton Panjabi Set', 'KPN-0105', 'Kids', 'kids/boys-panjabi', 'YOUR BRAND', 'Cotton', 610, 1150, null, 1350, 'available', 'POS only', 'p_kids_panjabi', '#E8E0CF', '4 (4 × 1)', '2026-09-01',
                ['2-3Y', '4-5Y', '6-7Y', '8-9Y'], ['Cream' => 'CR'],
                ['CR' => [4, 6, 5, 3]]],

            // Store catalogue (Main / Shop designs) — simpler stock matrices.
            [7, 'navy-slim-fit-panjabi', 'Navy Slim Fit Panjabi', 'PNJ-1031', 'Men › Panjabi', 'men/panjabi', 'YOUR BRAND', 'Cotton', 1280, 2250, null, null, 'available', 'Online + POS', 'p_panjabi_navy', '#C9D0D8', '5 (5 × 1)', '2026-08-20',
                ['S', 'M', 'L', 'XL', 'XXL'], ['Navy' => 'NV'], ['NV' => [6, 9, 8, 5, 3]]],
            [8, 'mint-cotton-panjabi', 'Mint Cotton Panjabi', 'PNJ-1036', 'Men › Panjabi', 'men/panjabi', 'YOUR BRAND', 'Cotton', 1150, 2350, 1990, 2350, 'available', 'Online + POS', 'p_panjabi_olive', '#D5D9C5', '5 (5 × 1)', '2026-08-24',
                ['S', 'M', 'L', 'XL', 'XXL'], ['Mint' => 'MN'], ['MN' => [5, 8, 9, 6, 2]]],
            [9, 'linen-blend-panjabi', 'Linen Blend Panjabi', 'PNJ-1042', 'Men › Panjabi', 'men/panjabi', 'YOUR BRAND', 'Linen blend', 1520, 2650, null, null, 'available', 'Online + POS', 'p_panjabi_linen', '#E3DDD2', '5 (5 × 1)', '2026-09-05',
                ['S', 'M', 'L', 'XL', 'XXL'], ['Sand' => 'SD'], ['SD' => [4, 7, 7, 5, 3]]],
            [10, 'printed-festive-panjabi', 'Printed Festive Panjabi', 'PNJ-1048', 'Men › Panjabi', 'men/panjabi', 'YOUR BRAND', 'Cotton', 1210, 2690, 2150, 2690, 'out-of-stock', 'Online + POS', 'p_panjabi_printed', '#E6D3D3', '5 (5 × 1)', '2026-07-11',
                ['S', 'M', 'L', 'XL', 'XXL'], ['Rose' => 'RS'], ['RS' => [0, 0, 0, 0, 0]]],
            [11, 'panjabi-pajama-set', 'Panjabi-Pajama Set', 'PNJ-1055', 'Men › Pajama', 'men/pajama', 'YOUR BRAND', 'Cotton', 1690, 3290, 2890, 3290, 'available', 'Online + POS', 'p_pajama', '#EDEBE6', '4 (4 × 1)', '2026-09-12',
                ['M', 'L', 'XL', 'XXL'], ['White' => 'WH'], ['WH' => [6, 8, 6, 3]]],
            [12, 'printed-casual-shirt', 'Printed Casual Shirt', 'SHT-0463', 'Men › Shirts', 'men/shirts', 'YOUR BRAND', 'Cotton', 760, 1450, null, null, 'available', 'Online + POS', 'p_shirt_check', '#D6DEE6', '4 (4 × 1)', '2026-08-15',
                ['S', 'M', 'L', 'XL'], ['Blue check' => 'BC'], ['BC' => [7, 11, 10, 6]]],
            [13, 'navy-twill-shirt', 'Navy Twill Shirt', 'SHT-0470', 'Men › Shirts', 'men/shirts', 'YOUR BRAND', 'Cotton twill', 820, 1790, 1550, 1790, 'available', 'Online + POS', 'cat_shirt', '#CFD8DF', '4 (4 × 1)', '2026-08-28',
                ['S', 'M', 'L', 'XL'], ['Navy' => 'NV'], ['NV' => [5, 9, 9, 4]]],
            [14, 'basic-crew-neck-t-shirt', 'Basic Crew Neck T-Shirt', 'TSH-0225', 'Men › T-Shirts', 'men/t-shirts-polo', 'YOUR BRAND', 'Cotton jersey', 190, 550, 390, 550, 'available', 'Online + POS', 'p_tshirt_basic', '#DADFD3', '5 (5 × 1)', '2026-06-02',
                ['S', 'M', 'L', 'XL', 'XXL'], ['Olive' => 'OL'], ['OL' => [14, 22, 20, 12, 8]]],
            [15, 'essential-white-tee', 'Essential White Tee', 'TSH-0231', 'Men › T-Shirts', 'men/t-shirts-polo', 'YOUR BRAND', 'Cotton jersey', 210, 450, null, null, 'out-of-stock', 'Online + POS', 'cat_tshirt', '#E4E6E1', '5 (5 × 1)', '2026-05-10',
                ['S', 'M', 'L', 'XL', 'XXL'], ['White' => 'WH'], ['WH' => [0, 0, 0, 0, 0]]],
            [16, 'solid-linen-long-kurti', 'Solid Linen Long Kurti', 'KRT-0890', 'Women › Kurti', 'women/kurti', 'YOUR BRAND', 'Linen', 860, 1650, null, null, 'available', 'Online + POS', 'p_kurti_linen', '#E2DCD3', '4 (4 × 1)', '2026-08-09',
                ['S', 'M', 'L', 'XL'], ['Beige' => 'BG'], ['BG' => [6, 9, 8, 4]]],
            [17, 'embroidered-maroon-kurti', 'Embroidered Maroon Kurti', 'KRT-0895', 'Women › Kurti', 'women/kurti', 'YOUR BRAND', 'Cotton', 790, 1850, 1490, 1850, 'available', 'Online + POS', 'cat_kurti', '#E6D3D3', '4 (4 × 1)', '2026-08-30',
                ['S', 'M', 'L', 'XL'], ['Maroon' => 'MR'], ['MR' => [5, 8, 7, 3]]],
            [18, 'embroidered-georgette-set', 'Embroidered Georgette Set', 'TPC-0320', 'Women › Three-Piece', 'women/three-piece', 'Partner brand', 'Georgette', 2150, 3850, null, null, 'available', 'Online + POS', 'p_threepiece_georgette', '#E0D9E6', '3 (3 × 1)', '2026-09-08',
                ['S', 'M', 'L'], ['Lilac' => 'LL'], ['LL' => [4, 6, 5]]],
            [19, 'red-cotton-three-piece', 'Red Cotton Three-Piece', 'TPC-0326', 'Women › Three-Piece', 'women/three-piece', 'YOUR BRAND', 'Cotton', 1310, 2790, 2490, 2790, 'out-of-stock', 'Online + POS', 'cat_threepiece', '#E9D2D2', '3 (3 × 1)', '2026-07-03',
                ['S', 'M', 'L'], ['Red' => 'RD'], ['RD' => [0, 0, 0]]],
            [20, 'lavender-festive-kurti', 'Lavender Festive Kurti', 'KRT-0901', 'Women › Kurti', 'women/kurti', 'YOUR BRAND', 'Rayon', 940, 1990, null, null, 'available', 'Online + POS', 'hero2', '#E3DDEA', '4 (4 × 1)', '2026-09-20',
                ['S', 'M', 'L', 'XL'], ['Lavender' => 'LV'], ['LV' => [6, 8, 7, 5]]],
            [21, 'girls-festive-frock', 'Girls Festive Frock', 'KGF-0112', 'Kids', 'kids/girls', 'YOUR BRAND', 'Cotton satin', 590, 1290, null, null, 'available', 'Online + POS', 'cat_kids', '#EDE3C8', '4 (4 × 1)', '2026-09-15',
                ['2-3Y', '4-5Y', '6-7Y', '8-9Y'], ['Yellow' => 'YL'], ['YL' => [5, 7, 6, 4]]],
            [22, 'stretch-chino-pant', 'Stretch Chino Pant', 'PNT-0601', 'Men › Pants & Chino', 'men/pants-chino', 'YOUR BRAND', 'Stretch cotton', 780, 1790, 1550, 1790, 'available', 'Online + POS', 'p_chino', '#DDD8CC', '5 (5 × 1)', '2026-07-21',
                ['30', '32', '34', '36', '38'], ['Khaki' => 'KH'], ['KH' => [6, 10, 9, 6, 3]]],
            [23, 'slim-fit-khaki-chino', 'Slim Fit Khaki Chino', 'PNT-0607', 'Men › Pants & Chino', 'men/pants-chino', 'YOUR BRAND', 'Stretch cotton', 810, 1650, null, null, 'out-of-stock', 'Online + POS', 'cat_pant', '#E0D9C9', '5 (5 × 1)', '2026-06-25',
                ['30', '32', '34', '36', '38'], ['Khaki' => 'KH'], ['KH' => [0, 0, 0, 0, 0]]],
        ];

        // Exact barcodes shown in the Products design (PNJ-1024 Off-white).
        $knownBarcodes = [
            'PNJ-1024-OW-S' => '8901234500110',
            'PNJ-1024-OW-M' => '8901234500127',
            'PNJ-1024-OW-L' => '8901234500134',
            'PNJ-1024-OW-XL' => '8901234500141',
            'PNJ-1024-OW-XXL' => '8901234500158',
        ];

        $products = [];
        foreach ($defs as $d) {
            [$id, $slug, $name, $sku, $category, $categorySlug, $brand, $fabric, $purchase, $selling, $sale, $compare,
                $status, $visibility, $image, $tone, $variantsLabel, $addedOn, $sizes, $colours, $matrix] = $d;

            $variants = [];
            $vi = 0;
            foreach ($colours as $colour => $code) {
                foreach ($sizes as $si => $size) {
                    $vi++;
                    $vSku = $sku.'-'.$code.'-'.$size;
                    $stock = $matrix[$code][$si] ?? 0;
                    $variants[] = [
                        'size' => $size,
                        'colour' => $colour,
                        'colour_code' => $code,
                        'sku' => $vSku,
                        'barcode' => $knownBarcodes[$vSku] ?? sprintf('89012%03d%04d1', $id, $vi),
                        'selling_price' => $selling,
                        'stock' => $stock,
                        'status' => self::stockStatus($stock),
                    ];
                }
            }

            $products[] = [
                'id' => $id,
                'slug' => $slug,
                'name' => $name,
                'sku' => $sku,
                'category' => $category,
                'category_slug' => $categorySlug,
                'brand' => $brand,
                'fabric' => $fabric,
                'purchase_price' => $purchase,
                'selling_price' => $selling,
                'sale_price' => $sale,
                'compare_at_price' => $compare,
                'price' => $sale ?? $selling,
                'stock' => array_sum(array_column($variants, 'stock')),
                'status' => $status,
                'visibility' => $visibility,
                'image' => $image,
                'image_url' => self::img($image),
                'tone' => $tone,
                'variants_label' => $variantsLabel,
                'sizes' => $sizes,
                'colours' => array_keys($colours),
                'low_stock_threshold' => 5,
                'added_on' => $addedOn,
                'description' => $name.' in '.strtolower($fabric).'. Each size/colour variant has its own SKU and barcode.',
                'variants' => $variants,
            ];
        }

        return $products;
    }

    /** The six products shown on the admin Products list (Products.dc.html). */
    public static function adminProducts(): array
    {
        return array_slice(self::products(), 0, 6);
    }

    /** Find by numeric id or slug or SKU; null when missing. */
    public static function findProduct(int|string $key): ?array
    {
        foreach (self::products() as $p) {
            if ((string) $p['id'] === (string) $key || $p['slug'] === $key || $p['sku'] === $key) {
                return $p;
            }
        }

        return null;
    }

    /** available / low-stock / out-of-stock for a variant stock count. */
    public static function stockStatus(int $stock, int $threshold = 5): string
    {
        return $stock === 0 ? 'out-of-stock' : ($stock <= $threshold ? 'low-stock' : 'available');
    }

    /** Category tree from Categories.dc.html (level 0 = main, 1 = sub). */
    public static function categories(): array
    {
        $rows = [
            ['men', 'Men', 'men', 186, 0, 'cat_panjabi', '#E4DCCF', null],
            ['pnj', 'Panjabi', 'men/panjabi', 64, 1, 'cat_panjabi', '#E3DDD2', 'men'],
            ['sht', 'Shirts', 'men/shirts', 48, 1, 'cat_shirt', '#D3DCE4', 'men'],
            ['tsh', 'T-Shirts & Polo', 'men/t-shirts-polo', 41, 1, 'cat_tshirt', '#DCE0D6', 'men'],
            ['pnt', 'Pants & Chino', 'men/pants-chino', 21, 1, 'cat_pant', '#DDD8CC', 'men'],
            ['pjm', 'Pajama', 'men/pajama', 12, 1, 'p_pajama', '#EDEBE6', 'men'],
            ['women', 'Women', 'women', 142, 0, 'cat_kurti', '#E8D8D6', null],
            ['krt', 'Kurti', 'women/kurti', 58, 1, 'cat_kurti', '#E8D8D6', 'women'],
            ['tpc', 'Three-Piece', 'women/three-piece', 52, 1, 'cat_threepiece', '#E7D6DA', 'women'],
            ['slw', 'Salwar & Palazzo', 'women/salwar-palazzo', 18, 1, 'p_threepiece_georgette', '#E2D9E3', 'women'],
            ['dup', 'Dupatta & Orna', 'women/dupatta', 14, 1, 'p_threepiece_lawn', '#EADFD3', 'women'],
            ['kids', 'Kids', 'kids', 56, 0, 'cat_kids', '#E8E0CF', null],
            ['kbp', 'Boys Panjabi', 'kids/boys-panjabi', 22, 1, 'p_kids_panjabi', '#E8E0CF', 'kids'],
            ['kgf', 'Girls Frock & Kurti', 'kids/girls', 19, 1, 'cat_kids', '#EAD9DD', 'kids'],
            ['kts', 'Kids T-Shirts', 'kids/t-shirts', 15, 1, 'p_tshirt_basic', '#DCE0D6', 'kids'],
            ['eid', 'Eid Collection 2026', 'eid-collection-2026', 28, 0, 'p_panjabi_printed', '#E6DFC9', null],
            ['acc', 'Accessories', 'accessories', 0, 0, null, '#E5E7EA', null],
        ];

        return array_map(fn (array $r) => [
            'id' => $r[0],
            'name' => $r[1],
            'slug' => $r[2],
            'product_count' => $r[3],
            'level' => $r[4],
            'image' => $r[5],
            'image_url' => self::img($r[5]),
            'tone' => $r[6],
            'parent_id' => $r[7],
            'status' => 'active',
            'show_in_menu' => true,
        ], $rows);
    }

    /* =====================================================================
     * Customers
     * ===================================================================== */

    /** Customers from Customers.dc.html (+ two referenced by orders). */
    public static function customers(): array
    {
        $c = fn (string $id, string $name, string $ini, string $since, string $phoneMasked, string $email, int $orders, int $spent, int $avg, int $returns, string $last, string $src, string $acct, array $addrs, array $notes = [], string $status = 'active') => [
            'id' => $id,
            'name' => $name,
            'initials' => $ini,
            'since' => $since,
            'phone_masked' => $phoneMasked,
            'phone' => self::unmaskPhone($phoneMasked),
            'email' => $email,
            'orders' => $orders,
            'spent' => $spent,
            'avg_order' => $avg,
            'returns' => $returns,
            'last_order' => $last,
            'source' => $src,
            'account' => $acct,
            'addresses' => $addrs,
            'notes' => $notes,
            'status' => $status,
        ];
        $a = fn (string $label, string $tag, string $line) => ['label' => $label, 'tag' => $tag, 'line' => $line];
        $n = fn (string $text, string $by) => ['text' => $text, 'by' => $by];

        return [
            $c('c01', '[Customer 01]', 'C1', 'Mar 2025', '017•••••678', 'customer01@email.com', 6, 18450, 3075, 1, '4 Oct 2026', 'Online', 'Registered',
                [$a('Home', 'Default', 'House 12, Road 5, Block C, Mirpur, Dhaka 1216'), $a('Office', 'Used 2 times', 'Level 6, [ADDRESS], Motijheel, Dhaka 1000')],
                [$n('Prefers a call before delivery. Size L in Panjabi, M in shirts.', 'Sales Staff 01 · 12 Sep 2026')]),
            $c('c02', '[Customer 02]', 'C2', 'Oct 2026', '019•••••204', 'No email', 1, 3240, 3240, 0, '4 Oct 2026', 'Online', 'Guest checkout',
                [$a('Home', 'Default', 'Road 7A, Dhanmondi, Dhaka 1209')],
                [$n('Ordered via Facebook page message. First order.', 'Sales Staff 02 · 4 Oct 2026')]),
            $c('c03', '[Customer 03]', 'C3', 'Nov 2024', '015•••••903', 'customer03@email.com', 11, 32780, 2980, 0, '4 Oct 2026', 'POS', 'POS profile',
                [$a('Home', 'Default', 'Section 10, Mirpur, Dhaka 1216')],
                [$n('Regular in-store customer, buys kids items for Eid.', 'Store Manager · 21 Aug 2026')]),
            $c('c04', '[Customer 04]', 'C4', 'Jan 2026', '016•••••317', 'customer04@email.com', 4, 14260, 3565, 0, '4 Oct 2026', 'Online', 'Registered',
                [$a('Home', 'Default', 'Zindabazar, Sylhet 3100'), $a('Parents', 'Used 1 time', 'Agrabad C/A, Chattogram 4100')]),
            $c('c05', '[Customer 05]', 'C5', 'Aug 2026', '013•••••842', 'No email', 2, 4080, 2040, 0, '3 Oct 2026', 'Online', 'Guest checkout',
                [$a('Home', 'Default', 'Sonadanga, Khulna 9100')]),
            $c('c06', '[Customer 06]', 'C6', 'May 2026', '018•••••226', 'customer06@email.com', 3, 6900, 2300, 2, '30 Sep 2026', 'Online', 'Registered',
                [$a('Home', 'Default', 'Block D, Bashundhara R/A, Dhaka 1229')],
                [$n('Refused 2 COD parcels at the door. Blocked for COD; prepaid orders allowed.', 'Store Manager · 1 Oct 2026')]),
            $c('c07', '[Customer 07]', 'C7', 'Feb 2025', '017•••••095', 'customer07@email.com', 8, 21340, 2668, 0, '2 Oct 2026', 'POS', 'POS profile',
                [$a('Home', 'Default', 'Road 41, Gulshan 2, Dhaka 1212')]),
            $c('c08', '[Customer 08]', 'C8', 'Sep 2026', '011•••••512', 'No email', 1, 1150, 1150, 0, '28 Sep 2026', 'POS', 'POS profile',
                [$a('No address', 'In-store only', 'Walk-in purchase, phone saved at counter')]),
            $c('c09', '[Customer 09]', 'C9', 'Jun 2026', '018•••••551', 'customer09@email.com', 3, 7480, 2493, 0, '4 Oct 2026', 'Online', 'Registered',
                [$a('Home', 'Default', 'Sector 7, Uttara, Dhaka 1230')]),
            $c('c10', '[Customer 10]', 'C10', 'Apr 2026', '019•••••730', 'No email', 2, 3420, 1710, 0, '2 Oct 2026', 'Online', 'Guest checkout',
                [$a('Home', 'Default', 'Shaheb Bazar, Rajshahi 6100')]),
        ];
    }

    public static function findCustomer(string $id): ?array
    {
        foreach (self::customers() as $c) {
            if ($c['id'] === $id) {
                return $c;
            }
        }

        return null;
    }

    /** "017•••••678" => "01712-345678" (sample full number for detail views). */
    private static function unmaskPhone(string $masked): string
    {
        $prefix = mb_substr($masked, 0, 3);
        $suffix = mb_substr($masked, -3);

        return $prefix.'12-345'.$suffix;
    }

    /* =====================================================================
     * Orders
     * ===================================================================== */

    /**
     * All orders (Dashboard + Orders + OrderDetail designs), newest first.
     *
     * Shape: id ("WB-10482", used in URLs), number ("#WB-10482"), date, time,
     * placed_at (ISO), customer{id,name,phone,phone_masked,email,area,address,
     * account,orders,spent}, items_count, items[{name,variant,sku,price,qty,total,
     * image,image_url,tone}], source, payment_method, payment_status, courier,
     * tracking, subtotal, discount, coupon, delivery_fee, delivery_zone, amount,
     * due, status, steps[{name,state(done|now|todo),time}], activity[{what,who,when}],
     * notifications[{channel,text,when,state}], note, counter, staff
     */
    public static function orders(): array
    {
        $p = [];
        foreach (self::products() as $prod) {
            $p[$prod['sku']] = $prod;
        }
        $item = function (string $sku, string $size, string $colour, int $price, int $qty) use ($p) {
            $prod = $p[$sku];
            $code = self::colourCode($prod, $colour);

            return [
                'product_id' => $prod['id'],
                'name' => $prod['name'],
                'variant' => 'Size '.$size.' · '.$colour,
                'size' => $size,
                'colour' => $colour,
                'sku' => $sku.'-'.$code.'-'.$size,
                'price' => $price,
                'qty' => $qty,
                'total' => $price * $qty,
                'image' => $prod['image'],
                'image_url' => $prod['image_url'],
                'tone' => $prod['tone'],
            ];
        };

        // id, date, time, iso, customerId|null, area, source, method, payStatus, courier, tracking, amount, status, items, deliveryFee, deliveryZone, coupon[code, amount]|null
        $defs = [
            ['WB-10491', '4 Oct 2026', '2:14 PM', '2026-10-04T14:14', 'c02', 'Dhanmondi, Dhaka', 'Facebook', 'COD', 'Unpaid', 'Not assigned', '—', 3240, 'pending',
                [$item('SHT-0457', 'L', 'Sky blue', 1650, 1), $item('SHT-0463', 'M', 'Blue check', 1520, 1)], 70, 'Inside Dhaka', null],
            ['WB-10490', '4 Oct 2026', '1:58 PM', '2026-10-04T13:58', 'c09', 'Uttara, Dhaka', 'Online', 'bKash', 'Paid', 'Not assigned', '—', 1650, 'pending',
                [$item('KRT-0890', 'M', 'Beige', 1580, 1)], 70, 'Inside Dhaka', null],
            ['PH-0217', '4 Oct 2026', '1:40 PM', '2026-10-04T13:40', 'c04', 'Agrabad, Chattogram', 'Phone', 'COD', 'Unpaid', 'Not assigned', '—', 5980, 'pending',
                [$item('TPC-0311', 'L', 'Pink', 2990, 1), $item('KRT-0890', 'L', 'Beige', 1650, 1), $item('KRT-0882', 'M', 'Indigo', 1210, 1)], 130, 'Outside Dhaka', null],
            ['WB-10487', '4 Oct 2026', '1:05 PM', '2026-10-04T13:05', 'c03', 'Mirpur, Dhaka', 'Online', 'Nagad', 'Paid', 'Pathao Courier', 'Booking pending', 2840, 'confirmed',
                [$item('PNT-0601', '32', 'Khaki', 1550, 1), $item('KPN-0105', '6-7Y', 'Cream', 1220, 1)], 70, 'Inside Dhaka', null],
            ['WB-10482', '4 Oct 2026', '12:41 PM', '2026-10-04T12:41', 'c01', 'Mirpur, Dhaka', 'Online', 'COD', 'Unpaid', 'Steadfast', 'SF-58213049', 4705, 'processing',
                [$item('PNJ-1024', 'M', 'Off-white', 2450, 1), $item('KRT-0882', 'L', 'Maroon', 1350, 2)], 70, 'Inside Dhaka', ['EID10', 515]],
            ['POS-2291', '4 Oct 2026', '12:30 PM', '2026-10-04T12:30', null, 'Counter 1 · Sales Staff 01', 'POS', 'Cash + bKash', 'Paid', 'In-store sale', '—', 6870, 'delivered',
                [$item('PNJ-1024', 'L', 'Navy', 2450, 1), $item('TPC-0320', 'M', 'Lilac', 2990, 1), $item('TSH-0219', 'M', 'Navy', 890, 1), $item('TSH-0225', 'L', 'Olive', 540, 1)], 0, 'In-store', null],
            ['WB-10481', '4 Oct 2026', '12:12 PM', '2026-10-04T12:12', 'c02', 'Dhanmondi, Dhaka', 'Facebook', 'COD', 'Unpaid', 'Not assigned', '—', 1890, 'pending',
                [$item('PNJ-1036', 'L', 'Mint', 1820, 1)], 70, 'Inside Dhaka', null],
            ['WB-10480', '4 Oct 2026', '11:58 AM', '2026-10-04T11:58', 'c09', 'Uttara, Dhaka', 'Online', 'COD', 'Unpaid', 'Not assigned', '—', 3250, 'confirmed',
                [$item('KRT-0890', 'S', 'Beige', 1650, 1), $item('KRT-0895', 'M', 'Maroon', 1530, 1)], 70, 'Inside Dhaka', null],
            ['POS-2290', '4 Oct 2026', '11:40 AM', '2026-10-04T11:40', 'c03', 'Counter 1 · Sales Staff 02', 'POS', 'Card', 'Paid', 'In-store sale', '—', 2450, 'delivered',
                [$item('PNJ-1024', 'XL', 'Navy', 2450, 1)], 0, 'In-store', null],
            ['WB-10476', '3 Oct 2026', '10:05 PM', '2026-10-03T22:05', 'c04', 'Sylhet Sadar', 'Online', 'COD', 'Unpaid', 'Steadfast', 'SF-58204417', 5140, 'shipped',
                [$item('TPC-0320', 'M', 'Lilac', 3850, 1), $item('KRT-0882', 'S', 'Mustard', 1160, 1)], 130, 'Outside Dhaka', null],
            ['FB-0388', '3 Oct 2026', '6:22 PM', '2026-10-03T18:22', 'c05', 'Khulna Sadar', 'Facebook', 'COD', 'Unpaid', 'RedX', 'RX-22918034', 2190, 'shipped',
                [$item('PNJ-1031', 'L', 'Navy', 2060, 1)], 130, 'Outside Dhaka', null],
            ['WB-10470', '2 Oct 2026', '4:47 PM', '2026-10-02T16:47', 'c07', 'Gulshan, Dhaka', 'Online', 'COD', 'COD collected', 'Pathao Courier', 'PT-7781204', 3560, 'delivered',
                [$item('KRT-0895', 'L', 'Maroon', 1500, 1), $item('KRT-0901', 'M', 'Lavender', 1990, 1)], 70, 'Inside Dhaka', null],
            ['WB-10466', '2 Oct 2026', '11:16 AM', '2026-10-02T11:16', 'c10', 'Rajshahi Sadar', 'Online', 'bKash', 'Refunded', 'Not assigned', '—', 1890, 'cancelled',
                [$item('PNT-0601', '34', 'Khaki', 1760, 1)], 130, 'Outside Dhaka', null],
            ['WB-10451', '30 Sep 2026', '8:09 PM', '2026-09-30T20:09', 'c06', 'Bashundhara, Dhaka', 'Online', 'COD', 'Paid', 'Steadfast', 'SF-58117702', 2450, 'returned',
                [$item('PNJ-1024', 'L', 'Off-white', 2380, 1)], 70, 'Inside Dhaka', null],
        ];

        $customers = [];
        foreach (self::customers() as $cust) {
            $customers[$cust['id']] = $cust;
        }

        $orders = [];
        foreach ($defs as $d) {
            [$id, $date, $time, $iso, $custId, $area, $source, $method, $payStatus, $courier, $tracking, $amount, $status, $items, $fee, $zone, $coupon] = $d;

            $subtotal = array_sum(array_column($items, 'total'));
            $couponAmount = $coupon[1] ?? 0;
            $discount = $subtotal + $fee - $couponAmount - $amount; // any extra manual discount
            $cust = $custId ? $customers[$custId] : null;
            $address = $cust ? $cust['addresses'][0]['line'] : 'Walk-in customer';
            if ($id === 'WB-10482') {
                $address = "House 12, Road 5, Block C\nMirpur, Dhaka 1216";
            }

            $orders[] = [
                'id' => $id,
                'number' => '#'.$id,
                'date' => $date,
                'time' => $time,
                'placed_at' => $iso,
                'customer' => [
                    'id' => $custId,
                    'name' => $cust['name'] ?? 'Walk-in',
                    'phone' => $cust['phone'] ?? null,
                    'phone_masked' => $cust['phone_masked'] ?? 'Walk-in',
                    'email' => $cust['email'] ?? null,
                    'area' => $area,
                    'address' => $address,
                    'account' => $cust['account'] ?? 'Walk-in',
                    'orders' => $cust['orders'] ?? null,
                    'spent' => $cust['spent'] ?? null,
                ],
                'items_count' => array_sum(array_column($items, 'qty')),
                'items' => $items,
                'source' => $source,
                'payment_method' => $method,
                'payment_method_label' => $method === 'COD' ? 'Cash on Delivery' : $method,
                'payment_status' => $payStatus,
                'courier' => $courier,
                'tracking' => $tracking,
                'subtotal' => $subtotal,
                'coupon' => $coupon ? ['code' => $coupon[0], 'amount' => $coupon[1]] : null,
                'discount' => max(0, $discount),
                'delivery_fee' => $fee,
                'delivery_zone' => $zone,
                'amount' => $amount,
                'due' => in_array($payStatus, ['Unpaid'], true) ? $amount : 0,
                'status' => $status,
                'steps' => self::orderSteps($status, $time, $source),
                'activity' => self::orderActivity($id, $status, $source, $date, $time, $coupon),
                'notifications' => self::orderNotifications($id, $amount, $status, $source),
                'note' => $id === 'WB-10482' ? ['text' => 'Customer asked to call before delivery.', 'by' => 'Sales Staff 01'] : null,
                'courier_charge' => $source === 'POS' ? 0 : ($zone === 'Outside Dhaka' ? 120 : 60),
                'courier_settlement' => $payStatus === 'COD collected' ? 'Received' : 'Not received yet',
            ];
        }

        return $orders;
    }

    public static function findOrder(string $id): ?array
    {
        $id = ltrim(strtoupper($id), '#');
        foreach (self::orders() as $o) {
            if ($o['id'] === $id) {
                return $o;
            }
        }

        return null;
    }

    /** Status tab counts on the Orders page (all-time). */
    public static function orderTabCounts(): array
    {
        return [
            'all' => 1412,
            'pending' => 12,
            'confirmed' => 8,
            'processing' => 6,
            'shipped' => 21,
            'delivered' => 1298,
            'cancelled' => 41,
            'returned' => 26,
        ];
    }

    private static function colourCode(array $product, string $colour): string
    {
        foreach ($product['variants'] as $v) {
            if ($v['colour'] === $colour) {
                return $v['colour_code'];
            }
        }

        return 'XX';
    }

    private static function orderSteps(string $status, string $placedTime, string $source): array
    {
        $flow = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
        $times = $status === 'processing'
            ? ['12:41 PM', '12:58 PM', '1:20 PM', '—', '—'] // OrderDetail design (#WB-10482)
            : [$placedTime, '+20 min', '+1 h', 'Next day', '2–3 days'];

        if ($source === 'POS') {
            return array_map(fn ($name) => ['name' => ucfirst($name), 'state' => 'done', 'time' => $placedTime], $flow);
        }

        $pos = array_search($status, $flow, true);
        if ($pos === false) { // cancelled / returned: show progress up to where it stopped
            $pos = $status === 'returned' ? 4 : 0;
        }

        $steps = [];
        foreach ($flow as $i => $name) {
            $state = $i < $pos ? 'done' : ($i === $pos ? ($status === 'delivered' || $status === 'returned' ? 'done' : 'now') : 'todo');
            $steps[] = ['name' => ucfirst($name), 'state' => $state, 'time' => $state === 'todo' ? '—' : $times[$i]];
        }

        return $steps;
    }

    private static function orderActivity(string $id, string $status, string $source, string $date, string $time, ?array $coupon): array
    {
        if ($id === 'WB-10482') {
            return [
                ['what' => 'Status changed to Processing · stock reserved', 'who' => 'Sales Staff 01', 'when' => '4 Oct, 1:20 PM'],
                ['what' => 'Order confirmed by phone call', 'who' => 'Sales Staff 01', 'when' => '4 Oct, 12:58 PM'],
                ['what' => 'Coupon EID10 applied (−৳515)', 'who' => 'Customer', 'when' => '4 Oct, 12:41 PM'],
                ['what' => 'Order placed on website', 'who' => 'Customer', 'when' => '4 Oct, 12:41 PM'],
            ];
        }

        $short = preg_replace('/ \d{4}$/', '', $date).', '.$time;
        $placed = match ($source) {
            'POS' => 'Sold at POS counter',
            'Facebook' => 'Order entered from Facebook message',
            'Phone' => 'Order taken by phone',
            default => 'Order placed on website',
        };
        $rows = [['what' => $placed, 'who' => $source === 'POS' || $source === 'Phone' || $source === 'Facebook' ? 'Sales Staff 01' : 'Customer', 'when' => $short]];
        if ($status !== 'pending' && $source !== 'POS') {
            array_unshift($rows, ['what' => 'Status changed to '.Status::label($status), 'who' => 'Sales Staff 01', 'when' => $short]);
        }

        return $rows;
    }

    private static function orderNotifications(string $id, int $amount, string $status, string $source): array
    {
        if ($source === 'POS') {
            return [['channel' => 'SMS', 'text' => 'Thank you for shopping at YOUR BRAND. Receipt #'.$id.', total '.self::money($amount).'.', 'when' => 'At sale', 'state' => 'Delivered']];
        }
        $rows = [['channel' => 'SMS', 'text' => 'We received your order #'.$id.'. We will call to confirm.', 'when' => 'On order', 'state' => 'Delivered']];
        if ($status !== 'pending') {
            array_unshift($rows,
                ['channel' => 'SMS', 'text' => 'Your order #'.$id.' is confirmed. Total '.self::money($amount).' (COD).', 'when' => 'On confirm', 'state' => 'Delivered'],
                ['channel' => 'Email', 'text' => 'Order confirmation with invoice PDF', 'when' => 'On confirm', 'state' => 'Sent'],
            );
        }
        if ($id === 'WB-10482') {
            return [
                ['channel' => 'SMS', 'text' => 'Your order #WB-10482 is confirmed. Total ৳4,705 (COD).', 'when' => '12:58 PM', 'state' => 'Delivered'],
                ['channel' => 'Email', 'text' => 'Order confirmation with invoice PDF', 'when' => '12:58 PM', 'state' => 'Sent'],
                ['channel' => 'SMS', 'text' => 'We received your order #WB-10482. We will call to confirm.', 'when' => '12:41 PM', 'state' => 'Delivered'],
            ];
        }

        return $rows;
    }

    /* =====================================================================
     * Dashboard
     * ===================================================================== */

    /** Date-range options for the dashboard segmented control. */
    public static function dashboardRanges(): array
    {
        return ['today' => 'Today', 'd7' => '7 days', 'd30' => '30 days'];
    }

    /**
     * KPI tiles. Range-dependent tiles have array values keyed by range
     * (today|d7|d30); static tiles have plain strings.
     * 'show' = all | desktop | mobile (the phone artboard shows a different set).
     * 'tone' = default | warn | pending | danger | success.
     */
    public static function kpis(): array
    {
        return [
            [
                'key' => 'sales', 'show' => 'all', 'tone' => 'default',
                'label' => ['today' => "Today's sales", 'd7' => 'Sales, last 7 days', 'd30' => 'Sales, last 30 days'],
                'value' => ['today' => self::money(48250), 'd7' => self::money(360250), 'd30' => self::money(1482600)],
                'sub' => [
                    'today' => 'Online '.self::money(31400).' · POS '.self::money(16850),
                    'd7' => 'Online '.self::money(232400).' · POS '.self::money(127850),
                    'd30' => 'Online '.self::money(961300).' · POS '.self::money(521300),
                ],
            ],
            [
                'key' => 'orders', 'show' => 'all', 'tone' => 'default',
                'label' => ['today' => "Today's orders", 'd7' => 'Orders, last 7 days', 'd30' => 'Orders, last 30 days'],
                'value' => ['today' => '37', 'd7' => '268', 'd30' => self::number(1094)],
                'sub' => ['today' => '12 pending confirmation', 'd7' => '+11% vs previous 7 days', 'd30' => '+6% vs previous 30 days'],
            ],
            [
                'key' => 'month', 'show' => 'desktop', 'tone' => 'default',
                'label' => 'Total sales, October', 'value' => self::money(214600), 'sub' => '+8% vs same days in Sep',
            ],
            [
                'key' => 'aov', 'show' => 'mobile', 'tone' => 'default',
                'label' => 'Avg. order value',
                'value' => ['today' => self::money(1304), 'd7' => self::money(1344), 'd30' => self::money(1355)],
                'sub' => ['today' => '+3% vs yesterday', 'd7' => '+2% vs previous 7 days', 'd30' => 'Flat vs previous 30 days'],
            ],
            [
                'key' => 'products', 'show' => 'desktop', 'tone' => 'default',
                'label' => 'Total products', 'value' => '412', 'sub' => self::number(1986).' size/colour variants',
            ],
            [
                'key' => 'pending', 'show' => 'mobile', 'tone' => 'pending',
                'label' => 'Pending confirmation', 'value' => '12', 'sub' => 'Oldest waiting 2 h 10 min',
            ],
            [
                'key' => 'stock', 'show' => 'all', 'tone' => 'default',
                'label' => 'Total stock', 'value' => self::number(8734).' pcs', 'sub' => 'Stock value ৳64.2 lakh (cost)',
            ],
            [
                'key' => 'low', 'show' => 'all', 'tone' => 'warn',
                'label' => 'Low stock / Out of stock', 'value' => '23 / 9', 'sub' => 'Variants below threshold',
            ],
        ];
    }

    /**
     * Daily sales by channel for the last 14 days, values in ৳ thousand.
     * Each: date (ISO), day ("21"), month ("Sep"), short ("21 Sep"), online, pos, total.
     */
    public static function salesLast14Days(): array
    {
        $online = [22, 25, 19, 28, 31, 26, 24, 30, 35, 29, 33, 38, 36, 31.4];
        $pos = [12, 14, 11, 15, 18, 16, 13, 17, 19, 15, 18, 22, 20, 16.85];
        $days = ['21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '1', '2', '3', '4'];
        $months = ['Sep', 'Sep', 'Sep', 'Sep', 'Sep', 'Sep', 'Sep', 'Sep', 'Sep', 'Sep', 'Oct', 'Oct', 'Oct', 'Oct'];

        $out = [];
        foreach ($online as $i => $o) {
            $mm = $months[$i] === 'Sep' ? '09' : '10';
            $out[] = [
                'date' => sprintf('2026-%s-%02d', $mm, (int) $days[$i]),
                'day' => $days[$i],
                'month' => $months[$i],
                'short' => $days[$i].' '.$months[$i],
                'online' => $o,
                'pos' => $pos[$i],
                'total' => round($o + $pos[$i], 2),
            ];
        }

        return $out;
    }

    /** Orders-by-status counts shown on the dashboard (today). */
    public static function dashboardStatusCounts(): array
    {
        return [
            'pending' => 12,
            'confirmed' => 8,
            'processing' => 6,
            'shipped' => 21,
            'delivered' => 15,
            'cancelled' => 2,
            'returned' => 3,
        ];
    }

    /**
     * "Recent sales & orders" rows on the dashboard.
     * POS sales show as "Completed" instead of "Delivered".
     */
    public static function recentOrders(int $limit = 6): array
    {
        $ids = ['WB-10482', 'POS-2291', 'WB-10481', 'WB-10480', 'POS-2290', 'WB-10476'];
        $rows = [];
        foreach (array_slice($ids, 0, $limit) as $id) {
            $o = self::findOrder($id);
            $rows[] = $o + [
                'display_status' => $o['source'] === 'POS' && $o['status'] === 'delivered' ? 'completed' : $o['status'],
                'customer_label' => $o['customer']['phone_masked'],
            ];
        }

        return $rows;
    }

    /** Low / out-of-stock variants (Dashboard "Stock alerts"). */
    public static function lowStockAlerts(): array
    {
        $a = fn (string $name, string $variant, string $sku, int $stock) => [
            'name' => $name,
            'variant' => $variant,
            'sku' => $sku,
            'stock' => $stock,
            'status' => self::stockStatus($stock),
            'label' => $stock === 0 ? 'Out of stock' : 'Low · '.$stock.' left',
        ];

        return [
            $a('Embroidered Cotton Panjabi', 'XXL · Off-white', 'PNJ-1024-OW-XXL', 0),
            $a('Printed Lawn Three-Piece', 'M · Pink', 'TPC-0311-PK-M', 0),
            $a('Embroidered Cotton Panjabi', 'XL · Off-white', 'PNJ-1024-OW-XL', 2),
            $a('Slim Fit Oxford Shirt', 'L · Sky blue', 'SHT-0457-SB-L', 3),
            $a('Block Print Cotton Kurti', 'S · Maroon', 'KRT-0882-MR-S', 4),
            $a('Premium Polo T-Shirt', 'XXL · Navy', 'TSH-0219-NV-XXL', 5),
        ];
    }

    /** Total number of stock alerts ("View all 32 alerts"). */
    public static function stockAlertTotal(): int
    {
        return 32;
    }

    /** Top selling products this month; pct = width relative to #1. */
    public static function topProducts(): array
    {
        $rows = [
            ['Embroidered Cotton Panjabi', 184],
            ['Premium Polo T-Shirt', 162],
            ['Block Print Cotton Kurti', 131],
            ['Slim Fit Oxford Shirt', 98],
            ['Printed Lawn Three-Piece', 76],
        ];
        $max = $rows[0][1];

        return array_map(fn ($r) => ['name' => $r[0], 'units' => $r[1], 'pct' => (int) round($r[1] / $max * 100)], $rows);
    }

    /* =====================================================================
     * Page data — page agents add their methods below this line.
     * ===================================================================== */
}
