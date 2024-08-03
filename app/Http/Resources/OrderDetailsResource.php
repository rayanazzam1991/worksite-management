<?php

namespace App\Http\Resources;

use App\Enums\OrderPriorityEnum;
use App\Enums\OrderStatusEnum;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\WorkSite;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property WorkSite $workSite
 * @property OrderItem[] $orderItems
 * @property float $total_amount
 * @property string $status
 * @property string $priority
 * @property User $orderCreatedBy
 */
class OrderDetailsResource extends JsonResource
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
            'workSite' => $this->workSite->title,
            'order_items' => OrderItemDetailsResource::collection($this->orderItems),
            'total_amount' => $this->total_amount,
            'status' => OrderStatusEnum::from($this->status)->name,
            'priority' => OrderPriorityEnum::from($this->priority)->name,
            'created_by' => $this->orderCreatedBy->fullName,
        ];
    }
}
