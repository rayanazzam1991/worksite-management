<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\ItemCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name,
            'description' => fake()->name,
            'item_category_id' => ItemCategory::query()->first() ?
                ItemCategory::query()->first()->id :
                ItemCategory::factory()->create()->id,

        ];
    }
}
