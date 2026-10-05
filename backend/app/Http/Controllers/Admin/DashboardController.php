<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\DemoData;
use Illuminate\View\View;

/**
 * Dashboard (designs: Dashboard on desktop, M-AdminDashboard on phones).
 */
class DashboardController extends Controller
{
    public function index(): View
    {
        $sales = DemoData::salesLast14Days();
        $last7 = array_slice($sales, -7);

        return view('admin.dashboard.index', [
            'ranges' => DemoData::dashboardRanges(),
            'kpis' => DemoData::kpis(),
            'sales14' => $sales,
            'sales7' => $last7,
            'sales7Totals' => [
                // Phone legend shows the 7-day channel totals.
                'online' => 232400,
                'pos' => 127850,
            ],
            'statusCounts' => DemoData::dashboardStatusCounts(),
            'recentOrders' => DemoData::recentOrders(6),
            'alerts' => DemoData::lowStockAlerts(),
            'alertTotal' => DemoData::stockAlertTotal(),
            'topProducts' => DemoData::topProducts(),
            'today' => 'Saturday, 4 October 2026',
            'todayShort' => '4 October 2026',
        ]);
    }
}
