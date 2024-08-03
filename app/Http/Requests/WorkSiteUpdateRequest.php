<?php

namespace App\Http\Requests;

use App\Enums\WorkSiteCompletionStatusEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WorkSiteUpdateRequest extends FormRequest
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
            'title' => ['sometimes', 'string'],
            'description' => ['sometimes', 'string'],
            'customer_id' => ['sometimes', 'integer', 'exists:customers,id'],
            'category_id' => ['sometimes', 'integer', 'exists:work_site_categories,id'],
            'parent_work_site_id' => ['nullable', 'integer', 'exists:work_sites,id'],
            'starting_budget' => ['sometimes', 'numeric', 'min:0'],
            'cost' => ['sometimes', 'numeric', 'min:0'],
            'address_id' => ['sometimes', 'integer', 'exists:addresses,id'],
            'workers_count' => ['sometimes', 'integer', 'min:0'],
            'receipt_date' => ['sometimes', 'date'],
            'starting_date' => ['sometimes', 'date'],
            'deliver_date' => ['sometimes', 'date'],
            'completion_status' => ['sometimes', 'integer', Rule::in(WorkSiteCompletionStatusEnum::cases())],
            'reception_status' => ['sometimes', 'integer'],
            'items' => ['sometimes', 'array'],
            'resources.*.quantity' => ['sometimes', 'numeric'],
            'resources.*.price' => ['sometimes', 'numeric'],
            'resources.*.id' => ['sometimes', 'integer'],
            'images' => ['sometimes'],
            'images.*' => ['sometimes', 'file', 'mimes:jpeg,png,gif,webp', 'max:2048'], // max:2048 for 2MB
        ];
    }
}
