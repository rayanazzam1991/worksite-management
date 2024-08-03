<?php

namespace Database\Factories;

use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    /**
     * @var string
     */
    protected $model = Payment::class;

    /**
     * @return array|mixed[]
     */
    public function definition(): array
    {
        return [
            'amount' => 20,
            'payment_date' => Carbon::now(),
            'payment_type' => 1,
        ];
    }
}
