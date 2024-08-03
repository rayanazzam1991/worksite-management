<?php

namespace App\Http\Resources;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id,
 * @property string $first_name,
 * @property string|null $last_name,
 * @property string|null $phone,
 * @property Address $address,
 * @property mixed $payments,
 **/
class CustomerDetailsResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'address' => AddressDetailsResource::make($this->address),
            'payments' => PaymentListResource::collection($this->payments),
        ];
    }
}
