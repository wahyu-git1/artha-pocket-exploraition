<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'income_id' => ['required', 'uuid', 'exists:incomes,id'],
            'category_id' => ['required', 'uuid', 'exists:categories,id'],
            'item' => ['required', 'string', 'max:150'],
            'amount' => ['required', 'integer', 'min:1'],
            'spent_at' => ['required', 'date_format:Y-m-d'],
            'note' => ['nullable', 'string', 'max:255'],
            'raw_input' => ['nullable', 'string'],
            'confidence_score' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'source' => ['required', 'string', 'in:manual,smart_entry,sync'],
            'client_id' => ['required', 'uuid'],
        ];

        // If it's bulk request, wrap rules
        if ($this->route()->getActionMethod() === 'bulk') {
            return [
                'expenses' => ['required', 'array', 'max:20'],
                'expenses.*.income_id' => $rules['income_id'],
                'expenses.*.category_id' => $rules['category_id'],
                'expenses.*.item' => $rules['item'],
                'expenses.*.amount' => $rules['amount'],
                'expenses.*.spent_at' => $rules['spent_at'],
                'expenses.*.note' => $rules['note'],
                'expenses.*.raw_input' => $rules['raw_input'],
                'expenses.*.confidence_score' => $rules['confidence_score'],
                'expenses.*.source' => $rules['source'],
                'expenses.*.client_id' => $rules['client_id'],
            ];
        }

        return $rules;
    }
}
