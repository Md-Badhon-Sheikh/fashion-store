<?php

namespace App\Support\Demo;

/**
 * Sample data for the Activity log (design: ActivityLog.dc.html).
 * Replace with an activity / audit-log query later (newest first).
 */
final class ActivityLogData
{
    public static function kpis(): array
    {
        return [
            ['label' => 'Events today', 'value' => '284', 'sub' => 'by 6 staff users', 'tone' => 'default', 'sub_tone' => null],
            ['label' => 'Logins', 'value' => '19', 'sub' => '3 failed attempts', 'tone' => 'default', 'sub_tone' => 'warn'],
            ['label' => 'Price changes', 'value' => '6', 'sub' => '4 products · 6 variants', 'tone' => 'default', 'sub_tone' => null],
            ['label' => 'Deletes / voids', 'value' => '2', 'sub' => '1 POS sale, 1 coupon', 'tone' => 'danger', 'sub_tone' => null],
        ];
    }

    /** Filter options. */
    public static function users(): array
    {
        return ['Admin', 'Manager 01', 'Sales Staff 01', 'Sales Staff 02', 'Sales Staff 03', 'Inventory Staff 01'];
    }

    /** Action types, in the order of the quick-filter chips. Value = dot colour key. */
    public static function actionTypes(): array
    {
        return [
            'Login' => 'login',
            'Sale' => 'sale',
            'Order status change' => 'status',
            'Product edit' => 'product',
            'Price change' => 'price',
            'Stock adjustment' => 'stock',
            'Delete' => 'delete',
            'Settings change' => 'settings',
        ];
    }

