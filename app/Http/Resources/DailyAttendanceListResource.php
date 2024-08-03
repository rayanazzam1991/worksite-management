<?php

namespace App\Http\Resources;

use App\Models\WorkSite;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property WorkSite $workSite
 * @property mixed $date
 */
class DailyAttendanceListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'workSite' => $this->workSite->title,
            'date' => $this->date,
        ];
    }
}
