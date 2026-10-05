<?php

namespace App\Support\Demo;

use App\Support\DemoData;

/**
 * Sample data for Categories & brands (design: Categories.dc.html).
 * The category tree itself comes from DemoData::categories().
 */
final class CategoryData
{
    /** Categories hidden from the store menu / set inactive in the design. */
    private const MENU_OFF = ['acc', 'dup'];

    private const INACTIVE = ['acc'];

    public static function tabs(): array
    {
        return [
            'categories' => ['label' => 'Categories', 'count' => 20],
            'brands' => ['label' => 'Brands', 'count' => 6],
            'attributes' => 'Attributes (sizes, colours, fabrics)',
            'charts' => ['label' => 'Size charts', 'count' => 5],
        ];
    }

    /** Tree rows (main category followed by its subs) with menu/status flags and form defaults. */
    public static function rows(): array
    {
        $all = DemoData::categories();
        $names = array_column($all, 'name', 'id');

        $siblingIndex = [];
        $childCount = [];
        $rows = [];
        foreach ($all as $c) {
            $parentKey = $c['parent_id'] ?? '_root';
            $siblingIndex[$parentKey] = ($siblingIndex[$parentKey] ?? 0) + 1;
            if ($c['parent_id']) {
                $childCount[$c['parent_id']] = ($childCount[$c['parent_id']] ?? 0) + 1;
            }

            $rows[$c['id']] = $c + [
                'parent_name' => $c['parent_id'] ? $names[$c['parent_id']] : 'None (main category)',
                'sort' => $siblingIndex[$parentKey],
                'menu' => ! in_array($c['id'], self::MENU_OFF, true),
                'active' => ! in_array($c['id'], self::INACTIVE, true),
                'meta' => 'Shop '.strtolower($c['name']).' online in Bangladesh. New designs every week, cash on delivery, easy exchange.',
            ];
        }
        foreach ($rows as $id => $r) {
            $rows[$id]['children'] = $childCount[$id] ?? 0;
        }

        return $rows;
    }

    /** Main categories offered in the "Parent category" select. */
    public static function parentOptions(): array
    {
        return array_values(array_map(fn ($c) => $c['name'], array_filter(DemoData::categories(), fn ($c) => $c['level'] === 0)));
    }

    public static function attributes(): array
    {
        return [
            'sizes' => ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL'],
            'sizes2' => ['28', '30', '32', '34', '36', '2Y', '4Y', '6Y', '8Y', '10Y', '12Y'],
            'colours' => [
                ['name' => 'Off-white', 'hex' => '#F1ECE1', 'n' => 184],
                ['name' => 'White', 'hex' => '#FFFFFF', 'n' => 96],
                ['name' => 'Black', 'hex' => '#1C1C1E', 'n' => 142],
                ['name' => 'Navy', 'hex' => '#1F2A44', 'n' => 151],
                ['name' => 'Olive', 'hex' => '#5D6B3A', 'n' => 88],
                ['name' => 'Maroon', 'hex' => '#6B1F2A', 'n' => 74],
                ['name' => 'Sky blue', 'hex' => '#9CC3E4', 'n' => 63],
                ['name' => 'Pink', 'hex' => '#E7A9B9', 'n' => 58],
                ['name' => 'Mustard', 'hex' => '#C99A2E', 'n' => 41],
            ],
            'fabrics' => ['100% Cotton', 'Linen blend', 'Lawn', 'Georgette', 'Silk blend', 'Khadi', 'Denim', 'Jersey knit'],
        ];
    }

    public static function brands(): array
    {
        return [
            ['name' => 'YOUR BRAND', 'initials' => 'YB', 'note' => 'Own label', 'products' => 352, 'menu' => true, 'status' => 'active'],
            ['name' => 'Partner brand', 'initials' => 'PB', 'note' => 'Three-piece & sets', 'products' => 38, 'menu' => true, 'status' => 'active'],
            ['name' => '[Partner brand 02]', 'initials' => 'P2', 'note' => 'Kids wear', 'products' => 12, 'menu' => true, 'status' => 'active'],
            ['name' => '[Partner brand 03]', 'initials' => 'P3', 'note' => 'Accessories', 'products' => 6, 'menu' => false, 'status' => 'active'],
            ['name' => '[Partner brand 04]', 'initials' => 'P4', 'note' => 'Footwear (trial)', 'products' => 4, 'menu' => false, 'status' => 'active'],
            ['name' => '[Partner brand 05]', 'initials' => 'P5', 'note' => 'Paused', 'products' => 0, 'menu' => false, 'status' => 'inactive'],
        ];
    }

    public static function sizeCharts(): array
    {
        return [
            ['name' => "Men's Panjabi (inch)", 'sizes' => 'S – XXL', 'measures' => 'Chest, length, shoulder, sleeve', 'products' => 64],
            ['name' => "Men's Shirt (inch)", 'sizes' => 'S – XXL', 'measures' => 'Chest, length, shoulder, sleeve, collar', 'products' => 89],
            ['name' => "Women's Kurti (inch)", 'sizes' => 'S – XL', 'measures' => 'Bust, waist, hip, length', 'products' => 110],
            ['name' => 'Kids (age)', 'sizes' => '2Y – 12Y', 'measures' => 'Age, height, chest, length', 'products' => 56],
            ['name' => 'Pants (waist)', 'sizes' => '28 – 36', 'measures' => 'Waist, hip, inseam, length', 'products' => 21],
        ];
    }
}
