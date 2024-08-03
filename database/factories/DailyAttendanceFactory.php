<?php

namespace Database\Factories;

use App\Models\DailyAttendance;
use App\Models\User;
use App\Models\WorkSite;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyAttendance>
 */
class DailyAttendanceFactory extends Factory
{
    protected $model = DailyAttendance::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => User::factory()->create()->id,
            'work_site_id' => Worksite::factory()->create()->id,
            'date' => $this->faker->date(),
        ];
    }
}
