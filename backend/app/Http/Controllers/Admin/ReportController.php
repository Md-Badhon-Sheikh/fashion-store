<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Demo\ReportsData;
use Illuminate\View\View;

/**
 * Reports (design: Reports.dc.html).
 * Passes sample data to the view; replace with real queries later.
 */
class ReportController extends Controller
{
    public function index(): View
    {
        return view('admin.reports.index', [
            'reportGroups' => ReportsData::reportGroups(),
            'currentReport' => ReportsData::currentReport(),
            'periodOptions' => ReportsData::periodOptions(),
            'defaultPeriod' => ReportsData::DEFAULT_PERIOD,
            'periods' => ReportsData::periods(),
            'generatedAt' => '4 Oct 2026, 1:05 PM',
        ]);
    }
}
