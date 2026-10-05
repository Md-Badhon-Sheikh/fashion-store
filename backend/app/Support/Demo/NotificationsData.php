<?php

namespace App\Support\Demo;

/**
 * Sample data for SMS & email notifications (design: Notifications.dc.html).
 *
 * Template texts contain literal placeholders such as {name} and {order_id};
 * they are replaced with real values when a message is sent.
 * Replace these methods with settings / notification-log queries later.
 */
final class NotificationsData
{
    /** Placeholder chips shown above the template editor. */
    public static function placeholders(): array
    {
        return ['name', 'order_id', 'amount', 'courier', 'tracking_id'];
    }

    /** Values used to render the preview and count SMS characters. */
    public static function sampleValues(): array
    {
        return [
            'name' => 'Customer',
            'order_id' => 'WB-10482',
            'amount' => '4,705',
            'courier' => 'Steadfast',
            'tracking_id' => 'SF-58213049',
            'otp' => '482913',
        ];
    }

    /** Header cards: SMS balance, SMTP status, sent today, failed today. */
    public static function stats(): array
    {
        return [
            'sms_left' => 4820,
            'sms_left_pct' => 48,
            'sms_gateway' => '[SMS GATEWAY]',
            'sms_days_left' => 34,
            'sms_alert_below' => 500,
            'smtp_host' => 'smtp.[DOMAIN]',
            'smtp_from' => '[EMAIL]',
            'smtp_port' => 587,
            'smtp_encryption' => 'TLS',
            'smtp_last_test' => '3 Oct, 6:10 PM · passed',
            'sent_sms' => 142,
            'sent_email' => 86,
            'sent_breakdown' => 'OTP 38 · Order updates 97 · Admin alerts 7',
            'failed' => 3,
            'failed_note' => 'Delivery rate 98.7% · auto-retry 2 times',
        ];
    }

    /** Notification channels (columns of the event matrix). */
    public static function channels(): array
    {
        return ['Customer SMS', 'Customer email', 'Admin SMS', 'Admin email'];
    }

    /**
     * Event × channel matrix. `on` holds one value per channel:
     * true / false = switch state, null = not applicable.
     * `template` is the key of the SMS template edited by the row's Edit link.
     */
    public static function events(): array
    {
        return [
            ['name' => 'Registration OTP', 'note' => 'Phone verification code', 'template' => 'reg_otp', 'on' => [true, null, null, null]],
            ['name' => 'Password reset OTP', 'note' => 'Valid for 5 minutes', 'template' => 'reset_otp', 'on' => [true, false, null, null]],
            ['name' => 'Order placed', 'note' => 'Online store + Facebook orders', 'template' => 'placed', 'on' => [true, true, null, null]],
            ['name' => 'Order confirmed', 'note' => 'After phone confirmation', 'template' => 'confirmed', 'on' => [true, true, null, null]],
            ['name' => 'Processing', 'note' => 'Packing started', 'template' => 'processing', 'on' => [false, false, null, null]],
            ['name' => 'Shipped (with tracking)', 'note' => 'Courier + tracking ID', 'template' => 'shipped', 'on' => [true, true, null, null]],
            ['name' => 'Delivered', 'note' => 'Includes review request link', 'template' => 'delivered', 'on' => [true, false, null, null]],
            ['name' => 'Cancelled', 'note' => 'By customer or admin', 'template' => 'cancelled', 'on' => [true, true, false, true]],
            ['name' => 'Return / refund update', 'note' => 'Request, approved, refunded', 'template' => 'return', 'on' => [true, true, false, true]],
            ['name' => 'Admin: new order', 'note' => 'To shop manager', 'template' => null, 'on' => [null, null, true, true]],
            ['name' => 'Admin: low stock', 'note' => 'Variant below threshold', 'template' => null, 'on' => [null, null, false, true]],
            ['name' => 'Admin: daily sales summary', 'note' => 'Every day at 10:00 PM', 'template' => null, 'on' => [null, null, true, true]],
        ];
    }

