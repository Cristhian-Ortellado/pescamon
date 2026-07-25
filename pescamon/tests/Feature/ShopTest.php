<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_the_shop(): void
    {
        $this->get(route('shop.index'))->assertRedirect(route('login'));
    }

    public function test_shop_lists_only_active_products(): void
    {
        $this->actingAs(User::factory()->create());

        $active = Product::factory()->create();
        Product::factory()->inactive()->create();

        $this->get(route('shop.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('shop/Index')
                ->has('products', 1)
                ->where('products.0.id', $active->id));
    }

    public function test_product_detail_is_visible_with_ordered_images_and_whatsapp(): void
    {
        $this->actingAs(User::factory()->create());
        $product = Product::factory()->withImages(3)->create();

        $this->get(route('shop.show', $product))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('shop/Show')
                ->where('product.id', $product->id)
                ->has('product.images', 3)
                ->has('product.images.0.url')
                ->where('whatsapp', config('shop.whatsapp')));
    }

    public function test_inactive_products_return_404(): void
    {
        $this->actingAs(User::factory()->create());
        $product = Product::factory()->inactive()->create();

        $this->get(route('shop.show', $product))->assertNotFound();
    }
}
