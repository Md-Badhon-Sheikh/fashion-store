<?php

namespace App\Support\Demo;

use App\Support\DemoData;

/**
 * Sample data for Finance › Reports (design: Reports.dc.html), "Sales — daily / weekly / monthly".
 * Everything is pre-computed per grouping (monthly|weekly|daily, default first) so the view
 * can switch with Alpine without recalculating.
 */
final class ReportsData
{
    public const DEFAULT_PERIOD = 'monthly';

    /** Report picker groups; 'current' marks the open report. */
    public static function reportGroups(): array
    {
        return [
            'Sales' => ['Sales — daily / weekly / monthly', 'Product-wise sales', 'Size-wise sales', 'Payment method', 'Coupon usage', 'Staff-wise POS sales'],
            'Stock' => ['Stock report', 'Low / out-of-stock', 'Purchase vs sales'],
            'Finance' => ['Profit / loss', 'Expense report'],
            'Customers' => ['Customer report'],
        ];
    }

    public static function currentReport(): string
    {
        return 'Sales — daily / weekly / monthly';
    }

    /** Group-by options in button order. */
    public static function periodOptions(): array
    {
        return ['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly'];
    }

    /** Raw series: channel sales in thousand (k) or lakh (L) Taka. */
    private static function raw(): array
    {
        return [
            'monthly' => [
                'name' => 'monthly', 'head' => 'Month', 'text' => 'October 2025 – September 2026 (12 months)', 'from' => '2025-10-01',
                'unit' => 'Monthly sales by channel, ৳ lakh', 'mult' => 100000, 'max' => 30, 'ticks' => ['30L', '20L', '10L', '0'], 'size_div' => 1, 'axis_every' => 1,
                'online' => [9.2, 10.1, 13.4, 11.0, 12.5, 16.8, 10.4, 14.9, 10.6, 9.1, 11.7, 12.2],
                'pos' => [4.1, 4.5, 5.9, 4.8, 5.2, 7.9, 5.0, 7.7, 5.5, 4.7, 5.8, 6.2],
                'labels' => ['Oct 25', 'Nov 25', 'Dec 25', 'Jan 26', 'Feb 26', 'Mar 26', 'Apr 26', 'May 26', 'Jun 26', 'Jul 26', 'Aug 26', 'Sep 26'],
            ],
            'weekly' => [
                'name' => 'weekly', 'head' => 'Week', 'text' => '10 Aug – 4 Oct 2026 (8 weeks)', 'from' => '2026-08-10',
                'unit' => 'Weekly sales by channel, ৳ thousand', 'mult' => 1000, 'max' => 600, 'ticks' => ['600k', '400k', '200k', '0'], 'size_div' => 6.5, 'axis_every' => 1,
                'online' => [262, 281, 248, 305, 274, 296, 318, 331],
                'pos' => [131, 142, 120, 156, 138, 149, 160, 168],
                'labels' => ['10 Aug', '17 Aug', '24 Aug', '31 Aug', '7 Sep', '14 Sep', '21 Sep', '28 Sep'],
            ],
            'daily' => [
                'name' => 'daily', 'head' => 'Day', 'text' => '21 Sep – 4 Oct 2026 (14 days)', 'from' => '2026-09-21',
                'unit' => 'Daily sales by channel, ৳ thousand', 'mult' => 1000, 'max' => 60, 'ticks' => ['60k', '40k', '20k', '0'], 'size_div' => 26, 'axis_every' => 2,
                'online' => [22, 25, 19, 28, 31, 26, 24, 30, 35, 29, 33, 38, 36, 31.4],
                'pos' => [12, 14, 11, 15, 18, 16, 13, 17, 19, 15, 18, 22, 20, 16.85],
                'labels' => ['21 Sep', '22 Sep', '23 Sep', '24 Sep', '25 Sep', '26 Sep', '27 Sep', '28 Sep', '29 Sep', '30 Sep', '1 Oct', '2 Oct', '3 Oct', '4 Oct'],
            ],
        ];
    }

    /** Units sold per size over the monthly (12-month) range; other groupings divide by size_div. */
    private const SIZE_BASE = ['S' => 412, 'M' => 968, 'L' => 1124, 'XL' => 786, 'XXL' => 298];

