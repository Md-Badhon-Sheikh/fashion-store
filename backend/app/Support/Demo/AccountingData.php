<?php

namespace App\Support\Demo;

use App\Support\DemoData;

/**
 * Sample data for Finance › Accounting (design: Accounting.dc.html).
 * Money is int Taka. Range-dependent figures are keyed by range (oct|sep|h6);
 * the default range ('sep', "Last month") comes first.
 */
final class AccountingData
{
    public const DEFAULT_RANGE = 'sep';

    private const EXPENSE_CATEGORIES = ['Rent', 'Salary', 'Utility', 'Marketing', 'Courier charges', 'Packaging', 'Other'];

    /** Raw figures per range: income, cost of goods sold, expenses per category (same order as EXPENSE_CATEGORIES). */
    private static function raw(): array
    {
        return [
            'sep' => ['label' => 'Last month', 'text' => '1 – 30 September 2026', 'from' => '2026-09-01', 'to' => '2026-09-30', 'income' => 1842600, 'cogs' => 1013400, 'cats' => [120000, 185000, 18400, 72500, 54800, 16900, 18700], 'orders' => 1146],
            'oct' => ['label' => 'This month', 'text' => '1 – 4 October 2026', 'from' => '2026-10-01', 'to' => '2026-10-04', 'income' => 214600, 'cogs' => 118000, 'cats' => [120000, 0, 0, 38000, 18600, 9800, 6000], 'orders' => 129],
            'h6' => ['label' => '6 months', 'text' => '1 April – 30 September 2026', 'from' => '2026-04-01', 'to' => '2026-09-30', 'income' => 10380000, 'cogs' => 5710000, 'cats' => [720000, 1110000, 112600, 402000, 291000, 96800, 103600], 'orders' => 6412],
        ];
    }

    /** Range buttons in display order (This month, Last month, 6 months). */
    public static function rangeOptions(): array
    {
        $r = self::raw();

        return ['oct' => $r['oct']['label'], 'sep' => $r['sep']['label'], 'h6' => $r['h6']['label']];
    }

    /** Period text and date-input values per range. */
    public static function rangeMeta(): array
    {
        return array_map(fn ($r) => ['text' => $r['text'], 'from' => $r['from'], 'to' => $r['to']], self::raw());
    }

    /**
     * Everything that changes with the range: KPI values, income-by-channel rows, expense categories.
     * Returned keyed by range so the view can hand the arrays to <x-admin.kpi> and Alpine.
     */
    public static function byRange(): array
    {
        $shares = [
            ['Online · cash on delivery', 'Collected by courier, settled weekly', 0.48],
            ['Online · prepaid', 'bKash / Nagad / card via payment gateway', 0.14],
            ['POS · cash', 'Cash drawer, day-close', 0.21],
            ['POS · card', 'Card terminal, bank T+1', 0.07],
            ['POS · mobile banking', 'bKash / Nagad merchant', 0.10],
        ];

        $out = [];
        foreach (self::raw() as $key => $r) {
            $expense = array_sum($r['cats']);
            $gross = $r['income'] - $r['cogs'];
            $net = $gross - $expense;

            $left = $r['income'];
            $channels = [];
            foreach ($shares as $i => [$name, $note, $s]) {
                $amt = $i === array_key_last($shares) ? $left : (int) round($r['income'] * $s / 100) * 100;
                $left -= $amt;
                $channels[] = ['name' => $name, 'note' => $note, 'amount' => DemoData::money($amt), 'pct' => (int) round($s * 100).'%', 'w' => (int) round($s / 0.48 * 100)];
            }

            $maxCat = max($r['cats']) ?: 1;
            $cats = [];
            foreach (self::EXPENSE_CATEGORIES as $i => $name) {
                $v = $r['cats'][$i];
                $share = $expense ? (int) round($v / $expense * 100) : 0;
                $cats[] = ['name' => $name, 'amount' => DemoData::money($v), 'w' => (int) round($v / $maxCat * 100), 'tip' => $name.': '.DemoData::money($v).' ('.$share.'% of expenses)'];
            }

            $out[$key] = [
                'income' => DemoData::money($r['income']),
                'income_sub' => DemoData::number($r['orders']).' orders · online + POS',
                'expense' => DemoData::money($expense),
                'gross' => DemoData::money($gross),
                'gross_sub' => 'Sales '.DemoData::money($r['income']).' − COGS '.DemoData::money($r['cogs']),
                'net' => DemoData::money($net),
                'net_negative' => $net < 0,
                'channels' => $channels,
                'cats' => $cats,
            ];
        }

        return $out;
    }

    /** Fixed (not range-dependent) KPI tiles. */
    public static function balances(): array
    {
        return [
            ['label' => 'COD receivable from courier', 'value' => DemoData::money(126850), 'sub' => '94 delivered parcels not yet settled', 'tone' => 'default'],
            ['label' => 'Supplier payable', 'value' => DemoData::money(238000), 'sub' => '3 suppliers · next due 10 Oct', 'tone' => 'warn'],
        ];
    }

