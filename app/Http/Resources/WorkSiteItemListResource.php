<?php

namespace App\Http\Resources;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property Item $item
 * @property string $name
 * @property object{
 *     quantity:int,
 *     price:float
 * } $pivot
 */
class WorkSiteItemListResource extends JsonResource
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
            'quantity' => $this->pivot->quantity,
            'price' => $this->pivot->price,
            'item' => $this->name,
        ];
    }
}
