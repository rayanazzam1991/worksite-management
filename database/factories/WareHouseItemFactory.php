<?php

namespace Database\Factories;

use App\Enums\WareHouseItemStatusEnum;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\WarehouseItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WarehouseItem>
 */
class WareHouseItemFactory extends Factory
{
    protected $model = WareHouseItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_id' => fn () => Item::query()->first()
                ? Item::query()->first()->id
                : Item::factory()->create()->id,
            'warehouse_id' => fn () => Warehouse::query()->first()
                ? Warehouse::query()->first()->id
                : Warehouse::factory()->create()->id,
            'supplier_id' => fn () => Supplier::query()->first()
                ? Supplier::query()->first()->id
                : Supplier::factory()->create()->id,
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'quantity' => $this->faker->randomFloat(2, 10, 100),
            'status' => $this->faker->randomElement(WareHouseItemStatusEnum::cases()),
            'date' => $this->faker->date(),
        ];
    }
}
