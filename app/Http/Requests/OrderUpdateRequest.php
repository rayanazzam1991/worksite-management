<?php

namespace App\Http\Requests;

use App\Enums\OrderStatusEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int,ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'work_site_id' => ['sometimes', 'exists:work_sites,id'],
            'total_amount' => ['sometimes', 'numeric'],
            'status' => ['sometimes', Rule::in(OrderStatusEnum::cases())],
            'items' => ['sometimes', 'array'],
            'items.*.item_id' => ['sometimes', 'exists:items,id'],
            'items.*.quantity' => ['sometimes', 'integer', 'min:1'],
            'priority' => ['sometimes', 'integer'],
        ];
    }
}
