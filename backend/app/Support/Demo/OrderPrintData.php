<?php

namespace App\Support\Demo;

use App\Support\DemoData;
use DateTimeImmutable;

/**
 * Data for the printable order documents (design: Invoice.dc.html):
 * A4 invoice, 80 mm POS receipt and packing slip.
 *
 * document($order) derives everything the three views need from one
 * DemoData::orders() row, so the printed totals always match the order.
 * When wiring real data, keep this shape (or move it to an API Resource).
 */
final class OrderPrintData
{
    /** Seller details printed on every document (placeholders until Settings are wired). */
    public static function store(): array
    {
        return [
            'name' => 'YOUR BRAND',
            'initials' => 'YB',
            'address' => '[ADDRESS]',
            'phone' => '[PHONE]',
            'email' => '[EMAIL]',
            'website' => '[WEBSITE]',
            'bin' => '[BIN NUMBER]',
            'exchange_days' => 7,
        ];
    }

    /** Everything the invoice / receipt / packing slip print for one order. */
    public static function document(array $order): array
    {
        $placed = new DateTimeImmutable($order['placed_at']);
        $isPos = $order['source'] === 'POS';
        $paid = $order['amount'] - $order['due'];
        $digits = preg_replace('/\D/', '', $order['id']);
        [$counter, $cashier] = $isPos
            ? array_pad(explode(' · ', $order['customer']['area'], 2), 2, '—')
            : [$order['source'].' order', '—'];

        // Discount rows: coupon first, then any manual discount.
        $discounts = [];
        if ($order['coupon']) {
            $discounts[] = ['label' => 'coupon '.$order['coupon']['code'], 'code' => $order['coupon']['code'], 'amount' => $order['coupon']['amount']];
        }
        if ($order['discount'] > 0) {
            $discounts[] = ['label' => $isPos ? 'counter discount' : 'manual', 'code' => 'DISC', 'amount' => $order['discount']];
        }

        $email = $order['customer']['email'];

        return [
            'invoice_no' => 'INV-'.$placed->format('Y').'-'.$digits,
            'invoice_date' => $order['date'],
            'placed' => $order['date'].', '.$order['time'],
            'receipt_date' => $placed->format('d/m/Y g:i A'),
            'is_pos' => $isPos,
            'counter' => $counter,
            'cashier' => $cashier,
            'lines' => count($order['items']),
            'pieces' => $order['items_count'],
            'discounts' => $discounts,
            'paid' => $paid,
            'due' => $order['due'],
            'is_cod' => $order['payment_method'] === 'COD',
            'balance_label' => $order['due'] > 0
                ? $order['payment_method_label'].' · due'
                : 'Paid in full · '.$order['payment_method'],
            'balance' => $order['due'] > 0 ? $order['due'] : $paid,
            'email' => $email && $email !== 'No email' ? $email : null,
            'address_lines' => explode("\n", $order['customer']['address']),
            'address_inline' => str_replace("\n", ', ', $order['customer']['address']),
            'tenders' => self::tenders($order),
        ];
    }

    /**
     * How the order was paid, as printed on the 80 mm receipt.
     * Returns ['rows' => [[label, amount, note|null]], 'received' => int|null, 'change' => int|null].
     * Transaction IDs / approval codes are sample values.
     */
    public static function tenders(array $order): array
    {
        $amount = $order['amount'];

        return match ($order['payment_method']) {
            'Cash + bKash' => (function () use ($amount) {
                $cash = min(3000, $amount);
                $received = (int) (ceil(($cash + 1) / 500) * 500);

                return [
                    'rows' => [['bKash', $amount - $cash, 'TrxID 9J7K2L4M0P'], ['Cash', $cash, null]],
                    'received' => $received,
                    'change' => $received - $cash,
                ];
            })(),
            'Cash' => (function () use ($amount) {
                $received = (int) (ceil($amount / 500) * 500);

                return ['rows' => [['Cash', $amount, null]], 'received' => $received, 'change' => $received - $amount];
            })(),
            'Card' => ['rows' => [['Card', $amount, 'Approval 4821 · VISA ••4821']], 'received' => null, 'change' => null],
            'bKash', 'Nagad' => ['rows' => [[$order['payment_method'], $amount, 'TrxID 9JX4K2L8QP']], 'received' => null, 'change' => null],
            default => ['rows' => [[$order['payment_method_label'], $order['due'] > 0 ? 0 : $amount, $order['due'] > 0 ? 'Due '.DemoData::number($order['due']).'.00 on delivery' : null]], 'received' => null, 'change' => null],
        };
    }

    /** 2450 => "2,450.00" (receipt style, en-IN grouping). */
    public static function receiptAmount(int|float $n): string
    {
        return ($n < 0 ? '-' : '').DemoData::number(abs($n)).'.00';
    }
}
