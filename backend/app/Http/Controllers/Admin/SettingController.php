<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Demo\SettingsData;
use Illuminate\View\View;

/**
 * Settings (design: Settings.dc.html).
 * Passes sample data to the view; replace with real queries later.
 */
class SettingController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.index', [
            'sections' => SettingsData::sections(),
            'store' => SettingsData::store(),
            'zones' => SettingsData::zones(),
            'freeDelivery' => SettingsData::freeDelivery(),
            'payments' => SettingsData::payments(),
            'merchantNumber' => SettingsData::merchantNumber(),
            'couriers' => SettingsData::couriers(),
            'courierApis' => SettingsData::courierApis(),
            'invoice' => SettingsData::invoice(),
            'receiptOptions' => SettingsData::receiptOptions(),
            'receipt' => SettingsData::receiptSample(),
            'tax' => SettingsData::tax(),
            'inventory' => SettingsData::inventory(),
            'inventoryOptions' => SettingsData::inventoryOptions(),
            'seo' => SettingsData::seo(),
            'securityOptions' => SettingsData::securityOptions(),
            'security' => SettingsData::security(),
        ]);
    }
}
