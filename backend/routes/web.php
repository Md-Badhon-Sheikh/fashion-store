<?php

use App\Http\Controllers\Admin\AccountingController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BarcodeController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FlashSaleController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PosController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReturnRequestController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StockInController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('admin.dashboard'));

/*
| Admin panel (static design export — no auth middleware yet).
| Add ->middleware('auth') to the group when real authentication is wired.
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');

    // Overview
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/pos', [PosController::class, 'index'])->name('pos');

    // Sales
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
    Route::get('/orders/{order}/receipt', [OrderController::class, 'receipt'])->name('orders.receipt');
    Route::get('/orders/{order}/packing-slip', [OrderController::class, 'packingSlip'])->name('orders.packing-slip');
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers');
    Route::get('/returns', [ReturnRequestController::class, 'index'])->name('returns');

    // Catalog
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories');
    Route::get('/barcodes', [BarcodeController::class, 'index'])->name('barcodes');
    Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews');

    // Inventory
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory');
    Route::get('/stock-in', [StockInController::class, 'index'])->name('stock-in');
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers');

    // Marketing
    Route::get('/coupons', [CouponController::class, 'index'])->name('coupons');
    Route::get('/flash-sales', [FlashSaleController::class, 'index'])->name('flash-sales');
    Route::get('/banners', [BannerController::class, 'index'])->name('banners');

    // Finance
    Route::get('/accounting', [AccountingController::class, 'index'])->name('accounting');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports');

    // System
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::get('/users', [UserController::class, 'index'])->name('users');
    Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log');
    Route::get('/settings', [SettingController::class, 'index'])->name('settings');
});
