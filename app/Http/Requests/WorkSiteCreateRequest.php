<?php

namespace App\Http\Requests;

use App\Enums\WorkSiteCompletionStatusEnum;
use App\Enums\WorkSiteReceptionStatusEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WorkSiteCreateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int,ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string'],
            'description' => ['required', 'string'],
            'customer_id' => ['sometimes', 'integer', 'exists:customers,id'],
            'category_id' => ['sometimes', 'integer', 'exists:work_site_categories,id'],
            'parent_work_site_id' => ['nullable', 'integer', 'exists:work_sites,id'],
            'contractor_id' => ['nullable', 'integer', 'exists:contractors,id'],
            'starting_budget' => ['sometimes', 'integer', 'min:0'],
            'cost' => ['sometimes', 'integer', 'min:0'],
            'address_id' => ['sometimes', 'integer', 'exists:addresses,id'],
            'workers_count' => ['sometimes', 'integer', 'min:0'],
            'receipt_date' => ['sometimes', 'date'],
            'starting_date' => ['sometimes', 'date'],
            'deliver_date' => ['sometimes', 'date'],
            'reception_status' => ['sometimes', 'integer', Rule::in(WorkSiteReceptionStatusEnum::cases())],
            'completion_status' => ['sometimes', 'integer', Rule::in(WorkSiteCompletionStatusEnum::cases())],
            'items' => ['sometimes', 'array'],
            'items.*.quantity' => ['sometimes', 'numeric'],
            'items.*.price' => ['sometimes', 'numeric'],
            'items.*.id' => ['sometimes', 'integer'],
            'payments' => ['sometimes', 'array'],
            'payments.*.payment_amount' => ['sometimes', 'numeric'],
            'payments.*.payment_date' => ['sometimes', 'date_format:Y-m-d H:i'],
            'images' => ['sometimes'],
            'images.*' => ['sometimes', 'file', 'mimes:jpeg,png,gif,webp', 'max:2048'], // max:2048 for 2MB
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
