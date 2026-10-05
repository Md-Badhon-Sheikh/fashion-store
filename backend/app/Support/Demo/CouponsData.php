<?php

namespace App\Support\Demo;

use App\Support\DemoData;

/**
 * Sample data for Marketing › Coupons (design: Coupons.dc.html).
 * Replace with Coupon model queries returning the same shapes.
 */
final class CouponsData
{
    /** KPI tiles above the coupon table. */
    public static function kpis(): array
    {
        return [
            ['label' => 'Active coupons', 'value' => '4', 'sub' => '2 scheduled · 1 expired · 1 paused'],
            ['label' => 'Redemptions, October', 'value' => '486', 'sub' => 'Online 402 · POS 84'],
            ['label' => 'Discount given, October', 'value' => DemoData::money(118450), 'sub' => '6.1% of gross sales'],
            ['label' => 'Sales with a coupon', 'value' => DemoData::money(974200), 'sub' => 'Avg order '.DemoData::money(2004).' vs '.DemoData::money(1610).' without'],
        ];
    }

    /**
     * All coupons.
     * status = schedule status from the dates (active|scheduled|expired);
     * enabled = the on/off switch (a disabled, non-expired coupon shows as "Inactive").
     * channel = both|online|pos (used by the channel filter).
     */
    public static function coupons(): array
    {
        $row = fn (string $code, string $type, string $value, string $cap, int $min, int $used, int $limit, int $per, string $scope, string $target, string $from, string $to, string $status, string $channels, string $channel, bool $enabled = true) => [
            'code' => $code,
            'type' => $type,
            'value' => $value,
            'cap' => $cap,
            'min' => DemoData::money($min),
            'used' => $used,
            'used_label' => DemoData::number($used),
            'limit' => $limit,
            'limit_label' => $limit ? DemoData::number($limit) : 'Unlimited',
            'usage_pct' => $limit ? min(100, (int) round($used / $limit * 100)) : 30,
            'per_customer' => $per,
            'scope' => $scope,
            'target' => $target,
            'from' => $from,
            'to' => $to,
            'status' => $status,
            'channels' => $channels,
            'channel' => $channel,
            'enabled' => $enabled,
        ];

        return [
            $row('EID25', 'Percentage', '25%', 'max ৳600', 2000, 312, 500, 1, 'Category', 'Men › Panjabi', '20 Sep 2026, 12:00 AM', '15 Oct 2026, 11:59 PM', 'active', 'Online + POS', 'both'),
            $row('WELCOME10', 'Percentage', '10%', 'max ৳300', 1000, 1284, 0, 1, 'All', 'All products', '1 Jan 2026, 12:00 AM', '31 Dec 2026, 11:59 PM', 'active', 'Online · first order', 'online'),
            $row('FLAT300', 'Fixed', '৳300', '', 2500, 96, 200, 2, 'All', 'All products', '1 Oct 2026, 12:00 AM', '31 Oct 2026, 11:59 PM', 'active', 'Online + POS', 'both'),
            $row('POLO150', 'Fixed', '৳150', '', 800, 41, 100, 1, 'Product', 'Premium Polo T-Shirt', '1 Oct 2026, 10:00 AM', '20 Oct 2026, 11:59 PM', 'active', 'Online only', 'online'),
            $row('PUJA20', 'Percentage', '20%', 'max ৳500', 1500, 0, 300, 1, 'Category', 'Three-Piece, Kurti', '15 Oct 2026, 12:00 AM', '25 Oct 2026, 11:59 PM', 'scheduled', 'Online + POS', 'both'),
            $row('WINTER15', 'Percentage', '15%', 'max ৳450', 1800, 0, 400, 1, 'Category', 'Men (all)', '1 Nov 2026, 12:00 AM', '30 Nov 2026, 11:59 PM', 'scheduled', 'Online + POS', 'both'),
            $row('BKASH100', 'Fixed', '৳100', '', 1200, 58, 150, 1, 'All', 'All products', '1 Sep 2026, 12:00 AM', '31 Oct 2026, 11:59 PM', 'active', 'Online · prepaid only', 'online', false),
            $row('SUMMER500', 'Fixed', '৳500', '', 4000, 188, 200, 1, 'All', 'All products', '1 Jun 2026, 12:00 AM', '31 Jul 2026, 11:59 PM', 'expired', 'Online + POS', 'both', false),
        ];
    }

    /** Codes cycled by the "Generate" button (first one is pre-filled). */
    public static function generatedCodes(): array
    {
        return ['PUJA20', 'YB-7KQ2MX', 'YB-X93PLA', 'YB-R4TN8C', 'YB-HE62WD'];
    }

    /** Discount type options of the create form. */
    public static function discountTypes(): array
    {
        return [
            'percent' => ['label' => 'Percentage (%)', 'hint' => 'e.g. 20% off', 'suffix' => '%', 'default' => '20', 'value_hint' => 'Percentage off eligible items, 1–90%'],
            'fixed' => ['label' => 'Fixed amount (৳)', 'hint' => 'e.g. ৳300 off', 'suffix' => '৳', 'default' => '300', 'value_hint' => 'Taka off the eligible subtotal'],
        ];
    }

    /** Example cart for the "How it applies" checkout preview. */
    public static function previewCart(): array
    {
        return [
            'label' => 'Printed Lawn Three-Piece + Block Print Cotton Kurti',
            'subtotal' => 4340,
            'delivery' => 80,
            'delivery_label' => 'Delivery (inside Dhaka)',
        ];
    }

    /** Messages customers see when a coupon cannot be applied. */
    public static function errorMessages(): array
    {
        return [
            ['"Add ৳660 more to use this coupon"', 'below minimum order'],
            ['"This coupon has expired"', 'after end date'],
            ['"You have already used this coupon"', 'per-customer limit reached (by phone number)'],
        ];
    }
}
