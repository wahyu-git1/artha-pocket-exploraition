<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class SavingsGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'price' => ['required', 'integer', 'min:1'],
            'target_date' => ['required', 'date_format:Y-m-d', 'after:today'],
            'status' => ['nullable', 'string', 'in:active,paused,completed,cancelled'],
        ];

        if ($this->isMethod('PATCH') || $this->isMethod('PUT')) {
            $rules['name'][0] = 'sometimes';
            $rules['price'][0] = 'sometimes';
            $rules['target_date'][0] = 'sometimes';
        }

        return $rules;
    }
}
