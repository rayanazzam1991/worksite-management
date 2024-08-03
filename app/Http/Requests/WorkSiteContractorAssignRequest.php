<?php

namespace App\Http\Requests;

use App\Enums\ConfirmEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WorkSiteContractorAssignRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'contractor_id' => ['nullable', 'integer', 'exists:contractors,id'],
            'should_remove' => ['sometimes', 'string', Rule::in(ConfirmEnum::cases())],
        ];
    }
}