    /**
     * Customer SMS templates, in the order of the template picker.
     * English templates write "Tk" (GSM 7-bit, 160 chars per part); Bangla is Unicode (70 per part).
     */
    public static function templates(): array
    {
        return [
            'shipped' => [
                'option' => 'Order shipped (with tracking)',
                'title' => 'Order shipped',
                'subtitle' => 'Sent to the customer when status changes to Shipped',
                'en' => 'Hi {name}, your order #{order_id} (Tk {amount}) has been shipped via {courier}. Tracking ID: {tracking_id}. Thank you - YOUR BRAND',
                'bn' => 'প্রিয় {name}, আপনার অর্ডার #{order_id} (৳{amount}) {courier}-এর মাধ্যমে পাঠানো হয়েছে। ট্র্যাকিং: {tracking_id}। ধন্যবাদ - YOUR BRAND',
            ],
            'placed' => [
                'option' => 'Order placed',
                'title' => 'Order placed',
                'subtitle' => 'Sent to the customer right after checkout',
                'en' => 'Hi {name}, thank you for your order #{order_id} (Tk {amount}). We will call you shortly to confirm it. - YOUR BRAND',
                'bn' => 'প্রিয় {name}, আপনার অর্ডার #{order_id} (৳{amount}) গ্রহণ করা হয়েছে। নিশ্চিত করতে আমরা শীঘ্রই কল করব। - YOUR BRAND',
            ],
            'confirmed' => [
                'option' => 'Order confirmed',
                'title' => 'Order confirmed',
                'subtitle' => 'Sent to the customer when status changes to Confirmed',
                'en' => 'Hi {name}, your order #{order_id} (Tk {amount}) is confirmed and will be packed soon. - YOUR BRAND',
                'bn' => 'প্রিয় {name}, আপনার অর্ডার #{order_id} (৳{amount}) নিশ্চিত হয়েছে। শীঘ্রই প্যাক করা হবে। - YOUR BRAND',
            ],
            'processing' => [
                'option' => 'Processing',
                'title' => 'Processing',
                'subtitle' => 'Sent to the customer when packing starts',
                'en' => 'Hi {name}, we are packing your order #{order_id}. You will get the tracking ID once it ships. - YOUR BRAND',
                'bn' => 'প্রিয় {name}, আপনার অর্ডার #{order_id} প্যাক করা হচ্ছে। পাঠানোর পর ট্র্যাকিং আইডি জানানো হবে। - YOUR BRAND',
            ],
            'delivered' => [
                'option' => 'Delivered',
                'title' => 'Delivered',
                'subtitle' => 'Sent to the customer when status changes to Delivered',
                'en' => 'Hi {name}, your order #{order_id} has been delivered. Rate your purchase: [DOMAIN]/review - YOUR BRAND',
                'bn' => 'প্রিয় {name}, আপনার অর্ডার #{order_id} ডেলিভারি হয়েছে। রিভিউ দিন: [DOMAIN]/review - YOUR BRAND',
            ],
            'cancelled' => [
                'option' => 'Cancelled',
                'title' => 'Cancelled',
                'subtitle' => 'Sent to the customer when an order is cancelled',
                'en' => 'Hi {name}, your order #{order_id} has been cancelled. Call us if this was a mistake. - YOUR BRAND',
                'bn' => 'প্রিয় {name}, আপনার অর্ডার #{order_id} বাতিল করা হয়েছে। ভুল হলে আমাদের কল করুন। - YOUR BRAND',
            ],
            'reg_otp' => [
                'option' => 'Registration OTP',
                'title' => 'Registration OTP',
                'subtitle' => 'Phone verification code for new accounts',
                'en' => 'Your YOUR BRAND verification code is {otp}. It expires in 5 minutes. Do not share it.',
                'bn' => 'YOUR BRAND যাচাই কোড: {otp}। ৫ মিনিটের মধ্যে ব্যবহার করুন। কাউকে জানাবেন না।',
            ],
            'reset_otp' => [
                'option' => 'Password reset OTP',
                'title' => 'Password reset OTP',
                'subtitle' => 'Sent when a customer resets the password',
                'en' => 'Your YOUR BRAND password reset code is {otp}. Valid for 5 minutes.',
                'bn' => 'YOUR BRAND পাসওয়ার্ড রিসেট কোড: {otp}। ৫ মিনিট পর্যন্ত বৈধ।',
            ],
            'return' => [
                'option' => 'Return / refund update',
                'title' => 'Return / refund update',
                'subtitle' => 'Sent when a return request is approved or refunded',
                'en' => 'Hi {name}, your return for order #{order_id} is approved. Refund of Tk {amount} is on the way. - YOUR BRAND',
                'bn' => 'প্রিয় {name}, অর্ডার #{order_id}-এর রিটার্ন অনুমোদিত হয়েছে। ৳{amount} ফেরত দেওয়া হবে। - YOUR BRAND',
            ],
        ];
    }

