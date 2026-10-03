<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class InvestmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'income_id' => ['nullable', 'uuid', 'exists:incomes,id'],
            'instrument_type' => ['required', 'string', 'in:mutual_fund,gold,stock,bond,deposit,crypto,other'],
            'instrument_name' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'integer', 'min:1'],
            'invested_at' => ['required', 'date_format:Y-m-d'],
            'note' => ['nullable', 'string', 'max:255'],
            'client_id' => ['required', 'uuid'],
        ];

        if ($this->isMethod('PATCH') || $this->isMethod('PUT')) {
            $rules['client_id'] = ['nullable', 'uuid']; // not always needed for update
            foreach ($rules as $key => $rule) {
                if (is_array($rule) && $key !== 'client_id') {
                    $rules[$key][0] = 'sometimes';
                }
            }
        }

        return $rules;
    }
}
