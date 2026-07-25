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
            ->with('images:id,product_id,path,position')
            ->latest()
            ->get(['id', 'name', 'slug', 'description', 'price'])
            ->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'description' => $p->description,
                'price' => $p->price,
                'images' => $p->images->map(fn ($img) => ['id' => $img->id, 'url' => $img->url])->all(),
            ]);

        return Inertia::render('shop/Index', [
            'products' => $products,
        ]);
    }

    public function show(Product $product): Response
    {
        abort_unless($product->is_active, 404);

        $product->load('images:id,product_id,path,position');

        return Inertia::render('shop/Show', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'price' => $product->price,
                'images' => $product->images->map(fn ($img) => ['id' => $img->id, 'url' => $img->url])->all(),
            ],
            'whatsapp' => config('shop.whatsapp'),
        ]);
    }
}