    /** SMS gateway form values. */
    public static function gateway(): array
    {
        return [
            'providers' => ['[SMS GATEWAY]', 'SSL Wireless', 'BulkSMSBD', 'Alpha SMS', 'Custom HTTP API'],
            'api_key' => 'placeholder-api-key-0000',
            'sender_id' => 'YOURBRAND',
            'types' => ['Masking', 'Non-masking'],
            'low_balance' => 500,
        ];
    }

    /** SMTP form values. */
    public static function smtp(): array
    {
        return [
            'host' => 'smtp.[DOMAIN]',
            'port' => '587',
            'encryptions' => ['TLS', 'SSL', 'None'],
            'username' => '[EMAIL]',
            'password' => 'placeholder-pass',
            'from_name' => 'YOUR BRAND',
            'from_email' => '[EMAIL]',
        ];
    }

    /** Recent send log, newest first. Status: delivered | failed | queued. */
    public static function logs(): array
    {
        return [
            ['time' => '2:14 PM', 'channel' => 'SMS', 'to' => '017•••••678', 'event' => 'Shipped', 'detail' => '#WB-10476 · Steadfast', 'status' => 'delivered'],
            ['time' => '2:09 PM', 'channel' => 'SMS', 'to' => '019•••••204', 'event' => 'Registration OTP', 'detail' => 'Code sent', 'status' => 'delivered'],
            ['time' => '1:58 PM', 'channel' => 'Email', 'to' => 'c•••••@gmail.com', 'event' => 'Order confirmed', 'detail' => '#WB-10480 · invoice PDF', 'status' => 'delivered'],
            ['time' => '1:41 PM', 'channel' => 'SMS', 'to' => '018•••••551', 'event' => 'Order confirmed', 'detail' => '#WB-10480', 'status' => 'failed'],
            ['time' => '1:20 PM', 'channel' => 'SMS', 'to' => '016•••••317', 'event' => 'Delivered', 'detail' => '#WB-10471', 'status' => 'queued'],
            ['time' => '12:58 PM', 'channel' => 'SMS', 'to' => '017•••••678', 'event' => 'Order confirmed', 'detail' => '#WB-10482', 'status' => 'delivered'],
            ['time' => '12:41 PM', 'channel' => 'Email', 'to' => 'Admin', 'event' => 'Admin: new order', 'detail' => '#WB-10482 · ৳4,705', 'status' => 'delivered'],
            ['time' => '11:02 AM', 'channel' => 'SMS', 'to' => '015•••••903', 'event' => 'Password reset OTP', 'detail' => 'Code sent', 'status' => 'failed'],
        ];
    }

    /**
     * SMS length for a template filled with the sample values
     * (same rules as the live counter in the view).
     *
     * @return array{preview: string, chars: int, parts: int, unicode: bool, per_part: int}
     */
    public static function smsInfo(string $template): array
    {
        $preview = $template;
        foreach (self::sampleValues() as $key => $value) {
            $preview = str_replace('{'.$key.'}', $value, $preview);
        }

        $unicode = (bool) preg_match('/[^\x00-\x7F]/u', $preview);
        $len = mb_strlen($preview, 'UTF-8');
        $parts = $unicode
            ? ($len <= 70 ? 1 : (int) ceil($len / 67))
            : ($len <= 160 ? 1 : (int) ceil($len / 153));

        return [
            'preview' => $preview,
            'chars' => $len,
            'parts' => $parts,
            'unicode' => $unicode,
            'per_part' => $unicode ? ($parts > 1 ? 67 : 70) : ($parts > 1 ? 153 : 160),
        ];
    }
}
