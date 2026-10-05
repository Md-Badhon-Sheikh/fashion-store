<?php

use App\Http\Controllers\Api\CatalogController;
use Illuminate\Support\Facades\Route;

/*
| Placeholder JSON API for the React store (prefix /api is added by bootstrap/app.php).
| Data comes from App\Support\DemoData until real models exist.
*/
Route::get('/products', [CatalogController::class, 'products'])->name('api.products.index');
Route::get('/products/{slug}', [CatalogController::class, 'product'])->name('api.products.show');
Route::get('/categories', [CatalogController::class, 'categories'])->name('api.categories.index');
