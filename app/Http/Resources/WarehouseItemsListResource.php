<?php

namespace App\Http\Resources;

use App\Models\Item;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Item $item
 * @property Warehouse $warehouse
 * @property float $quantity
 * @property float $price
 */
class WarehouseItemsListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'item' => ItemDetailsResource::make($this->item),
            'warehouse' => WarehouseDetailsResource::make($this->warehouse),
            'quantity' => $this->quantity,
            'price' => $this->price,
        ];
    }
}
