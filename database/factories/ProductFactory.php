<?php

namespace Database\Factories;

use App\Models\Currency;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id'=>$this->faker->unique()->randomNumber(),
            'name' => $this->faker->company,
            'price' => $this->faker->numberBetween(10,999),
            'currency_id'=>Currency::all()->random()->id,
            'seller_id'=>Seller::all()->random()->id,

        ];
    }
}
