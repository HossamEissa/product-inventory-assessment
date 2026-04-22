<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    private function productData(array $overrides = []): array
    {
        return array_merge([
            'sku'                 => 'SKU-001',
            'name'                => 'Test Product',
            'description'         => 'A test product',
            'price'               => 29.99,
            'stock_quantity'      => 50,
            'low_stock_threshold' => 10,
            'status'              => 'active',
        ], $overrides);
    }

    public function test_can_list_products_with_pagination(): void
    {
        Product::factory()->count(20)->create();

        $response = $this->getJson('/api/products?per_page=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'current_page',
                    'data',
                    'total',
                    'per_page',
                    'last_page',
                    'first_page_url',
                    'last_page_url',
                    'next_page_url',
                    'prev_page_url',
                    'links',
                    'from',
                    'to',
                    'path',
                ]
            ])
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Success request!')
            ->assertJsonPath('data.per_page', 10)
            ->assertJsonPath('data.total', 20)
            ->assertJsonPath('data.current_page', 1)
            ->assertJsonCount(10, 'data.data');
    }


    public function test_can_create_product(): void
    {
        $response = $this->postJson('/api/products', $this->productData());

        $response->assertStatus(201)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.sku', 'SKU-001')
            ->assertJsonPath('data.name', 'Test Product');

        $this->assertDatabaseHas('products', ['sku' => 'SKU-001']);
    }


    public function test_create_product_fails_validation(): void
    {
        $response = $this->postJson('/api/products', []);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors']);
    }

    public function test_can_update_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->putJson("/api/products/{$product->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name');
    }


    public function test_can_soft_delete_product(): void
    {
        $product = Product::factory()->create();

        $this->deleteJson("/api/products/{$product->id}")
            ->assertStatus(200)
            ->assertJsonPath('status', true);

        $this->assertSoftDeleted('products', ['id' => $product->id]);


        $this->getJson('/api/products')
            ->assertJsonMissing(['id' => $product->id]);
    }


    public function test_can_adjust_stock(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 20]);

        $this->postJson("/api/products/{$product->id}/stock", [
            'quantity' => -5,
        ])->assertStatus(200)
            ->assertJsonPath('data.stock_quantity', 15);
    }


    public function test_cannot_reduce_stock_below_zero(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 5]);

        $this->postJson("/api/products/{$product->id}/stock", [
            'quantity' => -10,
        ])->assertStatus(422)
            ->assertJsonPath('status', false)
            ->assertJsonPath('message', 'Insufficient stock');
    }


    public function test_low_stock_endpoint_returns_correct_products(): void
    {
        Product::factory()->create([
            'stock_quantity'      => 3,
            'low_stock_threshold' => 10,
        ]);
        Product::factory()->create([
            'stock_quantity'      => 50,
            'low_stock_threshold' => 10,
        ]);

        $response = $this->getJson('/api/products/low-stock');

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.total', 1)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.is_low_stock', true);
    }
}
