<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Warehouse>
 */
class WareHouseFactory extends Factory
{
    protected $model = Warehouse::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'address_id' => fn () => Address::query()->first() ? Address::query()->first()->id :
                Address::factory()->create()->id,
        ];
    }
}
