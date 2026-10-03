<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class IncomeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'default_amount' => ['nullable', 'integer', 'min:0'],
            'frequency' => ['required', 'string', 'in:monthly,weekly,irregular'],
            'pay_day' => ['nullable', 'integer', 'min:1', 'max:31'],
            'is_primary' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
