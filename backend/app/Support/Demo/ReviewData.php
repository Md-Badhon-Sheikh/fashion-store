<?php

namespace App\Support\Demo;

use App\Support\DemoData;

/**
 * Sample data for review moderation (design: Reviews.dc.html).
 *
 * Shape of one review: id, product{name, sku, image_url, tone}, variant, order,
 * rating (1–5), verified, date, phone (masked), status (pending|published|hidden),
 * text, photos (count), reply|null, draft|null, hidden_reason|null.
 * Moderation (publish / hide / reply / delete) runs client-side in Alpine;
 * wire it to POST routes when reviews come from the database.
 */
final class ReviewData
{
    /** Products referenced by the sample reviews (name, SKU for the edit link, image, tone). */
    private static function products(): array
    {
        $p = fn (string $name, string $sku, string $img, string $tone) => [
            'name' => $name, 'sku' => $sku, 'image_url' => DemoData::img($img), 'tone' => $tone,
        ];

        return [
            'panjabi' => $p('Embroidered Cotton Panjabi', 'PNJ-1024', 'p_panjabi_offwhite', '#E4DCCF'),
            'kurti' => $p('Block Print Cotton Kurti', 'KRT-0882', 'p_kurti_block', '#E8D8D6'),
            'polo' => $p('Premium Polo T-Shirt', 'TSH-0219', 'p_polo', '#DCE0D6'),
            'lawn' => $p('Printed Lawn Three-Piece', 'TPC-0311', 'p_threepiece_lawn', '#E7D6DA'),
            'kids' => $p('Kids Cotton Panjabi Set', 'KPN-0105', 'p_kids_panjabi', '#E8E0CF'),
            'shirt' => $p('Slim Fit Oxford Shirt', 'SHT-0457', 'p_shirt_oxford', '#D3DCE4'),
            'chino' => $p('Stretch Cotton Chino', 'PNT-0601', 'p_chino', '#DDD6C8'),
            'pajama' => $p('Cotton Pajama', 'PNJ-1055', 'p_pajama', '#E6E3DC'),
            'tshirt' => $p('Basic Cotton T-Shirt', 'TSH-0225', 'p_tshirt_basic', '#E1E3E6'),
        ];
    }

    /** The reviews on screen, newest first. */
    public static function reviews(): array
    {
        $products = self::products();
        $r = fn (string $id, string $p, string $variant, string $order, int $rating, bool $verified, string $date, string $sortDate, string $phone, string $status, int $photos, string $text, ?string $reply = null, ?string $draft = null, ?string $hiddenReason = null) => [
            'id' => $id,
            'product' => $products[$p] + ['edit_url' => route('admin.products.edit', $products[$p]['sku'])],
            'variant' => $variant,
            'order' => $order,
            'rating' => $rating,
            'verified' => $verified,
            'date' => $date,
            'sort_date' => $sortDate,
            'phone' => $phone,
            'status' => $status,
            'photos' => $photos,
            'text' => $text,
            'reply' => $reply,
            'draft' => $draft,
            'hidden_reason' => $hiddenReason,
        ];

        return [
            $r('rv1', 'panjabi', 'Size M · Off-white', '#WB-10377', 5, true, '4 Oct 2026', '2026-10-04', '017•••••678', 'pending', 2,
                'Fabric is soft and the embroidery is neat. Fits as per the size chart. Delivery took 2 days inside Dhaka.'),
            $r('rv2', 'kurti', 'Size L · Maroon', '#WB-10391', 4, true, '3 Oct 2026', '2026-10-03', '016•••••208', 'pending', 0,
                'Colour is exactly like the photo. Stitching on the neckline could be a little better, but good value for the price.'),
            $r('rv3', 'polo', 'Size XL · Navy', '#WB-10402', 2, true, '3 Oct 2026', '2026-10-03', '018•••••774', 'pending', 1,
                'Colour faded a bit after the first wash. Size and collar are fine.',
                null, 'Sorry to hear this. Please wash the polo inside out in cold water. We have sent you an SMS to arrange a free exchange for this piece. — YOUR BRAND'),
            $r('rv4', 'lawn', 'Free size · Pink', '#WB-10429', 3, false, '2 Oct 2026', '2026-10-02', '019•••••630', 'pending', 0,
                'Dupatta is lovely, but the colour is darker than shown on the website.'),
            $r('rv5', 'kids', 'Age 6Y · Cream', '#WB-10351', 5, true, '2 Oct 2026', '2026-10-02', '013•••••119', 'pending', 0,
                'My son loved it for the wedding. Soft fabric, no itching. Got many compliments.'),
            $r('rv6', 'shirt', 'Size L · Sky blue', '#POS-2187', 5, true, '28 Sep 2026', '2026-09-28', '015•••••903', 'published', 0,
                'Best oxford shirt at this price. Bought two more colours at the shop.',
                'Thank you! New colours arrive next week.'),
            $r('rv7', 'chino', 'Waist 34 · Khaki', '#WB-10380', 4, true, '27 Sep 2026', '2026-09-27', '017•••••351', 'published', 0,
                'Good stretch and comfortable for office. Exchange from 32 to 34 was quick.'),
            $r('rv8', 'tshirt', 'Size M · Black', '—', 1, false, '26 Sep 2026', '2026-09-26', '011•••••907', 'hidden', 0,
                'Cheap t-shirts available, message my page for wholesale price.', null, null, 'Spam / promotion'),
            $r('rv9', 'pajama', 'Size L · White', '#WB-10344', 1, true, '24 Sep 2026', '2026-09-24', '019•••••588', 'hidden', 0,
                'Courier came one day late and the rider was rude.', null, null, 'About courier, not the product. Forwarded to delivery team.'),
        ];
    }

    /** Reviews already handled that are not in the sample list (added to the tab counts). */
    public static function archivedCounts(): array
    {
        return ['pending' => 0, 'published' => 1245, 'hidden' => 15];
    }

    /** Summary tiles above the tabs. */
    public static function summary(): array
    {
        return [
            'average' => 4.6,
            'total' => 1270,
            'oldest_pending' => '2 Oct 2026',
            'this_month' => 86,
            'verified_share' => '92%',
            'low_share' => '4%',
        ];
    }

    /** Product filter options (names shown in the design). */
    public static function productOptions(): array
    {
        return ['Embroidered Cotton Panjabi', 'Block Print Cotton Kurti', 'Premium Polo T-Shirt', 'Printed Lawn Three-Piece', 'Kids Cotton Panjabi Set'];
    }
}
