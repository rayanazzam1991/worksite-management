<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\Contractor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contractor>
 */
class ContractorFactory extends Factory
{
    protected $model = Contractor::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'address_id' => fn () => Address::query()->first() ?
                Address::query()->first()->id :
                Address::factory()->create()->id,
        ];
    }
}
