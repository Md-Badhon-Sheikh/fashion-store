<?php

namespace App\Support\Demo;

use App\Support\DemoData;

/**
 * Sample data for Returns & exchanges (Returns.dc.html).
 *
 * Replace requests() with a query on a return_requests table (joined to the
 * order item + variant stock) returning the same keys when wiring real data.
 */
final class ReturnsData
{
    /** Tabs: key => [label, total count incl. requests not on this page, statuses in the tab]. */
    public static function tabs(): array
    {
        return [
            'requests' => ['label' => 'Requests', 'count' => 3, 'statuses' => ['requested']],
            'approved' => ['label' => 'Approved', 'count' => 4, 'statuses' => ['approved']],
            'received' => ['label' => 'Received', 'count' => 2, 'statuses' => ['received']],
            'done' => ['label' => 'Refunded / Exchanged', 'count' => 57, 'statuses' => ['refunded', 'exchanged']],
            'rejected' => ['label' => 'Rejected', 'count' => 6, 'statuses' => ['rejected']],
        ];
    }

    /** Request status => chip tone (Returns design: requested is amber, approved blue …). */
    public static function tones(): array
    {
        return [
            'requested' => 'amber',
            'approved' => 'blue',
            'received' => 'violet',
            'refunded' => 'green',
            'exchanged' => 'green',
            'rejected' => 'red',
        ];
    }

    /** Refund methods: key => [label, side field label, side field placeholder]. */
    public static function refundMethods(): array
    {
        return [
            'cash' => ['label' => 'Cash', 'field' => 'Paid at counter by', 'placeholder' => 'Sales Staff 01'],
            'bkash' => ['label' => 'bKash', 'field' => 'bKash number', 'placeholder' => '01XXXXXXXXX'],
            'credit' => ['label' => 'Store credit', 'field' => 'Credit expires', 'placeholder' => '30 days from today'],
        ];
    }

    /** Return policy window in days. */
    public const POLICY_DAYS = 7;

