<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Demo\NotificationsData;
use Illuminate\View\View;

/**
 * SMS & email (design: Notifications.dc.html).
 * Passes sample data to the view; replace with real queries later.
 */
class NotificationController extends Controller
{
    public function index(): View
    {
        $templates = NotificationsData::templates();

        return view('admin.notifications.index', [
            'stats' => NotificationsData::stats(),
            'channels' => NotificationsData::channels(),
            'events' => NotificationsData::events(),
            'templates' => $templates,
            'activeTemplate' => array_key_first($templates),
            'placeholders' => NotificationsData::placeholders(),
            'sample' => NotificationsData::sampleValues(),
            'sms' => NotificationsData::smsInfo($templates[array_key_first($templates)]['en']),
            'gateway' => NotificationsData::gateway(),
            'smtp' => NotificationsData::smtp(),
            'logs' => NotificationsData::logs(),
        ]);
    }
}
