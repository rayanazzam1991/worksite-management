<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $payable_id
 * @property mixed $payable_type
 * @property mixed $amount
 * @property mixed $payment_date
 * @property mixed $payment_type
 */
class PaymentListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payable_id' => $this->payable_id,
            'payable_type' => $this->payable_type,
            'amount' => $this->amount,
            'date' => $this->payment_date,
            'payment_type' => $this->payment_type,
        ];
    }
}