    /**
     * Timeline entries for 4 Oct 2026. `link` is [route name, params] or null.
     * severity: info | warning | critical.
     */
    public static function entries(): array
    {
        return [
            ['id' => 'EV-884213', 'time' => '2:31 PM', 'user' => 'Manager 01', 'role' => 'Manager', 'action' => 'Price change', 'target' => 'PNJ-1024-OW-M', 'link' => ['admin.products.index', []],
                'details' => 'Price ৳2,650 → ৳2,450 (Embroidered Cotton Panjabi, M · Off-white)', 'ip' => '103.112.•••.24', 'device' => 'Chrome · Windows', 'severity' => 'info',
                'diff' => [['Selling price', '৳2,650', '৳2,450'], ['Compare-at price', '(empty)', '৳2,650'], ['Applies to', 'M only', 'M only (1 of 5 sizes)']],
                'note' => 'Reason entered: "Match Eid offer price on Facebook post". Online store and POS price updated at the same time.'],
            ['id' => 'EV-884207', 'time' => '2:18 PM', 'user' => 'Sales Staff 01', 'role' => 'Sales Staff', 'action' => 'Sale', 'target' => '#POS-2291', 'link' => ['admin.pos', []],
                'details' => '6 items · ৳6,870 · Cash ৳4,000 + bKash ৳2,870 · discount 5%', 'ip' => 'POS terminal 1', 'device' => 'Counter 1 · Chrome', 'severity' => 'info',
                'diff' => [['Register cash', '৳18,450', '৳22,450'], ['Discount', '0%', '5% (within limit)']],
                'note' => 'Receipt printed (80mm). Stock reduced for 6 variants.'],
            ['id' => 'EV-884199', 'time' => '2:05 PM', 'user' => 'Admin', 'role' => 'Admin', 'action' => 'Delete', 'target' => '#POS-2287', 'link' => ['admin.pos', []],
                'details' => 'POS sale voided · ৳1,350 · reason: wrong item scanned', 'ip' => '103.112.•••.24', 'device' => 'Chrome · Windows', 'severity' => 'critical',
                'diff' => [['Sale status', 'Completed', 'Voided'], ['Stock KRT-0882-MR-L', '11', '12'], ['Register cash', '৳19,800', '৳18,450']],
                'note' => 'Voided sales stay in reports as voided. Original receipt number cannot be reused.'],
            ['id' => 'EV-884160', 'time' => '1:20 PM', 'user' => 'Sales Staff 01', 'role' => 'Sales Staff', 'action' => 'Order status change', 'target' => '#WB-10482', 'link' => ['admin.orders.show', ['order' => 'WB-10482']],
                'details' => 'Confirmed → Processing · stock reserved for 3 items', 'ip' => '103.112.•••.31', 'device' => 'Chrome · Windows', 'severity' => 'info',
                'diff' => [['Status', 'Confirmed', 'Processing'], ['Reserved stock', '0', '3 pcs']],
                'note' => 'Customer SMS not sent for Processing (turned off in SMS & email settings).'],
            ['id' => 'EV-884122', 'time' => '12:47 PM', 'user' => 'Inventory Staff 01', 'role' => 'Inventory Staff', 'action' => 'Stock adjustment', 'target' => 'SHT-0457-SB-L', 'link' => ['admin.inventory', []],
                'details' => 'Stock 5 → 3 · reason: damaged (stain)', 'ip' => '103.112.•••.40', 'device' => 'Chrome · Warehouse PC', 'severity' => 'warning',
                'diff' => [['Stock on hand', '5', '3'], ['Stock value (cost)', '৳4,250', '৳2,550']],
                'note' => 'Variant is now below low-stock threshold (5). Admin low-stock alert sent.'],
            ['id' => 'EV-884098', 'time' => '12:10 PM', 'user' => 'Manager 01', 'role' => 'Manager', 'action' => 'Product edit', 'target' => 'PNJ-1024', 'link' => ['admin.products.index', []],
                'details' => 'Description updated · 2 images added · tag "Eid 2026" added', 'ip' => '103.112.•••.24', 'device' => 'Chrome · Windows', 'severity' => 'info',
                'diff' => [['Images', '4', '6'], ['Tags', 'panjabi, cotton', 'panjabi, cotton, Eid 2026']],
                'note' => 'Long description changed (142 words). Full text diff available in product history.'],
            ['id' => 'EV-884071', 'time' => '11:52 AM', 'user' => 'Admin', 'role' => 'Admin', 'action' => 'Settings change', 'target' => 'Shipping zones', 'link' => ['admin.settings', []],
                'details' => 'Outside Dhaka charge ৳120 → ৳130', 'ip' => '103.112.•••.24', 'device' => 'Chrome · Windows', 'severity' => 'warning',
                'diff' => [['Outside Dhaka', '৳120', '৳130'], ['Free delivery over', '৳3,000', '৳3,000']],
                'note' => 'Applies to new orders only. Existing orders keep their delivery charge.'],
            ['id' => 'EV-884055', 'time' => '11:30 AM', 'user' => 'Admin', 'role' => 'Admin', 'action' => 'Delete', 'target' => 'Coupon FLASH50', 'link' => ['admin.coupons', []],
                'details' => 'Coupon deleted · 0 uses · expired 30 Sep', 'ip' => '103.112.•••.24', 'device' => 'Chrome · Windows', 'severity' => 'critical',
                'diff' => [['Coupon', 'FLASH50 · 50% · max ৳500', '(deleted)']],
                'note' => 'Deleted coupons are kept in the archive for 90 days.'],
            ['id' => 'EV-883990', 'time' => '10:30 AM', 'user' => 'Sales Staff 01', 'role' => 'Sales Staff', 'action' => 'Login', 'target' => 'POS', 'link' => ['admin.pos', []],
                'details' => 'Login successful · register opened with ৳5,000 float', 'ip' => 'POS terminal 1', 'device' => 'Counter 1 · Chrome', 'severity' => 'info',
                'diff' => [['Register', 'Closed', 'Open · float ৳5,000']],
                'note' => 'Session expires after 8 hours or at register close.'],
            ['id' => 'EV-883962', 'time' => '9:58 AM', 'user' => 'Unknown', 'role' => 'Not signed in', 'action' => 'Login', 'target' => 'Admin panel', 'link' => ['admin.login', []],
                'details' => 'Failed login · 3 attempts · counter2@[DOMAIN]', 'ip' => '37.111.•••.9', 'device' => 'Chrome · Android', 'severity' => 'warning',
                'diff' => [['Failed attempts', '0', '3'], ['Account lock', 'Unlocked', 'Locked 15 min']],
                'note' => 'Login from a new device and location. Account owner notified by SMS.'],
            ['id' => 'EV-883940', 'time' => '9:12 AM', 'user' => 'Admin', 'role' => 'Admin', 'action' => 'Login', 'target' => 'Admin panel', 'link' => ['admin.login', []],
                'details' => 'Login successful with 2FA code', 'ip' => '103.112.•••.24', 'device' => 'Chrome · Windows', 'severity' => 'info',
                'diff' => [['Session', '(none)', 'New session · remember 7 days']],
                'note' => 'Known device.'],
        ];
    }

    /** Severity => chip tone. */
    public static function severityTones(): array
    {
        return ['info' => 'gray', 'warning' => 'amber', 'critical' => 'red'];
    }
}
