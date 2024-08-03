<?php

namespace App\Http\Resources;

use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property City $city
 * @property mixed $street
 * @property mixed $state
 * @property mixed $zipcode
 * @property mixed $title
 */
class AddressDetailsResource extends JsonResource
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
            'title' => $this->title,
            'city' => $this->city->name,
            'street' => $this->street,
            'state' => $this->state,
            'zipCode' => $this->zipcode,
        ];
    }
}
