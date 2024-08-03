<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ContractorCreateRequest extends FormRequest
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
     * @return array<string, array<int, ValidationRule|string>|string|ValidationRule>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string'],
            'last_name' => ['sometimes', 'string'],
            'phone' => ['sometimes', 'string'],
            'address_id' => ['sometimes', 'int'],
        ];
    }

    /**
     * Get the validated data from the request.
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return array{
     *      first_name: string,
     *      last_name: string|null,
     *      phone: string|null,
     *      address_id: int|null
     * }
     */
    //    public function validated($key = null, $default = null): array
    //    {
    //        $validated = parent::validated();
    //
    //        return [
    //            'first_name' => $validated['first_name'],
    //            'last_name' => $validated['last_name'] ?? null,
    //            'phone' => $validated['phone'] ?? null,
    //            'address_id' => isset($validated['address_id']) ? (int)$validated['address_id'] : null,
    //        ];
    //    }
}
