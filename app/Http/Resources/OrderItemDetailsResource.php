<?php

namespace App\Http\Resources;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property Item $itemDetails
 * @property int $quantity
 * @property float $price
 */
class OrderItemDetailsResource extends JsonResource
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
            'item' => ItemDetailsResource::make($this->itemDetails),
            'quantity' => $this->quantity,
            'price' => $this->price,
        ];
    }
}
