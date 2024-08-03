<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName,
            'last_name' => fake()->lastName(),
            'email' => fake()->email(),
            'phone' => fake()->phoneNumber(),
            'password' => 'Rayan123@@',
        ];
    }

    public function mainAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'first_name' => fake()->firstName,
            'last_name' => fake()->lastName(),
            'email' => 'admin@admin.com',
            'phone' => fake()->phoneNumber(),
            'password' => 'admin123',
        ])->afterCreating(function (User $user) {
            return $user->assignRole('admin');
        });
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'first_name' => fake()->firstName,
            'last_name' => fake()->lastName(),
            'email' => fake()->email(),
            'phone' => fake()->phoneNumber(),
            'password' => 'admin123',
        ])->afterCreating(function (User $user) {
            return $user->assignRole('admin');
        });
    }

    public function siteManager(): static
    {
        return $this->afterCreating(function (User $user) {
            return $user->assignRole('site_manager');
        });
    }

    public function storeKeeper(): static
    {
        return $this->afterCreating(function (User $user) {
            return $user->assignRole('store_keeper');
        });
    }

    public function worker(): static
    {
        return $this->afterCreating(function (User $user) {
            return $user->assignRole('worker');
        });
    }
}
