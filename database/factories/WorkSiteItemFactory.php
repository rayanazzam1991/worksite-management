<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\WorkSite;
use App\Models\WorkSiteItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkSiteItem>
 */
class WorkSiteItemFactory extends Factory
{
    protected $model = WorkSiteItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'work_site_id' => fn () => WorkSite::query()->first() ?
                WorkSite::query()->first()->id : WorkSite::factory()->create()->id,
            'item_id' => fn () => Item::query()->first() ?
                Item::query()->first()->id : Item::factory()->create()->id,
            'quantity' => fake()->numberBetween(1, 20),
            'price' => fake()->randomFloat(10, 100),
        ];
    }
}
