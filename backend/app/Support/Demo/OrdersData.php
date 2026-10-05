<?php

namespace App\Support\Demo;

use App\Support\DemoData;

/**
 * Sample data for the Orders list (Orders.dc.html, M-AdminOrders.dc.html)
 * and the order detail page (OrderDetail.dc.html).
 *
 * Builds on DemoData::orders(); only adds presentation fields the list,
 * the phone cards and the detail page need. Replace with Eloquent queries
 * (or API Resources) returning the same keys when wiring real data.
 */
final class OrdersData
{
    /** Status flow of an online order (cancelled / returned sit outside it). */
    public const FLOW = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];

    /** Order ids shown on the Orders design, newest first. */
    private const LIST_IDS = [
        'WB-10491', 'WB-10490', 'PH-0217', 'WB-10487', 'WB-10482', 'POS-2291',
        'WB-10476', 'FB-0388', 'WB-10470', 'WB-10466', 'WB-10451',
    ];

    /** Quick action on a phone order card, by current status. */
    public static function nextActions(): array
    {
        return [
            'pending' => 'Confirm',
            'confirmed' => 'Start processing',
            'processing' => 'Mark shipped',
            'shipped' => 'Mark delivered',
        ];
    }

    /** Status tabs: key => label. */
    public static function tabs(): array
    {
        return [
            'all' => 'All',
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            'returned' => 'Returned',
        ];
    }

    /** All-time counts per tab (desktop tabs). */
    public static function tabCounts(): array
    {
        return DemoData::orderTabCounts();
    }

    /** Today's counts per tab (phone pill tabs); "all" is the sum. */
    public static function todayCounts(): array
    {
        $counts = DemoData::dashboardStatusCounts();

        return ['all' => array_sum($counts)] + $counts;
    }

    /**
     * Rows of the Orders list (desktop table and phone cards).
     *
     * Shape: id, number, date, time, card_time, phone (masked), phone_full|null, area, items_count, items_label,
     * items_summary, source, payment_method, payment_status, pay_tone, pay_label, paid,
     * courier, tracking, amount, status
     */
    public static function rows(): array
    {
        $rows = [];
        foreach (self::LIST_IDS as $id) {
            $o = DemoData::findOrder($id);
            if ($o === null) {
                continue;
            }

            $rows[] = [
                'id' => $o['id'],
                'number' => $o['number'],
                'date' => $o['date'],
                'time' => $o['time'],
                'card_time' => self::cardTime($o['date'], $o['time']),
                'phone' => $o['customer']['phone_masked'],
                'phone_full' => $o['customer']['phone'],
                'area' => $o['customer']['area'],
                'items_count' => $o['items_count'],
                'items_label' => $o['items_count'].' '.($o['items_count'] === 1 ? 'item' : 'items'),
                'items_summary' => self::itemsSummary($o['items']),
                'source' => $o['source'],
                'payment_method' => $o['payment_method'],
                'payment_status' => $o['payment_status'],
                'pay_tone' => self::payTone($o['payment_status']),
                'pay_label' => self::payLabel($o['payment_method'], $o['payment_status']),
                'paid' => in_array($o['payment_status'], ['Paid', 'COD collected'], true),
                'courier' => $o['courier'],
                'tracking' => $o['tracking'],
                'amount' => $o['amount'],
                'status' => $o['status'],
            ];
        }

        return $rows;
    }

    /**
     * Extra fields for the order detail page.
     *
     * Shape: source_label, step_index (last reached step in FLOW), step_times[5],
     * step_notes[5], pay_status_key, courier_options[], courier_selected,
     * tracking_value, show_courier (false for in-store sales), print_url_key
     */
    public static function detail(array $order): array
    {
        $isPos = $order['source'] === 'POS';

        $index = 0;
        foreach ($order['steps'] as $i => $s) {
            if ($s['state'] !== 'todo') {
                $index = $i;
            }
        }

        $couriers = ['Steadfast', 'Pathao Courier', 'RedX', 'Own delivery'];
        $shippedOrLater = in_array($order['status'], ['shipped', 'delivered', 'returned'], true);
        $trackingLooksReal = (bool) preg_match('/^[A-Z]{2}-\d+$/', $order['tracking']);

        return [
            'source_label' => self::sourceLabel($order['source']),
            'step_index' => $index,
            'step_times' => array_column($order['steps'], 'time'),
            'step_notes' => self::stepNotes($order),
            'pay_status_key' => $order['payment_status'],
            'courier_options' => $couriers,
            'courier_selected' => in_array($order['courier'], $couriers, true) ? $order['courier'] : 'Steadfast',
            'tracking_value' => $shippedOrLater && $trackingLooksReal ? $order['tracking'] : '',
            'show_courier' => ! $isPos,
            'is_pos' => $isPos,
            'customer_line' => self::customerLine($order['customer']),
        ];
    }

    public static function sourceLabel(string $source): string
    {
        return match ($source) {
            'Online' => 'Online store',
            'POS' => 'POS (shop)',
            'Phone' => 'Phone order',
            default => $source,
        };
    }

    /** "Registered · 6 orders · ৳18,450 lifetime" */
    private static function customerLine(array $c): string
    {
        if ($c['orders'] === null) {
            return 'Walk-in · no saved profile';
        }

        return $c['account'].' · '.$c['orders'].' '.($c['orders'] === 1 ? 'order' : 'orders').' · '.DemoData::money($c['spent']).' lifetime';
    }

    /** One line per FLOW step for the phone vertical stepper. */
    private static function stepNotes(array $order): array
    {
        $placed = match ($order['source']) {
            'POS' => 'Sold at POS counter',
            'Facebook' => 'Order entered from Facebook message',
            'Phone' => 'Order taken by phone',
            default => 'Order placed on website',
        };
        if ($order['coupon']) {
            $placed .= ' · coupon '.$order['coupon']['code'].' applied';
        }

        return [
            $placed,
            $order['source'] === 'POS' ? 'Paid at the counter' : 'Confirmed by phone call · Sales Staff 01',
            $order['source'] === 'POS' ? 'Packed at the counter' : 'Stock reserved · packing at shop',
            $order['source'] === 'POS' ? 'Handed to the customer' : 'Hand over to courier with tracking ID',
            $order['payment_method'] === 'COD' ? 'COD collected by courier' : 'Delivered to the customer',
        ];
    }

    /** "1:32 PM" today, "Yesterday", or "2 Oct". */
    private static function cardTime(string $date, string $time): string
    {
        return match ($date) {
            '4 Oct 2026' => $time,
            '3 Oct 2026' => 'Yesterday',
            default => preg_replace('/ \d{4}$/', '', $date),
        };
    }

    /** "Embroidered Cotton Panjabi (M, Off-white), Block Print Cotton Kurti (L, Maroon) × 2" */
    private static function itemsSummary(array $items): string
    {
        return implode(', ', array_map(
            fn (array $i) => $i['name'].' ('.$i['size'].', '.$i['colour'].')'.($i['qty'] > 1 ? ' × '.$i['qty'] : ''),
            $items
        ));
    }

    /** Text colour tone for the payment status line in the table. */
    private static function payTone(string $status): string
    {
        return match ($status) {
            'Paid', 'COD collected' => 'green',
            'Unpaid' => 'amber',
            default => 'gray',
        };
    }

    /** Phone card payment tag: "COD", "bKash · Paid", "COD · Collected". */
    private static function payLabel(string $method, string $status): string
    {
        return match ($status) {
            'Paid' => $method.' · Paid',
            'COD collected' => $method.' · Collected',
            'Refunded' => $method.' · Refunded',
            default => $method,
        };
    }
}
