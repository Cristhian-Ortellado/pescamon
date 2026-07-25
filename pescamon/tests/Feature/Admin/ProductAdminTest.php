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

    public function test_superadmin_can_create_a_product_with_multiple_images(): void
    {
        Storage::fake('public');
        $this->actingAs($this->superadmin());

        $response = $this->post(route('admin.products.store'), [
            'name' => 'Caña Test 2.40m',
            'description' => 'Una caña de prueba para el test.',
            'price' => 750000,
            'is_active' => true,
            'images' => [
                UploadedFile::fake()->image('a.jpg'),
                UploadedFile::fake()->image('b.jpg'),
                UploadedFile::fake()->image('c.jpg'),
            ],
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $product = Product::first();
        $this->assertNotNull($product);
        $this->assertSame('cana-test-240m', $product->slug);
        $this->assertCount(3, $product->images);
        // Positions preserve upload order.
        $this->assertSame([0, 1, 2], $product->images->pluck('position')->all());
        $product->images->each(fn ($img) => Storage::disk('public')->assertExists($img->path));
    }

    public function test_store_requires_at_least_one_image_and_a_name(): void
    {
        $this->actingAs($this->superadmin());

        $this->post(route('admin.products.store'), [
            'description' => 'Falta nombre e imagen.',
            'price' => 1000,
            'is_active' => true,
        ])->assertSessionHasErrors(['name', 'images']);
    }

    public function test_update_can_add_and_remove_images(): void
    {
        Storage::fake('public');
        $this->actingAs($this->superadmin());

        $product = Product::factory()->create();
        $keep = $product->images()->create(['path' => UploadedFile::fake()->image('keep.jpg')->store('products', 'public'), 'position' => 0]);
        $drop = $product->images()->create(['path' => UploadedFile::fake()->image('drop.jpg')->store('products', 'public'), 'position' => 1]);

        $this->put(route('admin.products.update', $product), [
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'is_active' => true,
            'images' => [UploadedFile::fake()->image('new.jpg')],
            'removed_image_ids' => [$drop->id],
        ])->assertRedirect(route('admin.products.index'));

        $product->refresh()->load('images');
        // keep + new = 2 images; dropped one is gone and its file deleted.
        $this->assertCount(2, $product->images);
        $this->assertTrue($product->images->contains('id', $keep->id));
        $this->assertFalse($product->images->contains('id', $drop->id));
        Storage::disk('public')->assertMissing($drop->path);
        // Positions were re-sequenced to stay contiguous.
        $this->assertSame([0, 1], $product->images->pluck('position')->all());
    }

    public function test_update_rejects_removing_all_images(): void
    {
        $this->actingAs($this->superadmin());
        $product = Product::factory()->create();
        $only = $product->images()->create(['path' => 'products/x.jpg', 'position' => 0]);

        $this->put(route('admin.products.update', $product), [
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'is_active' => true,
            'removed_image_ids' => [$only->id],
        ])->assertSessionHasErrors('images');

        $this->assertDatabaseHas('product_images', ['id' => $only->id]);
    }

    public function test_superadmin_can_update_text_fields_without_touching_images(): void
    {
        $this->actingAs($this->superadmin());
        $product = Product::factory()->withImages(1)->create(['name' => 'Viejo nombre']);

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
        $this->assertCount(1, $product->images);
    }

    public function test_deleting_a_product_removes_its_images_and_files(): void
    {
        Storage::fake('public');
        $this->actingAs($this->superadmin());

        $product = Product::factory()->create();
        $image = $product->images()->create([
            'path' => UploadedFile::fake()->image('x.jpg')->store('products', 'public'),
            'position' => 0,
        ]);

        $this->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'));

        $this->assertModelMissing($product);
        $this->assertDatabaseMissing('product_images', ['id' => $image->id]);
        Storage::disk('public')->assertMissing($image->path);
    }
}
