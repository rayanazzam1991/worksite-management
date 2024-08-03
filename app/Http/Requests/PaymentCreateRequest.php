<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PaymentCreateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string,array<int,ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'payable_id' => ['nullable', 'numeric'],
            'payable_type' => ['nullable', 'string'],
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_type' => ['sometimes', 'numeric'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    //    /**
    //     * @return array{
    //     *     payable_id:int|null,
    //     *     payable_type:string|null,
    //     *     payment_date:string,
    //     *     payment_amount:float,
    //     *     payment_type:int|null,
    //     * }
    //     */
}
