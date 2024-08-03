<?php

namespace App\Http\Resources;

use App\Models\Customer;
use App\Models\WorkSiteCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $title
 * @property mixed $description
 * @property Customer|null $customer
 * @property WorkSiteCategory|null $category
 * @property mixed $subWorksites
 * @property mixed $starting_budget
 * @property mixed $cost
 * @property mixed $address
 * @property mixed $workers_count
 * @property mixed $receipt_date
 * @property mixed $starting_date
 * @property mixed $deliver_date
 * @property mixed $reception_status
 * @property string $created_at
 * @property string $updated_at
 * @property mixed $resources
 * @property mixed $payments
 */
class WorkSiteListResource extends JsonResource
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
            'description' => $this->description,
            'customer' => $this->customer?->fullName,
            'category' => $this->category?->name,
            'sub_worksites' => $this->subWorksites,
            'starting_budget' => $this->starting_budget,
            'cost' => $this->cost,
            'address' => AddressDetailsResource::make($this->address),
            'workers_count' => $this->workers_count,
            'receipt_date' => $this->receipt_date,
            'starting_date' => $this->starting_date,
            'deliver_date' => $this->deliver_date,
            'reception_status' => $this->reception_status,
            'created_at' => Carbon::parse($this->created_at)->toDateTimeString(),
            'updated_at' => Carbon::parse($this->updated_at)->toDateTimeString(),
            'payments' => $this->payments,
        ];
    }
}
