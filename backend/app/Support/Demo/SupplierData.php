<?php

namespace App\Support\Demo;

use App\Support\DemoData;

/**
 * Sample data for Suppliers (design: Suppliers.dc.html); also used by Stock-in.
 * Money values are ints (Taka).
 */
final class SupplierData
{
    /** Payment methods and the reference placeholder each one pre-fills. */
    public static function paymentRefs(string $cashVoucher = 'Voucher PV-0091'): array
    {
        return [
            'Cash' => $cashVoucher,
            'bKash' => 'TrxID [BKASH-TRX-ID]',
            'Bank transfer' => 'Bank ref [BANK-REF]',
            'Cheque' => 'Cheque no. [CHEQUE-NO]',
        ];
    }

    public static function kpis(): array
    {
        return [
            ['label' => 'Suppliers', 'value' => '6', 'sub' => '6 active · 0 inactive', 'tone' => 'default'],
            ['label' => 'Total payable due', 'value' => DemoData::money(112690), 'sub' => 'Owed to 4 suppliers', 'tone' => 'pending'],
            ['label' => 'Purchased this month', 'value' => DemoData::money(110940), 'sub' => '2 purchases · 84 pcs', 'tone' => 'default'],
            ['label' => 'Paid this month', 'value' => DemoData::money(92300), 'sub' => 'Cash '.DemoData::money(20000).' · Bank '.DemoData::money(72300), 'tone' => 'success'],
        ];
    }

    /**
     * Suppliers keyed by id, each with its ledger (opening balance + purchases and
     * payments with a running due).
     */
    public static function suppliers(): array
    {
        $raw = [
            ['s03', 'Supplier 03', 'S3', 'Panjabi & shirts', 'Islampur, Dhaka', '017•••••412', 38, 984200, 18640, '4 Oct 2026', '#PO-0042', 'Panjabi & Shirts (Dhaka)'],
            ['s05', 'Supplier 05', 'S5', 'Three-piece', 'Keraniganj', '018•••••093', 22, 412600, 41850, '28 Sep 2026', '#PO-0040', 'Three-Piece (Keraniganj)'],
            ['s01', 'Supplier 01', 'S1', 'Fabrics & kurti', 'Narayanganj', '019•••••557', 31, 726300, 0, '1 Oct 2026', '#PO-0041', 'Fabrics & Kurti (Narayanganj)'],
            ['s02', 'Supplier 02', 'S2', 'T-shirts & polo', 'Gazipur', '015•••••220', 26, 388900, 0, '24 Sep 2026', '#PO-0039', 'T-Shirts & Polo (Gazipur)'],
            ['s04', 'Supplier 04', 'S4', 'Kids wear', 'Mirpur, Dhaka', '016•••••781', 18, 205400, 23200, '12 Sep 2026', '#PO-0038', 'Kids wear (Dhaka)'],
            ['s06', 'Supplier 06', 'S6', 'Pants & chino', 'Savar', '013•••••346', 9, 148000, 29000, '30 Aug 2026', '#PO-0035', 'Pants & Chino (Savar)'],
        ];

        // [date, kind, ref, note, purchase, payment, opening]
        $ledgers = [
            's03' => [['1 Sep 2026', 'Opening', '', '', 0, 0, 12400], ['1 Sep 2026', 'Purchase', '#PO-0036', '46 pcs · panjabi', 46200, 0], ['3 Sep 2026', 'Payment', 'PMT-0071', 'Bank transfer', 0, 40000], ['20 Sep 2026', 'Payment', 'PMT-0079', 'bKash', 0, 18600], ['4 Oct 2026', 'Purchase', '#PO-0042', '24 pcs · panjabi, shirts', 38640, 0], ['4 Oct 2026', 'Payment', 'PMT-0084', 'Cash', 0, 20000]],
            's05' => [['1 Sep 2026', 'Opening', '', '', 0, 0, 0], ['10 Sep 2026', 'Purchase', '#PO-0037', '30 pcs · lawn three-piece', 36400, 0], ['15 Sep 2026', 'Payment', 'PMT-0076', 'Bank transfer', 0, 36400], ['28 Sep 2026', 'Purchase', '#PO-0040', '36 pcs · three-piece', 41850, 0]],
            's01' => [['1 Sep 2026', 'Opening', '', '', 0, 0, 0], ['1 Oct 2026', 'Purchase', '#PO-0041', '60 pcs · kurti', 72300, 0], ['1 Oct 2026', 'Payment', 'PMT-0082', 'Bank transfer', 0, 72300]],
            's02' => [['1 Sep 2026', 'Opening', '', '', 0, 0, 0], ['24 Sep 2026', 'Purchase', '#PO-0039', '48 pcs · polo', 28900, 0], ['24 Sep 2026', 'Payment', 'PMT-0080', 'Cash', 0, 28900]],
            's04' => [['1 Sep 2026', 'Opening', '', '', 0, 0, 0], ['12 Sep 2026', 'Purchase', '#PO-0038', '40 pcs · kids panjabi', 35200, 0], ['14 Sep 2026', 'Payment', 'PMT-0074', 'Cash', 0, 12000]],
            's06' => [['1 Aug 2026', 'Opening', '', '', 0, 0, 0], ['30 Aug 2026', 'Purchase', '#PO-0035', '32 pcs · chino', 44000, 0], ['2 Sep 2026', 'Payment', 'PMT-0069', 'bKash', 0, 15000]],
        ];

        $out = [];
        foreach ($raw as [$id, $name, $initials, $type, $area, $phone, $products, $purchased, $due, $last, $lastPo, $short]) {
            $bal = 0;
            $totalPurchase = 0;
            $totalPayment = 0;
            $entries = [];
            foreach ($ledgers[$id] as $e) {
                [$date, $kind, $ref, $note, $purchase, $payment] = $e;
                if ($kind === 'Opening') {
                    $bal = $e[6];
                    $entries[] = ['date' => $date, 'kind' => 'Opening balance', 'tone' => 'gray', 'ref' => '—', 'href' => null, 'note' => 'Brought forward', 'purchase' => '', 'payment' => '', 'bal' => DemoData::money($bal), 'opening' => true];

                    continue;
                }
                $bal += $purchase - $payment;
                $totalPurchase += $purchase;
                $totalPayment += $payment;
                $isPurchase = $kind === 'Purchase';
                $entries[] = [
                    'date' => $date, 'kind' => $kind, 'tone' => $isPurchase ? 'blue' : 'green', 'ref' => $ref,
                    'href' => $isPurchase ? route('admin.stock-in') : null, 'note' => $note,
                    'purchase' => $purchase ? DemoData::money($purchase) : '', 'payment' => $payment ? DemoData::money($payment) : '',
                    'bal' => DemoData::money($bal), 'opening' => false,
                ];
            }

            $out[$id] = [
                'id' => $id, 'name' => $name, 'initials' => $initials, 'type' => $type, 'area' => $area, 'phone' => $phone,
                'products' => $products, 'purchased' => $purchased, 'paid' => $purchased - $due, 'due' => $due,
                'last' => $last, 'last_po' => $lastPo, 'short' => $short,
                'ledger' => $entries, 'ledger_purchases' => $totalPurchase, 'ledger_payments' => $totalPayment,
            ];
        }

        return $out;
    }
}
