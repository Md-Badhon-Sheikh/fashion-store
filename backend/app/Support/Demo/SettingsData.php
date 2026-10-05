<?php

namespace App\Support\Demo;

/**
 * Sample data for Settings (design: Settings.dc.html).
 * Replace with a settings store (key/value table or config) later.
 */
final class SettingsData
{
    /** Left section nav: anchor id => label. */
    public static function sections(): array
    {
        return [
            'store' => 'Store info',
            'shipping' => 'Shipping zones',
            'payment' => 'Payment methods',
            'courier' => 'Courier',
            'invoice' => 'Invoice & receipt',
            'tax' => 'Tax/VAT',
            'inventory' => 'Inventory',
            'seo' => 'SEO & analytics',
            'security' => 'Security & backup',
        ];
    }

    public static function store(): array
    {
        return [
            'name' => 'YOUR BRAND',
            'phone' => '[PHONE]',
            'email' => '[EMAIL]',
            'address' => '[ADDRESS]',
            'currencies' => ['BDT · Bangladeshi Taka (৳)'],
            'number_formats' => ['৳2,14,600 (lakh)', '৳214,600'],
            'time_zones' => ['Asia/Dhaka (GMT+6)'],
        ];
    }

    public static function zones(): array
    {
        return [
            ['name' => 'Inside Dhaka', 'areas' => 'Dhaka city corporation (North + South)', 'fee' => 70, 'days' => '1–2 days', 'status' => 'active'],
            ['name' => 'Dhaka sub-area', 'areas' => 'Savar, Keraniganj, Gazipur, Narayanganj, Tongi', 'fee' => 100, 'days' => '2–3 days', 'status' => 'active'],
            ['name' => 'Outside Dhaka', 'areas' => 'All other districts', 'fee' => 130, 'days' => '3–5 days', 'status' => 'active'],
        ];
    }

    public static function freeDelivery(): array
    {
        return ['enabled' => true, 'threshold' => '3,000'];
    }

    /** Payment methods. `phase2` methods are shown disabled with a "Phase 2" badge. */
    public static function payments(): array
    {
        return [
            ['key' => 'cod', 'where' => 'Online', 'name' => 'Cash on Delivery (COD)', 'note' => 'Customer pays the courier on delivery', 'on' => true, 'phase2' => false],
            ['key' => 'pos_cash', 'where' => 'POS', 'name' => 'Cash', 'note' => 'Change calculated automatically', 'on' => true, 'phase2' => false],
            ['key' => 'pos_card', 'where' => 'POS', 'name' => 'Card (Visa / Mastercard)', 'note' => 'Via bank POS machine · enter last 4 digits', 'on' => true, 'phase2' => false],
            ['key' => 'pos_bkash', 'where' => 'POS', 'name' => 'bKash', 'note' => 'Send Money / Payment · enter transaction ID', 'on' => true, 'phase2' => false],
            ['key' => 'pos_nagad', 'where' => 'POS', 'name' => 'Nagad', 'note' => 'Send Money / Payment · enter transaction ID', 'on' => true, 'phase2' => false],
            ['key' => 'sslcommerz', 'where' => 'Online', 'name' => 'SSLCommerz (cards, mobile banking)', 'note' => 'Online payment gateway', 'on' => false, 'phase2' => true],
            ['key' => 'bkash_gateway', 'where' => 'Online', 'name' => 'bKash payment gateway', 'note' => 'Checkout with bKash PIN', 'on' => false, 'phase2' => true],
        ];
    }

    public static function merchantNumber(): string
    {
        return '[PHONE]';
    }

    public static function couriers(): array
    {
        return ['Steadfast', 'Pathao Courier', 'RedX', 'Own delivery'];
    }

    /** Courier API connections (Phase 2, shown disabled). */
    public static function courierApis(): array
    {
        return [
            ['name' => 'Steadfast', 'key1' => 'API key', 'key2' => 'Secret key'],
            ['name' => 'Pathao Courier', 'key1' => 'Client ID', 'key2' => 'Client secret'],
            ['name' => 'RedX', 'key1' => 'API access token', 'key2' => 'Store ID'],
        ];
    }

    public static function invoice(): array
    {
        return [
            'online_prefix' => 'WB-',
            'pos_prefix' => 'POS-',
            'next_online' => '10483',
            'next_pos' => '2292',
            'footer' => 'Exchange within 7 days with receipt and tags. No cash refund on sale items. Thank you for shopping!',
            'paper' => '80',
        ];
    }

    /** Toggle rows: key => [label, note, default]. */
    public static function receiptOptions(): array
    {
        return [
            'r_logo' => ['Print logo on receipt', null, true],
            'r_barcode' => ['Print sale barcode (for exchange lookup)', null, true],
            'r_vat' => ['Show VAT line on receipt', null, false],
        ];
    }

    /** Sample POS sale shown in the receipt preview. */
    public static function receiptSample(): array
    {
        return [
            'number' => 'POS-2292',
            'date' => '04/10/26 14:40',
            'lines' => [['Cotton Panjabi M', '2,450'], ['Polo T-Shirt L x2', '1,980']],
            'total' => 'Tk 4,430',
            'vat' => 'Tk 309',
            'cash' => '5,000',
            'change' => '570',
            'footer' => 'Exchange within 7 days with receipt and tags.',
        ];
    }

    public static function tax(): array
    {
        return [
            'enabled' => false,
            'rate' => '7.5',
            'modes' => ['VAT inclusive', 'VAT exclusive (added at checkout)'],
        ];
    }

    public static function inventory(): array
    {
        return [
            'threshold' => 5,
            'reserve_on' => ['Confirmed', 'Placed (Pending)', 'Processing'],
            'release_on' => ['Cancelled or Returned', 'Cancelled only'],
        ];
    }

    public static function inventoryOptions(): array
    {
        return [
            'i_backorder' => ['Allow online orders when out of stock', 'Off: size shows "Out of stock" and cannot be added to cart', false],
            'i_negative' => ['Allow negative stock at POS', 'Off: POS blocks selling more than stock on hand', false],
            'i_autohide' => ['Hide product when all sizes are out of stock', 'Product stays visible in admin and returns when restocked', true],
        ];
    }

    public static function seo(): array
    {
        return [
            'meta_title' => 'YOUR BRAND · Panjabi, Shirts, Kurti & Three-Piece Online in Bangladesh',
            'meta_description' => "Shop men's, women's and kids' fashion. Cash on delivery all over Bangladesh. Easy 7-day exchange.",
            'events' => 'PageView, ViewContent, AddToCart, InitiateCheckout, Purchase',
        ];
    }

    public static function securityOptions(): array
    {
        return [
            's_2fa' => ['Require 2FA for Admin and Manager', 'SMS code at login from a new device', true],
            's_new_device' => ['Alert owner on login from a new device', 'SMS to the store owner number', true],
            's_backup' => ['Daily database backup', 'Runs every night at the time below; stored off-server', true],
        ];
    }

    public static function security(): array
    {
        return [
            'backup_times' => ['3:00 AM', '2:00 AM', '4:00 AM'],
            'retention' => ['30 days', '14 days', '90 days'],
            'timeouts' => ['8 hours', '2 hours', '30 minutes'],
            'lockouts' => ['3 attempts · 15 min', '5 attempts · 30 min'],
            'last_backup' => 'today, 3:00 AM',
            'backup_meta' => 'Database + product images · 48.2 MB · 30 backups stored',
        ];
    }
}