    /** Income vs expenses for the last 6 months, ৳ lakh (expenses exclude cost of goods). */
    public static function months(): array
    {
        $inc = [15.4, 22.6, 16.1, 13.8, 17.5, 18.4];
        $exp = [4.4, 5.6, 4.6, 4.3, 4.6, 4.86];
        $short = ['Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'];
        $full = ['April', 'May', 'June', 'July', 'August', 'September'];
        $lk = fn (float $v) => '৳'.rtrim(rtrim(number_format($v, 2, '.', ''), '0'), '.').' lakh';

        return array_map(fn ($i) => [
            'label' => $short[$i],
            'full' => $full[$i].' 2026',
            'income' => $inc[$i],
            'expense' => $exp[$i],
            'income_text' => $lk($inc[$i]),
            'expense_text' => $lk($exp[$i]),
            'diff_text' => $lk($inc[$i] - $exp[$i]),
        ], array_keys($inc));
    }

    public static function expenseCategories(): array
    {
        return self::EXPENSE_CATEGORIES;
    }

    public static function paymentMethods(): array
    {
        return ['Cash', 'Bank transfer', 'bKash', 'Nagad', 'Card'];
    }

    /** Recent expenses (newest first). */
    public static function recentExpenses(): array
    {
        return [
            ['date' => '4 Oct 2026', 'cat' => 'Packaging', 'note' => 'Poly mailers, 2,000 pcs', 'method' => 'Cash', 'amount' => DemoData::money(9800), 'receipt' => 'View'],
            ['date' => '2 Oct 2026', 'cat' => 'Marketing', 'note' => 'Facebook ads top-up', 'method' => 'Card', 'amount' => DemoData::money(25000), 'receipt' => 'View'],
            ['date' => '2 Oct 2026', 'cat' => 'Courier charges', 'note' => 'Pathao delivery charges, week 39', 'method' => 'Deducted at settlement', 'amount' => DemoData::money(18600), 'receipt' => 'View'],
            ['date' => '1 Oct 2026', 'cat' => 'Rent', 'note' => 'Shop rent — October', 'method' => 'Bank transfer', 'amount' => DemoData::money(120000), 'receipt' => 'View'],
            ['date' => '1 Oct 2026', 'cat' => 'Marketing', 'note' => 'Product photo shoot, Puja collection', 'method' => 'bKash', 'amount' => DemoData::money(13000), 'receipt' => 'Add'],
            ['date' => '30 Sep 2026', 'cat' => 'Salary', 'note' => 'Salary — September, 8 staff', 'method' => 'Bank transfer', 'amount' => DemoData::money(185000), 'receipt' => 'View'],
        ];
    }

    /** Opening balance before the ledger below. */
    public static function openingBalance(): array
    {
        return ['date' => '30 Sep', 'amount' => 642300];
    }

    /** Cash + bank + wallet ledger, newest first, with running balance. type = income|expense. */
    public static function transactions(): array
    {
        $rows = [
            ['30 Sep', 'Courier COD settlement — Pathao, 86 parcels', 'STL-PTH-0930', 'Bank transfer', 86450],
            ['30 Sep', 'Salary — September, 8 staff', 'EXP-0931', 'Bank transfer', -185000],
            ['1 Oct', 'Shop rent — October', 'EXP-1001', 'Bank transfer', -120000],
            ['1 Oct', 'POS sales — day close', 'POS-Z-1001', 'Cash + card + bKash', 16240],
            ['2 Oct', 'Facebook ads top-up', 'EXP-1002', 'Card', -25000],
            ['2 Oct', 'Online prepaid — gateway settlement', 'PG-1002', 'bKash', 12380],
            ['3 Oct', 'Supplier payment — PO-0412 fabric', 'SUP-0412', 'Bank transfer', -80000],
            ['3 Oct', 'Courier COD settlement — Steadfast, 52 parcels', 'STL-SFD-1003', 'Bank transfer', 54120],
            ['4 Oct', 'Packaging — poly mailers, 2,000 pcs', 'EXP-1004', 'Cash', -9800],
            ['4 Oct', 'POS sales — day close', 'POS-Z-1004', 'Cash + card + bKash', 18850],
        ];

        $bal = self::openingBalance()['amount'];
        $out = [];
        foreach ($rows as [$date, $desc, $ref, $method, $amt]) {
            $bal += $amt;
            $out[] = [
                'date' => $date.' 2026',
                'type' => $amt > 0 ? 'income' : 'expense',
                'desc' => $desc,
                'ref' => $ref,
                'method' => $method,
                'amount' => ($amt > 0 ? '+' : '').DemoData::money($amt),
                'balance' => DemoData::money($bal),
            ];
        }

        return array_reverse($out);
    }
}
