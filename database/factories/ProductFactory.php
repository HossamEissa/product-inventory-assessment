<?php

namespace Database\Factories;

use App\Enum\ProductStatus;
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
            'sku'                 => strtoupper($this->faker->unique()->bothify('SKU-####')),
            'name'                => $this->faker->words(3, true),
            'description'         => $this->faker->sentence(),
            'price'               => $this->faker->randomFloat(2, 1, 999),
            'stock_quantity'      => $this->faker->numberBetween(0, 100),
            'low_stock_threshold' => 10,
            'status'              => $this->faker->randomElement(ProductStatus::cases()),
        ];
    }
}
