<?php

namespace Database\Factories;

use App\Enums\OrderPriorityEnum;
use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\User;
use App\Models\WorkSite;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'work_site_id' => WorkSite::factory()->create()->id,
            'total_amount' => fake()->numberBetween(1000, 99999),
            'status' => fake()->randomElement(OrderStatusEnum::cases()),
            'priority' => fake()->randomElement(OrderPriorityEnum::cases()),
            'created_by' => User::factory()->siteManager()->create()->id,
        ];
    }
}
