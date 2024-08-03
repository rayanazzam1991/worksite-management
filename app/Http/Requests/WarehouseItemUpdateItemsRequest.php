<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class WarehouseItemUpdateItemsRequest extends FormRequest
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
            'items' => ['required', 'array'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.quantity' => ['sometimes', 'integer', 'min:1'],
            'items.*.price' => ['sometimes', 'numeric'],
        ];
    }
}
