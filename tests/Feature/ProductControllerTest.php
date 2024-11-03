<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function createAdminUser()
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function createRegularUser()
    {
        return User::factory()->create(['role' => 'user']);
    }

    /** @test */
    public function admin_and_user_can_access_index()
    {
        /** @var \App\Models\User $admin */
        $admin = $this->createAdminUser();

        /** @var \App\Models\User $user */
        $user = $this->createRegularUser();

        // Admin can access index
        $this->actingAs($admin, 'api')
            ->get('/api/admin/product')
            ->assertStatus(200);

        // User can access index
        $this->actingAs($user, 'api')
            ->get('/api/user/product')
            ->assertStatus(200);
    }

    /** @test */
    public function admin_can_access_store()
    {
        /** @var \App\Models\User $admin */
        $admin = $this->createAdminUser();

        /** @var \App\Models\User $user */
        $user = $this->createRegularUser();

        $data = [
            'code' => 'P' . fake()->numberBetween(1, 999),
            'name' => fake()->word,
            'description' => fake()->sentence,
            'stock' => fake()->numberBetween(1, 20),
            'price' => fake()->numberBetween(1000, 10000),
            'category' => fake()->randomElement(['leptop', 'hp']),
            'is_delete' => 0,
            'img' => UploadedFile::fake()->image('product.jpg', 500, 500),
        ];

        // Admin can access store
        $this->actingAs($admin, 'api')
            ->post('/api/admin/product', $data)
            ->assertStatus(201);

        // User cannot access store
        $this->actingAs($user, 'api')
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/admin/product', $data)
            ->assertStatus(403);
    }

    /** @test */
    public function admin_cannot_access_store_with_invalid_data()
    {
        /** @var \App\Models\User $admin */
        $admin = $this->createAdminUser();

        $invalidData = [
            'code' => null,
            'name' => '',
            'description' => '',
            'stock' => '',
            'price' => '',
            'category' => '',
            'is_delete' => '',
            'img' => '',

        ];

        $this->actingAs($admin, 'api')
            ->post('/api/admin/product', $invalidData)
            ->assertStatus(422);
    }

    /** @test */
    public function admin_and_user_can_access_show()
    {
        /** @var \App\Models\User $admin */
        $admin = $this->createAdminUser();

        /** @var \App\Models\User $user */
        $user = $this->createRegularUser();

        $product = Product::factory()->create();

        // Admin can access show
        $this->actingAs($admin, 'api')
            ->get("/api/admin/product/{$product->id}")
            ->assertStatus(200);

        // user can access show
        $this->actingAs($user, 'api')
            ->get("/api/user/product/{$product->id}")
            ->assertStatus(200);
    }

    /** @test */
    public function admin_can_access_update()
    {
        /** @var \App\Models\User $admin */
        $admin = $this->createAdminUser();

        $product = Product::factory()->create();

        $updatedData = [
            'code' => $product->code,
            'name' => 'Product Aja',
            'description' => $product->description,
            'stock' => $product->stock,
            'price' => $product->price,
            'category' => $product->category,
            'is_delete' => $product->is_delete,
            'img' => $product->img
        ];

        $this->actingAs($admin, 'api')
            ->put("/api/admin/product/{$product->id}", $updatedData)
            ->assertStatus(200);
    }

    /** @test */
    public function user_cannot_access_update()
    {
        /** @var \App\Models\User $user */
        $user = $this->createRegularUser();

        $product = Product::factory()->create();

        $updatedData = [
            'code' => $product->code,
            'name' => 'Product Aja',
            'description' => $product->description,
            'stock' => $product->stock,
            'price' => $product->price,
            'category' => $product->category,
            'is_delete' => $product->is_delete,
            'img' => $product->img
        ];

        $this->actingAs($user, 'api')
            ->put("/api/admin/product/{$product->id}", $updatedData)
            ->assertStatus(403);
    }

    /** @test */
    public function admin_cannot_access_update_with_invalid_data()
    {
        /** @var \App\Models\User $admin */
        $admin = $this->createAdminUser();

        $product = Product::factory()->create();

        $updatedData = [
            'code' => $product->code,
            'name' => '',
            'description' => $product->description,
            'stock' => $product->stock,
            'price' => $product->price,
            'category' => $product->category,
            'is_delete' => $product->is_delete,
            'img' => $product->img
        ];

        // dd($updatedData);
        $this->actingAs($admin, 'api')
            ->put("/api/admin/product/{$product->id}", $updatedData)
            ->assertStatus(422);
    }

    /** @test */
    public function admin_can_access_destroy()
    {
        /** @var \App\Models\User $admin */
        $admin = $this->createAdminUser();

        $product = Product::factory()->create();

        $softDeletedData = [
            'code' => $product->code,
            'name' => $product->name,
            'description' => $product->description,
            'stock' => $product->stock,
            'price' => $product->price,
            'category' => $product->category,
            'is_delete' => 1,
            'img' => $product->img
        ];

        $this->actingAs($admin, 'api')
            ->put("/api/admin/product/{$product->id}", $softDeletedData)
            ->assertStatus(200);
    }

    /** @test */
    public function user_cannot_access_destroy()
    {
        /** @var \App\Models\User $user */
        $user = $this->createRegularUser();

        $product = Product::factory()->create();

        $softDeletedData = [
            'code' => $product->code,
            'name' => $product->name,
            'description' => $product->description,
            'stock' => $product->stock,
            'price' => $product->price,
            'category' => $product->category,
            'is_delete' => 1,
            'img' => $product->img
        ];

        $this->actingAs($user, 'api')
            ->put("/api/admin/product/{$product->id}", $softDeletedData)
            ->assertStatus(403);
    }

    /** @test */
    public function admin_can_access_restore()
    {
        /** @var \App\Models\User $admin */
        $admin = $this->createAdminUser();

        $product = Product::factory()->create();

        $softDeletedData = [
            'code' => $product->code,
            'name' => $product->name,
            'description' => $product->description,
            'stock' => $product->stock,
            'price' => $product->price,
            'category' => $product->category,
            'is_delete' => 0,
            'img' => $product->img
        ];

        $this->actingAs($admin, 'api')
            ->put("/api/admin/product/{$product->id}", $softDeletedData)
            ->assertStatus(200);
    }

    /** @test */
    public function user_cannot_access_restore()
    {
        /** @var \App\Models\User $user */
        $user = $this->createRegularUser();

        $product = Product::factory()->create();

        $softDeletedData = [
            'code' => $product->code,
            'name' => $product->name,
            'description' => $product->description,
            'stock' => $product->stock,
            'price' => $product->price,
            'category' => $product->category,
            'is_delete' => 1,
            'img' => $product->img
        ];

        $this->actingAs($user, 'api')
            ->put("/api/admin/product/{$product->id}", $softDeletedData)
            ->assertStatus(403);
    }
}
