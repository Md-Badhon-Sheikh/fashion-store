<?php

namespace App\Support\Demo;

use App\Support\DemoData;

/**
 * Sample data for Marketing › Banners & pages (design: Banners.dc.html).
 * Replace with Slide / Banner / Page / Menu queries returning the same shapes.
 */
final class BannersData
{
    /** Section nav (anchor links) with counts. */
    public static function sections(): array
    {
        return [
            'sliders' => ['label' => 'Home sliders', 'count' => '4'],
            'promos' => ['label' => 'Promo banners', 'count' => '3'],
            'announcement' => ['label' => 'Announcement bar', 'count' => ''],
            'pages' => ['label' => 'Pages', 'count' => '7'],
            'menus' => ['label' => 'Menus', 'count' => '2'],
        ];
    }

    /**
     * Home page slides in display order. status = schedule status (active|scheduled);
     * enabled = the visibility switch (switched-off slides show as "Inactive").
     */
    public static function slides(): array
    {
        $s = fn (string $id, string $title, string $sub, string $btn, string $link, string $schedule, string $start, string $end, string $status, string $img, string $tone, bool $enabled = true) => [
            'id' => $id, 'title' => $title, 'sub' => $sub, 'btn' => $btn, 'link' => $link,
            'schedule' => $schedule, 'start' => $start, 'end' => $end, 'status' => $status,
            'img' => DemoData::img($img), 'tone' => $tone, 'enabled' => $enabled,
        ];

        return [
            $s('s1', 'Eid Collection 2026', 'Hand-embroidered cotton and linen Panjabi for the festive season.', 'Shop Panjabi', '/collections/eid-2026', '20 Sep 2026 – 15 Oct 2026', '2026-09-20T00:00', '2026-10-15T23:59', 'active', 'hero1', '#E4DCCF'),
            $s('s2', 'New Kurti Arrivals', 'Block print and linen kurti, sizes S to XXL.', 'Shop kurti', '/category/women/kurti', 'Always on', '2026-08-01T00:00', '', 'active', 'hero2', '#E8D8D6'),
            $s('s3', 'Puja Festive Edit', 'Lawn and georgette three-piece in festive colours.', 'Explore the edit', '/collections/puja', '15 Oct 2026 – 25 Oct 2026', '2026-10-15T00:00', '2026-10-25T23:59', 'scheduled', 'hero3', '#E7D6DA'),
            $s('s4', 'Winter Preview', 'Early look at the winter shirt and chino range.', 'Coming soon', '/collections/winter', '1 Nov 2026 – 31 Jan 2027', '2026-11-01T00:00', '2027-01-31T23:59', 'scheduled', 'promo2', '#D3DCE4', false),
        ];
    }

    /** Fixed promo banner spots. */
    public static function promos(): array
    {
        return [
            ['title' => 'Panjabi under ৳1,990', 'place' => 'Home · below categories', 'schedule' => 'until 15 Oct', 'status' => 'active', 'img' => DemoData::img('promo1'), 'tone' => '#E9DCCB'],
            ['title' => 'Free delivery over ৳3,000', 'place' => 'Home · above footer', 'schedule' => 'Always on', 'status' => 'active', 'img' => DemoData::img('promo2'), 'tone' => '#DCE6DF'],
            ['title' => 'Kids festive sets', 'place' => 'Category · Kids top', 'schedule' => 'from 15 Oct', 'status' => 'scheduled', 'img' => DemoData::img('cat_kids'), 'tone' => '#E8E0CF'],
        ];
    }

    /** Announcement bar: quick templates and colour options. */
    public static function announcement(): array
    {
        return [
            'enabled' => true,
            'link' => '/pages/shipping',
            'max' => 90,
            'templates' => [
                ['label' => 'Free delivery', 'text' => 'Free delivery inside Dhaka on orders over ৳3,000'],
                ['label' => 'Eid sale', 'text' => 'Eid sale is live: up to 30% off Panjabi'],
                ['label' => 'Cash on delivery', 'text' => 'Cash on delivery available all over Bangladesh'],
            ],
            'colours' => [
                'brand' => ['name' => 'Brand green', 'bg' => '#0E5A3A', 'fg' => '#FFFFFF'],
                'ink' => ['name' => 'Black', 'bg' => '#16181D', 'fg' => '#FFFFFF'],
                'sale' => ['name' => 'Sale red', 'bg' => '#B42318', 'fg' => '#FFFFFF'],
                'gold' => ['name' => 'Gold', 'bg' => '#E8B04B', 'fg' => '#16181D'],
            ],
            'colour' => 'brand',
            'store_nav' => ['Men', 'Women', 'Kids', 'New in'],
        ];
    }

    /** Static information pages (store footer / checkout links). */
    public static function pages(): array
    {
        $p = fn (string $name, string $url, string $updated, string $by, string $status = 'published') => compact('name', 'url', 'updated', 'by', 'status');

        return [
            $p('About us', '/pages/about', '12 Aug 2026', 'Admin'),
            $p('Contact', '/pages/contact', '3 Oct 2026', 'Store Manager'),
            $p('FAQ', '/pages/faq', '28 Sep 2026', 'Store Manager'),
            $p('Size guide', '/pages/size-guide', '19 Sep 2026', 'Admin'),
            $p('Return & exchange policy', '/pages/return-policy', '1 Sep 2026', 'Admin'),
            $p('Privacy policy', '/pages/privacy', '15 Jul 2026', 'Admin'),
            $p('Terms & conditions', '/pages/terms', '2 Oct 2026', 'Admin', 'draft'),
        ];
    }

    /** Store menus; level 1 = sub-menu item. */
    public static function menus(): array
    {
        $items = fn (array $rows) => array_map(fn ($r) => ['label' => $r[0], 'link' => $r[1], 'level' => $r[2] ?? 0], $rows);

        return [
            [
                'name' => 'Main menu', 'where' => 'Store header', 'bold_top' => true,
                'items' => $items([
                    ['Men', '/category/men'], ['Panjabi', '/category/men/panjabi', 1], ['Shirts & polo', '/category/men/shirts', 1], ['Pants & pajama', '/category/men/pants', 1],
                    ['Women', '/category/women'], ['Kurti', '/category/women/kurti', 1], ['Three-piece', '/category/women/three-piece', 1],
                    ['Kids', '/category/kids'], ['Flash sale', '/flash-sale'],
                ]),
            ],
            [
                'name' => 'Footer menu', 'where' => 'Store footer', 'bold_top' => false,
                'items' => $items([
                    ['About us', '/pages/about'], ['Contact', '/pages/contact'], ['FAQ', '/pages/faq'], ['Size guide', '/pages/size-guide'],
                    ['Return & exchange policy', '/pages/return-policy'], ['Privacy policy', '/pages/privacy'], ['Terms & conditions', '/pages/terms'],
                ]),
            ],
        ];
    }
}
