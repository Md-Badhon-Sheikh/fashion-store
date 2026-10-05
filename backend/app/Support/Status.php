<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Maps any status word used in the admin (order, stock, payment, record states)
 * to one of the seven chip tones defined in public/css/admin.css.
 *
 * Usage in Blade: <x-admin.status-chip status="processing" />
 * Usage in Alpine: pass Status::toneMap() through @js(...) and build
 * the class as 'chip chip--' + map[key].
 */
final class Status
{
    /** status key => tone */
    private const TONES = [
        'pending' => 'amber',
        'unpaid' => 'amber',
        'low-stock' => 'amber',
        'low' => 'amber',
        'partial' => 'amber',
        'awaiting' => 'amber',
        'confirmed' => 'blue',
        'scheduled' => 'blue',
        'requested' => 'blue',
        'processing' => 'violet',
        'in-review' => 'violet',
        'shipped' => 'cyan',
        'in-transit' => 'cyan',
        'delivered' => 'green',
        'completed' => 'green',
        'active' => 'green',
        'available' => 'green',
        'paid' => 'green',
        'cod-collected' => 'green',
        'approved' => 'green',
        'published' => 'green',
        'running' => 'green',
        'sent' => 'green',
        'received' => 'green',
        'in-stock' => 'green',
        'cancelled' => 'red',
        'canceled' => 'red',
        'out-of-stock' => 'red',
        'failed' => 'red',
        'blocked' => 'red',
        'rejected' => 'red',
        'overdue' => 'red',
        'returned' => 'gray',
        'draft' => 'gray',
        'inactive' => 'gray',
        'hidden' => 'gray',
        'refunded' => 'gray',
        'expired' => 'gray',
        'ended' => 'gray',
        'archived' => 'gray',
    ];

    /** Normalise "Out of stock", "out_of_stock", "OUT-OF-STOCK" => "out-of-stock". */
    public static function key(string $status): string
    {
        return Str::slug(str_replace('_', '-', $status));
    }

    /** One of: amber, blue, violet, cyan, green, red, gray. */
    public static function tone(string $status): string
    {
        return self::TONES[self::key($status)] ?? 'gray';
    }

    /** Human label: "low-stock" => "Low stock", "Processing" stays "Processing". */
    public static function label(string $status): string
    {
        return Str::ucfirst(str_replace(['-', '_'], ' ', Str::lower($status)));
    }

    /** Full map for client-side (Alpine) rendering. */
    public static function toneMap(): array
    {
        return self::TONES;
    }
}