    /**
     * Per period: title, period text, date, chart (bars for admin.partials.bar-chart), KPI tiles,
     * detailed rows (newest first) + totals, size-wise bars and table.
     */
    public static function periods(): array
    {
        $tk = fn (float $n) => DemoData::money($n);
        $num = fn (float $n) => DemoData::number($n);
        $short = fn (float $v) => rtrim(rtrim(number_format($v, 2, '.', ''), '0'), '.');

        $out = [];
        foreach (self::raw() as $key => $d) {
            $unit = $d['mult'] === 100000 ? 'L' : 'k';

            // Sales trend chart (online at the bottom, POS stacked on top).
            $bars = [];
            foreach ($d['online'] as $i => $o) {
                $p = $d['pos'][$i];
                $bars[] = [
                    'x' => $i % $d['axis_every'] === ($d['axis_every'] - 1) % $d['axis_every'] ? $d['labels'][$i] : '',
                    'tip' => $d['labels'][$i].' · Online ৳'.$short($o).$unit.' · POS ৳'.$short($p).$unit.' · Total ৳'.$short(round($o + $p, 1)).$unit,
                    'segments' => [['series' => 'online', 'value' => $o], ['series' => 'pos', 'value' => $p]],
                ];
            }

            // Detailed breakdown + totals.
            $t = ['on_orders' => 0, 'pos_orders' => 0, 'online' => 0, 'pos' => 0, 'gross' => 0, 'disc' => 0, 'ret' => 0, 'net' => 0];
            $rows = [];
            foreach ($d['online'] as $i => $o) {
                $on = round($o * $d['mult']);
                $po = round($d['pos'][$i] * $d['mult']);
                $onOrders = (int) round($on / 1980);
                $posOrders = (int) round($po / 2150);
                $gross = $on + $po;
                $disc = round($gross * 0.055 / 10) * 10;
                $ret = round($on * 0.025 / 10) * 10;
                $net = $gross - $disc - $ret;
                foreach (['on_orders' => $onOrders, 'pos_orders' => $posOrders, 'online' => $on, 'pos' => $po, 'gross' => $gross, 'disc' => $disc, 'ret' => $ret, 'net' => $net] as $k => $v) {
                    $t[$k] += $v;
                }
                $rows[] = [
                    'period' => $d['labels'][$i], 'on_orders' => $num($onOrders), 'pos_orders' => $num($posOrders),
                    'online' => $tk($on), 'pos' => $tk($po), 'gross' => $tk($gross),
                    'disc' => '−'.$tk($disc), 'ret' => '−'.$tk($ret), 'net' => $tk($net),
                ];
            }
            $orders = $t['on_orders'] + $t['pos_orders'];

            // Size-wise sales with a "nice" axis maximum.
            $sizes = array_map(fn ($u) => (int) round($u / $d['size_div']), self::SIZE_BASE);
            $sMax = max($sizes);
            $step = 10 ** floor(log10($sMax)) / 2;
            $nice = ceil($sMax / $step) * $step;
            $sTotal = array_sum($sizes);
            $sizeBars = [];
            $sizeRows = [];
            foreach ($sizes as $size => $u) {
                $share = (int) round($u / $sTotal * 100);
                $sizeBars[] = ['x' => $size, 'value' => $num($u), 'tip' => 'Size '.$size.': '.$num($u).' units ('.$share.'%)', 'segments' => [['series' => 'brand', 'value' => $u]]];
                $sizeRows[] = ['size' => $size, 'units' => $num($u), 'share' => $share.'%'];
            }

            $out[$key] = [
                'title' => 'Sales report — '.$d['name'],
                'text' => $d['text'],
                'from' => $d['from'],
                'head' => $d['head'],
                'unit' => $d['unit'],
                'chart' => ['bars' => $bars, 'max' => $d['max'], 'ticks' => $d['ticks']],
                'tiles' => [
                    'gross' => $tk($t['gross']),
                    'gross_sub' => 'Online '.(int) round($t['online'] / $t['gross'] * 100).'% · POS '.(int) round($t['pos'] / $t['gross'] * 100).'%',
                    'orders' => $num($orders),
                    'orders_sub' => $num($t['on_orders']).' online · '.$num($t['pos_orders']).' POS',
                    'aov' => $tk($t['gross'] / $orders),
                    'disc' => $tk($t['disc']),
                    'ret' => $tk($t['ret']),
                    'ret_sub' => (round($t['ret'] / $t['online'] * 1000) / 10).'% of online sales',
                    'net' => $tk($t['net']),
                ],
                'rows' => array_reverse($rows),
                'total' => [
                    'on_orders' => $num($t['on_orders']), 'pos_orders' => $num($t['pos_orders']),
                    'online' => $tk($t['online']), 'pos' => $tk($t['pos']), 'gross' => $tk($t['gross']),
                    'disc' => '−'.$tk($t['disc']), 'ret' => '−'.$tk($t['ret']), 'net' => $tk($t['net']),
                ],
                'sizes' => ['bars' => $sizeBars, 'max' => $nice, 'ticks' => [$num($nice), $num($nice / 2), '0'], 'rows' => $sizeRows],
            ];
        }

        return $out;
    }
}
