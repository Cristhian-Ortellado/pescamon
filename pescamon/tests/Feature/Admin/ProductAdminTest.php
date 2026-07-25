<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductAdminTest extends TestCase
{
    use RefreshDatabase;

    private function superadmin(): User
    {
        return User::factory()->create(['is_superadmin' => true]);
    }

    public function test_non_superadmin_cannot_access_product_admin(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('admin.products.index'))->assertForbidden();
    }

    public function test_guests_cannot_access_product_admin(): void
    {
        $this->get(route('admin.products.index'))->assertRedirect(route('login'));
    }

    public function test_superadmin_can_view_the_product_list(): void
    {
        $this->actingAs($this->superadmin());
        Product::factory()->count(2)->create();

        $this->get(route('admin.products.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('admin/products/Index')->has('products', 2));
    }

    public function test_superadmin_can_create_a_product(): void
    {
        Storage::fake('public');
        $this->actingAs($this->superadmin());

        $response = $this->post(route('admin.products.store'), [
            'name' => 'Caña Test 2.40m',
            'description' => 'Una caña de prueba para el test.',
            'price' => 750000,
            'is_active' => true,
            'image' => UploadedFile::fake()->image('cana.jpg'),
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $product = Product::first();
        $this->assertNotNull($product);
        $this->assertSame('Caña Test 2.40m', $product->name);
        $this->assertSame('cana-test-240m', $product->slug);
        Storage::disk('public')->assertExists($product->image_path);
    }

    public function test_store_requires_an_image_and_name(): void
    {
        $this->actingAs($this->superadmin());

        $this->post(route('admin.products.store'), [
            'description' => 'Falta nombre e imagen.',
            'price' => 1000,
            'is_active' => true,
        ])->assertSessionHasErrors(['name', 'image']);
    }

    public function test_superadmin_can_update_a_product_without_changing_the_image(): void
    {
        $this->actingAs($this->superadmin());
        $product = Product::factory()->create(['name' => 'Viejo nombre']);

        $this->put(route('admin.products.update', $product), [
            'name' => 'Nuevo nombre',
            'description' => $product->description,
            'price' => 999000,
            'is_active' => false,
        ])->assertRedirect(route('admin.products.index'));

        $product->refresh();
        $this->assertSame('Nuevo nombre', $product->name);
        $this->assertSame('999000.00', $product->price);
        $this->assertFalse($product->is_active);
    }

    public function test_superadmin_can_delete_a_product(): void
    {
        Storage::fake('public');
        $this->actingAs($this->superadmin());
        $product = Product::factory()->create([
            'image_path' => UploadedFile::fake()->image('x.jpg')->store('products', 'public'),
        ]);

        $this->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'));

        $this->assertModelMissing($product);
        Storage::disk('public')->assertMissing($product->image_path);
    }
}
