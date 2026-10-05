<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\DemoData;
use Illuminate\Http\JsonResponse;

/**
 * Placeholder read-only catalogue API for the React store (served from DemoData).
 * GET /api/products, /api/products/{slug}, /api/categories
 */
class CatalogController extends Controller
{
    public function products(): JsonResponse
    {
        $products = array_map(fn (array $p) => $this->summary($p), DemoData::products());

        return response()->json(['data' => $products]);
    }

    public function product(string $slug): JsonResponse
    {
        $product = DemoData::findProduct($slug);
        abort_if($product === null, 404, 'Product not found');

        $data = $this->summary($product) + [
            'description' => $product['description'],
            'fabric' => $product['fabric'],
            'variants' => array_map(fn (array $v) => [
                'size' => $v['size'],
                'colour' => $v['colour'],
                'sku' => $v['sku'],
                'stock' => $v['stock'],
                'in_stock' => $v['stock'] > 0,
            ], $product['variants']),
        ];

        return response()->json(['data' => $data]);
    }

    public function categories(): JsonResponse
    {
        return response()->json(['data' => DemoData::categories()]);
    }

    /** Public fields only (no purchase price / internal stock values). */
    private function summary(array $p): array
    {
        return [
            'id' => $p['id'],
            'slug' => $p['slug'],
            'name' => $p['name'],
            'sku' => $p['sku'],
            'category' => $p['category'],
            'category_slug' => $p['category_slug'],
            'brand' => $p['brand'],
            'price' => $p['price'],
            'old_price' => $p['compare_at_price'] && $p['compare_at_price'] > $p['price'] ? $p['compare_at_price'] : null,
            'price_label' => DemoData::money($p['price']),
            'image' => $p['image'],
            'image_url' => $p['image_url'],
            'tone' => $p['tone'],
            'sizes' => $p['sizes'],
            'colours' => $p['colours'],
            'in_stock' => $p['stock'] > 0,
            'online' => $p['visibility'] !== 'POS only',
        ];
    }
}
