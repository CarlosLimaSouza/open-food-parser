<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected $apiKey = 'fitness_food_secret_key';

    public function test_can_list_products()
    {
        Product::factory()->count(5)->create();

        $response = $this->withHeaders(['x-api-key' => $this->apiKey])
                         ->getJson('/api/products');

        $response->assertStatus(200)
                 ->assertJsonCount(5, 'data');
    }

    public function test_can_show_product()
    {
        $product = Product::factory()->create(['code' => '12345']);

        $response = $this->withHeaders(['x-api-key' => $this->apiKey])
                         ->getJson('/api/products/12345');

        $response->assertStatus(200)
                 ->assertJsonPath('code', '12345');
    }

    public function test_can_update_product()
    {
        $product = Product::factory()->create(['code' => '54321', 'product_name' => 'Old Name']);

        $response = $this->withHeaders(['x-api-key' => $this->apiKey])
                         ->putJson('/api/products/54321', [
                             'product_name' => 'New Name'
                         ]);

        $response->assertStatus(200)
                 ->assertJsonPath('product_name', 'New Name');

        $this->assertDatabaseHas('products', [
            'code' => '54321',
            'product_name' => 'New Name'
        ]);
    }

    public function test_cannot_access_without_api_key()
    {
        $response = $this->getJson('/api/products');
        $response->assertStatus(401);
    }
}
