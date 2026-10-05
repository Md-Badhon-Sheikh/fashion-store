<?php

namespace App\Support\Demo;

/**
 * Sample data for Users & roles (design: Users.dc.html).
 * Replace with User / Role / Permission queries later.
 */
final class UsersData
{
    /** Role name => chip tone used in the users table and role cards. */
    public static function roleTones(): array
    {
        return [
            'Admin' => 'brand',
            'Manager' => 'blue',
            'Sales Staff' => 'violet',
            'Inventory Staff' => 'cyan',
        ];
    }

    /** Staff accounts. `avatar` is a tone key (green, blue, violet, cyan, gray). */
    public static function users(): array
    {
        return [
            ['id' => 1, 'name' => 'Admin', 'initials' => 'SA', 'email' => 'owner@[DOMAIN]', 'role' => 'Admin', 'phone' => '017•••••120', 'last_login' => 'Today, 9:12 AM', 'where' => 'Chrome · Windows · Dhaka', 'two_factor' => true, 'active' => true, 'avatar' => 'green'],
            ['id' => 2, 'name' => 'Manager 01', 'initials' => 'M1', 'email' => 'manager@[DOMAIN]', 'role' => 'Manager', 'phone' => '018•••••336', 'last_login' => 'Today, 10:04 AM', 'where' => 'Chrome · Android', 'two_factor' => true, 'active' => true, 'avatar' => 'blue'],
            ['id' => 3, 'name' => 'Sales Staff 01', 'initials' => 'S1', 'email' => 'counter1@[DOMAIN]', 'role' => 'Sales Staff', 'phone' => '019•••••581', 'last_login' => 'Today, 10:30 AM', 'where' => 'POS terminal 1', 'two_factor' => false, 'active' => true, 'avatar' => 'violet'],
            ['id' => 4, 'name' => 'Sales Staff 02', 'initials' => 'S2', 'email' => 'counter2@[DOMAIN]', 'role' => 'Sales Staff', 'phone' => '016•••••772', 'last_login' => 'Today, 11:15 AM', 'where' => 'POS terminal 2', 'two_factor' => false, 'active' => true, 'avatar' => 'violet'],
            ['id' => 5, 'name' => 'Sales Staff 03', 'initials' => 'S3', 'email' => 'online@[DOMAIN]', 'role' => 'Sales Staff', 'phone' => '015•••••904', 'last_login' => 'Yesterday, 8:47 PM', 'where' => 'Chrome · Windows', 'two_factor' => true, 'active' => true, 'avatar' => 'violet'],
            ['id' => 6, 'name' => 'Inventory Staff 01', 'initials' => 'I1', 'email' => 'stock@[DOMAIN]', 'role' => 'Inventory Staff', 'phone' => '013•••••218', 'last_login' => '2 Oct, 4:20 PM', 'where' => 'Chrome · Windows · Warehouse', 'two_factor' => false, 'active' => true, 'avatar' => 'cyan'],
            ['id' => 7, 'name' => 'Sales Staff 04', 'initials' => 'S4', 'email' => 'temp@[DOMAIN]', 'role' => 'Sales Staff', 'phone' => '017•••••663', 'last_login' => '14 Sep, 6:02 PM', 'where' => 'POS terminal 1', 'two_factor' => false, 'active' => false, 'avatar' => 'gray'],
        ];
    }

    /** Role cards. */
    public static function roles(): array
    {
        return [
            ['name' => 'Admin', 'members' => 1, 'description' => 'Full access to everything, including settings, users and accounting.'],
            ['name' => 'Manager', 'members' => 1, 'description' => 'Runs the shop: orders, products, stock, reports. No user or system settings.'],
            ['name' => 'Sales Staff', 'members' => 4, 'description' => 'POS selling, online order handling and customers. Limited discount.'],
            ['name' => 'Inventory Staff', 'members' => 1, 'description' => 'Stock-in, purchases, adjustments, barcode labels and suppliers.'],
        ];
    }

    /** Permission matrix rows. */
    public static function modules(): array
    {
        return ['Dashboard', 'POS', 'Orders', 'Products', 'Inventory', 'Purchases', 'Customers', 'Coupons', 'Accounting', 'Reports', 'Settings', 'Users'];
    }

    /** Permission matrix columns. */
    public static function actions(): array
    {
        return ['View', 'Create', 'Edit', 'Delete', 'Export'];
    }

    /**
     * Default permissions per role: role => [module index => [View, Create, Edit, Delete, Export]] as booleans.
     */
    public static function permissions(): array
    {
        $map = [
            'Admin' => array_fill(0, 12, '11111'),
            'Manager' => ['10001', '11100', '11101', '11101', '11101', '11101', '11101', '11101', '11001', '10001', '10000', '10000'],
            'Sales Staff' => ['10000', '11000', '11100', '10000', '10000', '00000', '11100', '10000', '00000', '00000', '00000', '00000'],
            'Inventory Staff' => ['10000', '00000', '10000', '11100', '11101', '11100', '00000', '00000', '00000', '10001', '00000', '00000'],
        ];

        return array_map(
            fn (array $rows) => array_map(fn (string $r) => array_map(fn ($c) => $c === '1', str_split($r)), $rows),
            $map
        );
    }

    /** POS & sales special permissions. */
    public static function specialPermissions(): array
    {
        return [
            ['name' => 'Give discount above 10%', 'note' => 'Up to 10% allowed without approval; above needs Manager PIN'],
            ['name' => 'Edit price at POS', 'note' => 'Override the selling price of an item in the cart'],
            ['name' => 'Delete sale', 'note' => 'Void a completed POS sale (always logged)'],
            ['name' => 'Close register', 'note' => 'End-of-day cash count and register closing'],
        ];
    }

    /** Default special permissions per role (same order as specialPermissions()). */
    public static function specialDefaults(): array
    {
        return [
            'Admin' => [true, true, true, true],
            'Manager' => [true, true, true, true],
            'Sales Staff' => [false, false, false, true],
            'Inventory Staff' => [false, false, false, false],
        ];
    }

    /** Invite form options. */
    public static function branches(): array
    {
        return ['Main shop · Counter 1', 'Main shop · Counter 2', 'Warehouse', 'Online only'];
    }

    public static function pendingInvites(): array
    {
        return [
            ['name' => 'Sales Staff 05', 'phone' => '013•••••442'],
        ];
    }
}
