<?php

namespace App\Http\Resources;

use App\Enums\PaymentTypesEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property float $amount
 * @property string $payment_date
 * @property string $payment_type
 */
class WorkSitePaymentListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'amount' => $this->amount,
            'payment_date' => $this->payment_date,
            'payment_type' => PaymentTypesEnum::from($this->payment_type)->name,
        ];
    }
}
