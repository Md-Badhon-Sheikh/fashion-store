<?php

namespace App\Support;

/**
 * Sidebar navigation definition (groups, links, icons, badges).
 * Rendered by resources/views/admin/partials/sidebar.blade.php.
 *
 * 'active' is the pattern passed to request()->routeIs().
 * 'icon' is a key of resources/views/components/admin/icon.blade.php.
 * 'badge' is a key of DemoData::navBadges().
 */
final class AdminNav
{
    public static function groups(): array
    {
        $badges = DemoData::navBadges();

        $groups = [
            'Overview' => [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'icon' => 'dashboard'],
                ['label' => 'POS', 'route' => 'admin.pos', 'active' => 'admin.pos', 'icon' => 'pos'],
            ],
            'Sales' => [
                ['label' => 'Orders', 'route' => 'admin.orders.index', 'active' => 'admin.orders.*', 'icon' => 'orders', 'badge' => 'orders'],
                ['label' => 'Customers', 'route' => 'admin.customers', 'active' => 'admin.customers', 'icon' => 'customers'],
                ['label' => 'Returns & exchanges', 'route' => 'admin.returns', 'active' => 'admin.returns', 'icon' => 'returns', 'badge' => 'returns'],
            ],
            'Catalog' => [
                ['label' => 'Products', 'route' => 'admin.products.index', 'active' => 'admin.products.*', 'icon' => 'products'],
                ['label' => 'Categories & brands', 'route' => 'admin.categories', 'active' => 'admin.categories', 'icon' => 'categories'],
                ['label' => 'Barcode labels', 'route' => 'admin.barcodes', 'active' => 'admin.barcodes', 'icon' => 'barcode'],
                ['label' => 'Reviews', 'route' => 'admin.reviews', 'active' => 'admin.reviews', 'icon' => 'star', 'badge' => 'reviews'],
            ],
            'Inventory' => [
                ['label' => 'Stock overview', 'route' => 'admin.inventory', 'active' => 'admin.inventory', 'icon' => 'stock'],
                ['label' => 'Stock-in / purchases', 'route' => 'admin.stock-in', 'active' => 'admin.stock-in', 'icon' => 'stock-in'],
                ['label' => 'Suppliers', 'route' => 'admin.suppliers', 'active' => 'admin.suppliers', 'icon' => 'truck'],
            ],
            'Marketing' => [
                ['label' => 'Coupons', 'route' => 'admin.coupons', 'active' => 'admin.coupons', 'icon' => 'coupon'],
                ['label' => 'Flash sale', 'route' => 'admin.flash-sales', 'active' => 'admin.flash-sales', 'icon' => 'flash'],
                ['label' => 'Banners & pages', 'route' => 'admin.banners', 'active' => 'admin.banners', 'icon' => 'image'],
            ],
            'Finance' => [
                ['label' => 'Accounting', 'route' => 'admin.accounting', 'active' => 'admin.accounting', 'icon' => 'accounting'],
                ['label' => 'Reports', 'route' => 'admin.reports', 'active' => 'admin.reports', 'icon' => 'reports'],
            ],
            'System' => [
                ['label' => 'SMS & email', 'route' => 'admin.notifications', 'active' => 'admin.notifications', 'icon' => 'mail'],
                ['label' => 'Users & roles', 'route' => 'admin.users', 'active' => 'admin.users', 'icon' => 'shield'],
                ['label' => 'Activity log', 'route' => 'admin.activity-log', 'active' => 'admin.activity-log', 'icon' => 'clock'],
                ['label' => 'Settings', 'route' => 'admin.settings', 'active' => 'admin.settings', 'icon' => 'settings'],
            ],
        ];

        $out = [];
        foreach ($groups as $label => $items) {
            $out[] = [
                'label' => $label,
                'items' => array_map(function (array $item) use ($badges) {
                    $item['badge'] = isset($item['badge']) ? ($badges[$item['badge']] ?? null) : null;
                    $item['url'] = route($item['route']);
                    $item['is_active'] = request()->routeIs($item['active']);

                    return $item;
                }, $items),
            ];
        }

        return $out;
    }

    /** Bottom tab bar on phones (M-AdminDashboard / M-AdminOrders). */
    public static function tabbar(): array
    {
        $badges = DemoData::navBadges();

        return [
            ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'icon' => 'dashboard', 'badge' => null],
            ['label' => 'Orders', 'route' => 'admin.orders.index', 'active' => 'admin.orders.*', 'icon' => 'orders', 'badge' => $badges['orders'] ?? null],
            ['label' => 'POS', 'route' => 'admin.pos', 'active' => 'admin.pos', 'icon' => 'pos', 'badge' => null],
            ['label' => 'Products', 'route' => 'admin.products.index', 'active' => 'admin.products.*', 'icon' => 'products', 'badge' => null],
        ];
    }
}
