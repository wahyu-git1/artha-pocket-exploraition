<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class EmergencyFundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'multiplier' => ['required', 'integer', 'min:3', 'max:12'],
            'avg_monthly_expense' => ['required', 'integer', 'min:1'],
            'target_amount' => ['required', 'integer', 'min:1'],
            'plan_months' => ['required', 'integer', 'min:1'],
            'monthly_amount' => ['required', 'integer', 'min:1'],
            'status' => ['nullable', 'string', 'in:active,paused,completed'],
        ];

        if ($this->isMethod('PATCH') || $this->isMethod('PUT')) {
            foreach ($rules as $key => $rule) {
                if (is_array($rule)) {
                    $rules[$key][0] = 'sometimes';
                }
            }
        }

        return $rules;
    }
}
