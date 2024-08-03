<?php

namespace App\Http\Resources;

use App\Models\WorkSiteItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $name
 * @property mixed $description
 * @property mixed $category
 * @property WorkSiteItem|null $pivot
 */
class ItemListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'item_category' => ItemCategoryDetailsResource::make($this->category),
            'work_site_id' => $this->pivot?->work_site_id,
            'item_id' => $this->pivot?->item_id,
            'price' => $this->pivot?->price,
            'quantity' => $this->pivot?->quantity,
        ];
    }
}
