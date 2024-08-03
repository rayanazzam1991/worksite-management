<?php

namespace App\Http\Resources;

use App\Enums\StatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $first_name
 * @property string|null $last_name
 * @property string $phone
 * @property string|null $email
 * @property int $status
 */
class UserDetailsResource extends JsonResource
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
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => StatusEnum::from($this->status)->name,
        ];
    }
}
