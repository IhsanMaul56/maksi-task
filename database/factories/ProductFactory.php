<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
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

    protected $model = Product::class;
    
    public function definition(): array
    {
        return [
            'code' => 'P' . fake()->numberBetween(1, 999),
            'name' => fake()->word,
            'description' => fake()->sentence,
            'stock' => fake()->numberBetween(1, 20),
            'price' => fake()->numberBetween(1000, 10000),
            'category' => fake()->randomElement(['leptop', 'hp']),
            'is_delete' => 0,
            'img' => UploadedFile::fake()->image('product.jpg', 500, 500),
        ];
    }
}
