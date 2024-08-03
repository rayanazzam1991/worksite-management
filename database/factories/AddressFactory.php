<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->address(),
            'zipcode' => fake()->postcode(),
            'street' => fake()->streetName(),
            'state' => fake()->city,
            'city_id' => fn () => City::query()->first() != null ?
                City::query()->first()->id : City::factory()->create()->id,
        ];
    }
}
