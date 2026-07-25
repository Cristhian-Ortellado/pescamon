<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class ShopController extends Controller
{
    public function index(): Response
    {
        $products = Product::query()
            ->where('is_active', true)
            ->latest()
            ->get(['id', 'name', 'slug', 'description', 'price', 'image_path']);

        return Inertia::render('shop/Index', [
            'products' => $products,
        ]);
    }

    public function show(Product $product): Response
    {
        abort_unless($product->is_active, 404);

        return Inertia::render('shop/Show', [
            'product' => $product->only(['id', 'name', 'slug', 'description', 'price', 'image_url']),
            'whatsapp' => config('shop.whatsapp'),
        ]);
    }
}
