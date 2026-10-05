<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Demo\ActivityLogData;
use Illuminate\View\View;

/**
 * Activity log (design: ActivityLog.dc.html).
 * Passes sample data to the view; replace with real queries later.
 */
class ActivityLogController extends Controller
{
    public function index(): View
    {
        $entries = array_map(function (array $e) {
            [$route, $params] = $e['link'];
            $e['href'] = route($route, $params);

            return $e;
        }, ActivityLogData::entries());

        return view('admin.activity-log.index', [
            'kpis' => ActivityLogData::kpis(),
            'users' => ActivityLogData::users(),
            'types' => ActivityLogData::actionTypes(),
            'entries' => $entries,
            'severityTones' => ActivityLogData::severityTones(),
            'dateLabel' => '4 Oct 2026',
            'dateValue' => '2026-10-04',
            'pagination' => ['page' => 1, 'pages' => 26, 'total' => 284],
        ]);
    }
}
