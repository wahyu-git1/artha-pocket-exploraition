<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:100'],
            'marital_status' => ['nullable', 'string', Rule::in(['single', 'married'])],
            'dependents_count' => ['nullable', 'integer', 'min:0', 'max:20'],
            'income_stability' => ['nullable', 'string', Rule::in(['stable', 'variable'])],
            'has_installments' => ['nullable', 'boolean'],
            'timezone' => ['nullable', 'string', 'timezone:all'],
            'primary_income_id' => ['nullable', 'uuid', 'exists:incomes,id'],
        ];
    }
}
