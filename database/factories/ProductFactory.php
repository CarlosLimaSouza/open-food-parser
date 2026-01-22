<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->ean13(),
            'status' => 'published',
            'imported_t' => now(),
            'url' => $this->faker->url(),
            'creator' => $this->faker->userName(),
            'product_name' => $this->faker->word(),
            'quantity' => $this->faker->word(),
            'brands' => $this->faker->company(),
        ];
    }
}