    /**
     * Requests, newest first.
     * Shape: id, order, order_id, order_exists, name, image_url, tone, variant, colour, sku, reason,
     * type (exchange|return), from, to, date, delivered, days, in_window, status, price, pay, phone,
     * channel, photos[{label, tone}], note, sizes[{label, stock, status, stock_label}], closed_note
     */
    public static function requests(): array
    {
        $p = [
            'panjabi' => ['Embroidered Cotton Panjabi', 'p_panjabi_offwhite', '#E4DCCF'],
            'lawn' => ['Printed Lawn Three-Piece', 'p_threepiece_lawn', '#E7D6DA'],
            'shirt' => ['Slim Fit Oxford Shirt', 'p_shirt_oxford', '#D3DCE4'],
            'polo' => ['Premium Polo T-Shirt', 'p_polo', '#DCE0D6'],
            'kurti' => ['Block Print Cotton Kurti', 'p_kurti_block', '#E8D8D6'],
            'chino' => ['Stretch Cotton Chino', 'p_chino', '#DDD6C8'],
            'kids' => ['Kids Cotton Panjabi Set', 'p_kids_panjabi', '#E8E0CF'],
            'pajama' => ['Cotton Pajama', 'p_pajama', '#E6E3DC'],
            'printed' => ['Printed Cotton Panjabi', 'p_panjabi_printed', '#DDD8CC'],
        ];

        // id, order, product, variant, colour, sku, reason, type, from, to, requested, delivered, days, status, price, pay, phone, channel, photos, note, sizes, closed note
        $defs = [
            ['RR-0146', 'WB-10438', 'panjabi', 'Size M · Off-white', 'Off-white', 'PNJ-1024-OW-M', 'Size too small', 'exchange', 'M', 'L', '4 Oct 2026, 11:20 AM', '1 Oct 2026', 3, 'requested', 2450, 'COD paid', '017•••••412', 'website', 2,
                'Fits tight on the chest. Please send size L in the same colour. Tags are still attached and it was not washed.',
                ['S' => 6, 'M' => 4, 'L' => 11, 'XL' => 2, 'XXL' => 0], null],
            ['RR-0145', 'WB-10429', 'lawn', 'Free size · Pink', 'Pink', 'TPC-0311-PK-FS', 'Colour different from photo', 'return', '', '', '3 Oct 2026, 7:45 PM', '30 Sep 2026', 3, 'requested', 2990, 'bKash paid', '019•••••630', 'website', 3,
                'The pink is much darker than the website photo. Unused, still in the original packet with dupatta.', [], null],
            ['RR-0144', 'POS-2263', 'shirt', 'Size L · Sky blue', 'Sky blue', 'SHT-0457-SB-L', 'Stitching defect on sleeve', 'exchange', 'L', 'L', '3 Oct 2026, 4:10 PM', '2 Oct 2026 (shop)', 1, 'requested', 1650, 'Cash paid', '015•••••903', 'phone call', 1,
                'Sleeve seam opened after first wear. Want the same shirt, same size.', ['S' => 5, 'M' => 8, 'L' => 3, 'XL' => 6], null],
            ['RR-0141', 'WB-10402', 'polo', 'Size XL · Navy', 'Navy', 'TSH-0219-NV-XL', 'Size too large', 'exchange', 'XL', 'L', '1 Oct 2026, 9:02 PM', '28 Sep 2026', 3, 'approved', 890, 'COD paid', '018•••••774', 'website', 1,
                'Too loose. Need L please.', ['S' => 12, 'M' => 20, 'L' => 14, 'XL' => 9, 'XXL' => 5], null],
            ['RR-0139', 'WB-10391', 'kurti', 'Size M · Maroon', 'Maroon', 'KRT-0882-MR-M', 'Did not like the fabric', 'return', '', '', '30 Sep 2026, 1:15 PM', '27 Sep 2026', 3, 'approved', 1350, 'COD paid', '016•••••208', 'website', 2,
                'Fabric feels thinner than expected.', [], null],
            ['RR-0137', 'WB-10380', 'chino', 'Waist 32 · Khaki', 'Khaki', 'CHN-0610-KH-32', 'Waist too tight', 'exchange', '32', '34', '29 Sep 2026, 6:30 PM', '26 Sep 2026', 3, 'received', 1990, 'Nagad paid', '017•••••351', 'website', 1,
                'Please send 34 waist.', ['30' => 4, '32' => 7, '34' => 5, '36' => 2], null],
            ['RR-0133', 'WB-10351', 'kids', 'Age 6Y · Cream', 'Cream', 'KPN-0105-CR-6Y', 'Size too small', 'exchange', '6Y', '8Y', '25 Sep 2026, 10:12 AM', '22 Sep 2026', 3, 'exchanged', 1150, 'COD paid', '013•••••119', 'website', 0,
                'Too short for my son.', ['4Y' => 3, '6Y' => 5, '8Y' => 4, '10Y' => 2], 'New size sent on 27 Sep with Steadfast. Old item restocked.'],
            ['RR-0131', 'WB-10344', 'pajama', 'Size L · White', 'White', 'PJM-0140-WH-L', 'Wrong item sent', 'return', '', '', '24 Sep 2026, 3:40 PM', '23 Sep 2026', 1, 'refunded', 890, 'bKash paid', '019•••••588', 'website', 2,
                'Ordered off-white, received white.', [], 'Refunded ৳890 via bKash on 26 Sep. Item restocked.'],
            ['RR-0128', 'WB-10310', 'printed', 'Size XL · Blue print', 'Blue print', 'PNJ-1102-BP-XL', 'Changed mind', 'return', '', '', '20 Sep 2026, 8:05 PM', '9 Sep 2026', 11, 'rejected', 2250, 'COD paid', '018•••••062', 'website', 2,
                'I want to return this.', [], 'Rejected on 21 Sep: request came 11 days after delivery (policy is 7 days). SMS sent to customer.'],
        ];

        $photoTones = ['#E2DED6', '#D9DEE2', '#E5DCDC'];
        $rows = [];
        foreach ($defs as $d) {
            [$id, $order, $prod, $variant, $colour, $sku, $reason, $type, $from, $to, $date, $delivered, $days, $status, $price, $pay, $phone, $channel, $photoCount, $note, $sizes, $closedNote] = $d;
            [$name, $img, $tone] = $p[$prod];

            $photos = [];
            for ($i = 1; $i <= $photoCount; $i++) {
                $photos[] = ['label' => 'Customer photo '.$i, 'tone' => $photoTones[$i - 1] ?? $photoTones[0]];
            }

            $rows[] = [
                'id' => $id,
                'order' => '#'.$order,
                'order_id' => $order,
                'order_exists' => DemoData::findOrder($order) !== null,
                'name' => $name,
                'image_url' => DemoData::img($img),
                'tone' => $tone,
                'variant' => $variant,
                'colour' => $colour,
                'sku' => $sku,
                'reason' => $reason,
                'type' => $type,
                'type_label' => $type === 'exchange' ? 'Exchange · '.$from.' → '.$to : 'Return · refund',
                'from' => $from,
                'to' => $to,
                'date' => $date,
                'delivered' => $delivered,
                'days' => $days,
                'in_window' => $days <= self::POLICY_DAYS,
                'status' => $status,
                'price' => $price,
                'pay' => $pay,
                'phone' => $phone,
                'channel' => $channel,
                'photos' => $photos,
                'note' => $note,
                'sizes' => array_map(fn ($label, $stock) => [
                    'label' => (string) $label,
                    'stock' => $stock,
                    'stock_label' => $stock === 0 ? 'Out of stock' : ($stock <= 3 ? 'Low · '.$stock.' left' : $stock.' in stock'),
                    'level' => $stock === 0 ? 'out' : ($stock <= 3 ? 'low' : 'ok'),
                ], array_keys($sizes), $sizes),
                'closed_note' => $closedNote,
            ];
        }

        return $rows;
    }
}
