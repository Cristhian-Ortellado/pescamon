<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(): Response
    {
        $products = Product::query()
            ->with('images:id,product_id,path,position')
            ->latest()
            ->get(['id', 'name', 'slug', 'description', 'price', 'is_active'])
            ->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'price' => $p->price,
                'is_active' => $p->is_active,
                'cover' => optional($p->images->first())->url,
                'image_count' => $p->images->count(),
            ]);

        return Inertia::render('admin/products/Index', [
            'products' => $products,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/products/Create');
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $product = Product::create([
            'name' => $request->validated('name'),
            'slug' => $this->uniqueSlug($request->validated('name')),
            'description' => $request->validated('description'),
            'price' => $request->validated('price'),
            'is_active' => $request->validated('is_active'),
        ]);

        $this->addImages($product, $request);

        return redirect()->route('admin.products.index')
            ->with('success', 'Producto creado.');
    }

    public function edit(Product $product): Response
    {
        $product->load('images:id,product_id,path,position');

        return Inertia::render('admin/products/Edit', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'price' => $product->price,
                'is_active' => $product->is_active,
                'images' => $product->images->map(fn ($img) => ['id' => $img->id, 'url' => $img->url])->all(),
            ],
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $product->update([
            'name' => $request->validated('name'),
            'slug' => $request->validated('name') !== $product->name
                ? $this->uniqueSlug($request->validated('name'), $product->id)
                : $product->slug,
            'description' => $request->validated('description'),
            'price' => $request->validated('price'),
            'is_active' => $request->validated('is_active'),
        ]);

        // Delete images the user removed in the editor.
        $removedIds = collect($request->input('removed_image_ids', []))->map('intval');

        if ($removedIds->isNotEmpty()) {
            $product->images()->whereIn('id', $removedIds)->get()
                ->each(function ($image) {
                    Storage::disk('public')->delete($image->path);
                    $image->delete();
                });
        }

        $this->addImages($product, $request);
        $this->resequence($product);

        return redirect()->route('admin.products.index')
            ->with('success', 'Producto actualizado.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->load('images');
        $product->images->each(fn ($img) => Storage::disk('public')->delete($img->path));
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Producto eliminado.');
    }

    /**
     * Store any uploaded images, appending them after existing ones.
     */
    private function addImages(Product $product, ProductRequest $request): void
    {
        $files = $request->file('images', []);

        if (empty($files)) {
            return;
        }

        // Continue numbering after the last existing image (or from 0 when none).
        $max = $product->images()->max('position');
        $position = is_null($max) ? -1 : (int) $max;

        foreach ($files as $file) {
            $product->images()->create([
                'path' => $file->store('products', 'public'),
                'position' => ++$position,
            ]);
        }
    }

    /**
     * Compact positions to 0..n after removals so ordering stays contiguous.
     */
    private function resequence(Product $product): void
    {
        $product->images()->get()->values()->each(
            fn ($image, $i) => $image->update(['position' => $i]),
        );
    }

    /**
     * Slug derived from the name, guaranteed unique across products.
     */
    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;

        while (Product::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
