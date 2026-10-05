<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Demo\ReviewData;
use Illuminate\View\View;

/**
 * Reviews (design: Reviews.dc.html).
 * Passes sample data to the view; replace with real queries later.
 * Publish / hide / reply / delete are client-side (Alpine) in this export.
 */
class ReviewController extends Controller
{
    public function index(): View
    {
        return view('admin.reviews.index', [
            'reviews' => ReviewData::reviews(),
            'archived' => ReviewData::archivedCounts(),
            'summary' => ReviewData::summary(),
            'productOptions' => ReviewData::productOptions(),
        ]);
    }
}
